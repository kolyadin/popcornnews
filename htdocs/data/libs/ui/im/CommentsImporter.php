<?php
/**
 * User: anubis
 * Date: 27.03.13 12:25
 */

set_time_limit(0);

require_once 'RoomFactory.php';

class CommentsImporter {

    private static $month = array('', 'января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5, 'июня' => 6, 'июля' => 7,
                           'августа'    => 8, 'сентября' => 9, 'октября' => 10, 'ноября' => 11, 'декабря' => 12);
    private static $newsId = 0;

    public static function import($id, $type) {
        self::$newsId = $id;
        $room = self::importComments($type);
        $o_s = new VPA_table_main_comments_subscribers();
        $users = $o_s->get_fetch(array('nid' => self::$newsId));
        if(count($users) > 0) {
            foreach($users as $user) {
                $room->subscribe(intval($user['uid']));
            }
        }
        unset($users);
        unset($o_s);
        unset($room);
    }

    /**
     * @return IRoom
     */
    private static function importComments($type) {
        $nodes = self::parseOldComments();
        $room = RoomFactory::load($type.'-'.self::$newsId);
        self::parseNodes($room, $nodes);
        return $room;
    }

    private static function parseNodes(IRoom $room, $nodes, $parentId = null) {
        if(empty($nodes)) return;
        foreach($nodes as $node) {
            $data = array(
                'owner'     => $node['user_id'],
                'content'   => iconv('WINDOWS-1251', 'UTF-8', $node['text']),
                'date'      => $node['dateu'],
                'parent'    => $parentId,
                'rating'    => array($node['rating_down'], $node['rating_up']),
                'IP'        => $node['ip'],
            );
            try {
                $msg = new Message($data);
                $room->addMessage($msg);
                if(isset($node['children'])) {
                    self::parseNodes($room, $node['children'], $msg->getID());
                }
            }
            catch(Exception $e) {

            }
        }
    }

    private static function parseOldComments() {
        $comments = $users = array();

        $sql = <<<EOL
		SELECT
			 c.id
			,c.pole1 create_time
			,c.pole3 content
			,c.pole8 user_id
			,c.pole2 ip
			,c.rating_up
			,c.rating_down
			,u.nick  user_nick
		FROM
			popconnews_comments      as c
			left join popkorn_users as u on (u.id = c.pole8)
		WHERE
			c.pole5   = %u
			AND c.del = 0
EOL;
        $result = mysql_query(sprintf($sql, self::$newsId));

        while($row = mysql_fetch_assoc($result)) {
            $comments[] = $row;
        }

        mysql_free_result($result);

        $out = array();

        foreach($comments as $row) {
            $commentText = '';

            //В коммент есть ответы, парсим
            if(preg_match_all('@\[b\]Ответ\s+на\s+сообщение\s+от(.+)\[\/b\](.+)\z@si', $row['content'], $matches1)) {
                if(preg_match_all('@\[b\]Ответ\s+на\s+сообщение\s+от\s+(.+),(.+),(.+)\[\/b\]@', $matches1[0][0], $matches2)) {
                    $firstNode['nick'] = trim($matches2[1][0]);
                    $firstNode['date'] = trim($matches2[2][0]);
                    $firstNode['time'] = trim($matches2[3][0]);

                    $dateFormat = trim($firstNode['date']).' '.trim($firstNode['time']);

                    list($d, $m, $y, $h, $i) = sscanf($dateFormat, '%02u %s %04u %02u:%02u');
                    $m = self::$month[$m];
                    $date = sprintf('%02u-%02u-%04u %02u:%02u', $d, $m, $y, $h, $i);

                    list($d, $m, $y, $h, $i) = sscanf($row['create_time'], '%02u-%02u-%04u %02u:%02u');
                    $dateu = sprintf('%04u-%02u-%02u %02u:%02u:00', $y, $m, $d, $h, $i);

                    //Выдергиваем нужный текст, отсекаем лишнюю парашу
                    $commentText = $matches1[2][0];
                    $ar = explode('[/quote]', $commentText);

                    $commentText = trim(end($ar));

                    $f = array_filter($comments, function ($var) use ($firstNode, $date) {
                        if($var['user_nick'] == $firstNode['nick'] && $var['create_time'] == $date) return $var;
                    });

                    $ar = array(
                        'id'        => $row['id']
                        //,'nick'    => $firstNode['nick']
                    , 'user_id'     => $row['user_id']
                    , 'dateu'       => strtotime($dateu)
                    , 'text'        => $commentText
                    , 'ip'          => $row['ip']
                    , 'rating_up'   => $row['rating_up']
                    , 'rating_down' => $row['rating_down']
                    );

                    $f = current($f);

                    if(count($f)) $ar['parent_id'] = $f['id'];

                    $out[] = $ar;
                }
            }
            //Просто коммент, без ответа
            else {
                list($d, $m, $y, $h, $i) = sscanf($row['create_time'], '%02u-%02u-%04u %02u:%02u');
                $dateu = sprintf('%04u-%02u-%02u %02u:%02u:00', $y, $m, $d, $h, $i);

                $out[] = array(
                    'id'        => $row['id']
                , 'user_id'     => $row['user_id']
                    //,'nick'      => $row['user_nick']
                , 'dateu'       => strtotime($dateu)
                , 'text'        => $row['content']
                , 'parent_id'   => 0
                , 'ip'          => $row['ip']
                , 'rating_up'   => $row['rating_up']
                , 'rating_down' => $row['rating_down']
                );
            }
        }

        return self::buildTree($out);
    }

    private static function buildTree(array &$elements, $parentId = 0) {
        $branch = array();

        foreach($elements as $element) {
            if($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);

                if($children) {
                    $element['children'] = $children;
                }

                $branch[$element['id']] = $element;

                unset($elements[$element['id']]);
            }
        }

        return $branch;
    }

    public static function canComment($id) {
        $hr = mysql_query("SELECT stat FROM news_convert_queue WHERE nid = {$id}");
        if(!$hr) return true;

        $r = mysql_fetch_assoc($hr);

        if($r == false) return true;

        mysql_free_result($hr);

        return $r['stat'] != 1;
    }

}