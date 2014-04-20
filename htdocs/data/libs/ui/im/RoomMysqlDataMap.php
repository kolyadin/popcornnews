<?php
/**
 * User: anubis
 * Date: 04.04.13 16:08
 */

class RoomMysqlDataMap implements RoomDataMap {

    private $roomName = '';
    private $roomId = 0;
    /**
     * @var VPA_table_comments_new
     */
    private $commentsTable;
    /**
     * @var VPA_table_comments_vote
     */
    private $voteTable;
    /**
     * @var VPA_table_comments_abuse
     */
    private $abuseTable;
    /**
     * @var VPA_table_comments_subscribe
     */
    private $subscribeTable;

    private $cachedItems = array();
    /**
     * @var VPA_memcache
     */
    private $mmc;

    public function __construct($roomId) {
        $this->mmc = VPA_memcache::getInstance();
        $room = explode('-', $roomId);
        $this->roomName = $room[0];
        $this->roomId = $room[1];

        $this->initTableObjects();
    }

    public function find($query = array(), $offset = 0, $count = 100) {
        $query = $this->convertQuery($query);

        if(empty($this->cachedItems)) {
            if(is_null($query['parent'])) {
                $items = $this->commentsTable->get_fetch($query, array('date ASC'));
                $this->buildTree($items, 'root');
                $this->mmc->set('room'.$this->roomId, $this->cachedItems, 600);
            }
        }

        if(is_null($query['parent'])) {
            return $this->cachedItems['root'];
        } else {
            return $this->cachedItems['item'.$query['parent']];
        }
    }

    public function count($query = array()) {
        $query = $this->convertQuery($query);
        $count = $this->commentsTable->get_num_fetch($query);

        return $count;
    }

    public function findOne($query) {
        $query = $this->convertQuery($query);
        $item = $this->commentsTable->get_first_fetch($query, array('date ASC'));

        return $this->convertDataFormat($item);
    }

    public function findSubscriptions() {
        return $this->subscribeTable->getSubscribes($this->roomId);
    }

    public function subscribe($uid) {
        $uid = $uid['uid'];
        if($this->subscribeTable->add($ret, array('user_id' => $uid, 'room_id' => $this->roomId))) {
            throw new CommentDataBaseException(mysql_error());
        }
    }

    public function unSubscribe($uid) {
        $this->subscribeTable->del_where($ret, array('uid' => $uid, 'rid' => $this->roomId));
    }

    public function isSubscribed($uid) {
        $isSubscribed = $this->subscribeTable->isSubscribed($uid, $this->roomId);

        return $isSubscribed;
    }

    public function isValidId($id) {
        return $this->commentsTable->isValidId($id);
    }

    public function saveMessage(&$data) {
        if(empty($data['parent'])) $data['parent'] = 'null';
        $data['deleted'] = intval($data['deleted']);
        $data['news_id'] = $this->roomId;

        if(isset($data['id'])) {
            $this->voteTable->setRated($data['rated'], $data['id']);
            $this->abuseTable->setAbused($data['abused'], $data['id']);
            if(!$this->commentsTable->updateComment($data)) {
                throw new CommentDataBaseException(mysql_error());
            }
        }
        else {
            if(!$this->commentsTable->addComment($data)) {
                throw new CommentDataBaseException(mysql_error());
            }
            $data['id'] = mysql_insert_id();
        }
        $this->mmc->delete('room'.$this->roomId);
    }

    public function removeMessage($id) {
        $this->commentsTable->del_where($ret, array('id' => $id));
        $this->voteTable->del_where($ret, array('cid' => $id));
        $this->abuseTable->del_where($ret, array('cid' => $id));
    }

    public function findCustom($array) {
        throw new NotImplementedException(__METHOD__);
    }

    public function getMessageLevel($id) {
        $parent = $this->commentsTable->get_first_fetch(array('id' => $id));
        $parent = $parent['parent'];
        $level = 1;
        while($parent != null) {
            $level++;
            $parent = $this->commentsTable->get_first_fetch(array('id' => $parent));
            $parent = $parent['parent'];
        }
        if($level > 8) $level = 8;

        return $level;
    }

    private function convertDataFormat($item) {
        $item['content'] = MessageFormatter::clearCommentText($item['content']);
        $item['rating'] = array($item['rating_down'], $item['rating_up']);
        unset($item['rating_down']);
        unset($item['rating_up']);
        $item['rated'] = $this->voteTable->getRated($item['id']);
        $item['abused'] = $this->abuseTable->getAbused($item['id']);

        return $item;
    }

    private function convertQuery($query) {
        //$query['deleted'] = 0;
        $query['news_id'] = $this->roomId;

        return $query;
    }

    private function initTableObjects() {
        $this->commentsTable = new VPA_table_comments_new($this->roomName);
        $this->voteTable = new VPA_table_comments_vote($this->roomName);
        $this->abuseTable = new VPA_table_comments_abuse($this->roomName);
        $this->subscribeTable = new VPA_table_comments_subscribe();
        if(empty($this->cachedItems)) {
            if($this->mmc->is('room'.$this->roomId)) {
                $this->cachedItems = $this->mmc->get('room'.$this->roomId);
            }
        }
    }

    private function buildTree($items, $parent = null) {
        $pids = array();

        foreach($items as $id => $item) {
            $items[$id] = $this->convertDataFormat($item);
            if($parent == 'root') {
                $this->cachedItems['root'][] = $items[$id];
            }
            else {
                $this->cachedItems['item'.$item['parent']][] = $items[$id];
            }
            $pids[] = $item['id'];
        }

        $childs = $this->commentsTable->get_fetch(array('news_id' => $this->roomId, 'parent_in' => implode(',', $pids)),
                                                  array('date ASC'));

        if(count($childs) > 0) {
            $this->buildTree($childs);
        }
    }

    public function getAllUSers() {
        $items = $this->commentsTable->get_fetch($this->convertQuery(array()));
        $uids = array();
        foreach($items as $item) {
            $uids[] = $item['owner'];
        }
        unset($items);
        return $uids;
    }
}

class VPA_table_comments_new extends VPA_extended_table {

    public function __construct($name) {
        parent::__construct("pn_comments_".$name);

        $this->addIntField('id');
        $this->addIntField('news_id');
        $this->addIntField('date');
        $this->addIntField('owner');
        $this->addIntField('parent');
        $this->addTextField('content');
        $this->addIntField('edit_date');
        $this->addTextField('ip');
        $this->addIntField('abuse');
        $this->addIntField('rating_down');
        $this->addIntField('rating_up');
        $this->addIntField('deleted');

        $this->add_where('id', 'id = $', WHERE_INT);
        $this->add_where('news_id', 'news_id = $', WHERE_INT);
        $this->add_where('owner', 'owner = $', WHERE_INT);
        $this->add_where('parent', 'parent = $', WHERE_INT);
        $this->add_where('parent', 'parent IS NULL', WHERE_NULL);
        $this->add_where('parent_in', 'parent IN ($)', WHERE_STRING);
        $this->add_where('deleted', 'deleted = $', WHERE_INT);
    }

    public function isValidId($id) {
        return !is_null($this->get_first_fetch(array('id' => $id)));
    }

    public function updateComment($data) {
        $id = $data['id'];
        unset($data['id']);
        $data['rating_up'] = $data['rating'][1];
        $data['rating_down'] = $data['rating'][0];
        unset($data['rating']);
        unset($data['abused']);
        unset($data['rated']);
        $data['edit_date'] = $data['editDate'];
        unset($data['editDate']);

        return $this->set_where($ret, $data, array('id' => $id));
    }

    public function addComment($data) {
        $data['rating_up'] = 0;
        $data['rating_down'] = 0;
        unset($data['rating']);
        unset($data['abused']);
        unset($data['rated']);

        return $this->add_fetch($data);
    }

    public function getAbuseData($ordering, $offset, $limit) {
        $c = clone $this;
        $c->set_as_query("SELECT
  c.news_id as id,
  count(c.id) as num_comments,
  MAX(c.abuse) as max_complains
FROM pn_comments_news as c
WHERE c.deleted = 0
GROUP BY c.news_id
ORDER BY {$ordering}");

        $items = $c->get_fetch(null, null, $offset, $limit);
        unset($c);
        return $items;
    }

    protected function getCreateSQL($table) {
        return "CREATE TABLE `{$table}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `content` mediumtext CHARACTER SET cp1251 NOT NULL,
  `edit_date` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(16) CHARACTER SET cp1251 NOT NULL,
  `abuse` int(11) NOT NULL DEFAULT '0',
  `rating_down` int(11) NOT NULL DEFAULT '0',
  `rating_up` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `news_id` (`news_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 COLLATE=cp1251_bin;";
    }

}

class VPA_table_comments_vote extends VPA_extended_table {

    public function __construct($name) {
        parent::__construct("pn_comments_".$name."_vote");

        $this->addIntField('comment_id');
        $this->addIntField('user_id');

        $this->add_where('cid', 'comment_id = $', WHERE_INT);
        $this->add_where('uid', 'user_id = $', WHERE_INT);
    }

    public function setRated($rated, $id) {
        $this->del_where($ret, array('cid' => $id));
        foreach($rated as $uid) {
            if(!$this->add($ret, array('comment_id' => $id, 'user_id' => $uid))) {
                throw new CommentDataBaseException(mysql_error());
            }
        }
    }

    public function getRated($id) {
        $items = $this->get_fetch(array('cid' => $id));
        $result = array();
        if(count($items) > 0) {
            foreach($items as $item) {
                $result[] = $item['user_id'];
            }
        }

        return $result;
    }

    protected function getCreateSQL($table) {
        return "CREATE TABLE `{$table}` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  UNIQUE KEY `comments_vote` (`comment_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;";
    }
}

class VPA_table_comments_abuse extends VPA_extended_table {

    public function __construct($name) {
        parent::__construct("pn_comments_".$name."_abuse");

        $this->addIntField('comment_id');
        $this->addIntField('user_id');

        $this->add_where('cid', 'comment_id = $', WHERE_INT);
        $this->add_where('uid', 'user_id = $', WHERE_INT);
    }

    public function getAbused($id) {
        $items = $this->get_fetch(array('cid' => $id));
        $result = array();
        if(count($items) > 0) {
            foreach($items as $item) {
                $result[] = $item['user_id'];
            }
        }

        return $result;
    }

    public function setAbused($abused, $id) {
        $this->del_where($ret, array('cid' => $id));
        foreach($abused as $uid) {
            if(!$this->add($ret, array('comment_id' => $id, 'user_id' => $uid))) {
                throw new CommentDataBaseException(mysql_error());
            }
        }
    }

    protected function getCreateSQL($table) {
        return "CREATE TABLE `{$table}` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  UNIQUE KEY `comment_abuse` (`comment_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;";
    }
}

class VPA_table_comments_subscribe extends VPA_extended_table {

    public function __construct() {
        parent::__construct("pn_comments_subscribe");

        $this->addIntField('user_id');
        $this->addIntField('room_id');

        $this->add_where('uid', 'user_id = $', WHERE_INT);
        $this->add_where('rid', 'room_id = $', WHERE_INT);
    }

    public function isSubscribed($uid, $roomId) {
        return !is_null($this->get_first_fetch(array('uid' => $uid, 'rid' => $roomId)));
    }

    public function getSubscribes($roomId) {
        $clone = clone $this;
        $clone->set_as_query("
        SELECT
        s.user_id as uid,
        u.email as email,
        u.nick as nick
        FROM pn_comments_subscribe as s
        INNER JOIN popkorn_users as u ON (s.user_id = u.id)
        WHERE s.room_id = ".$roomId);

        $items = $clone->get_fetch();
        unset($clone);
        return $items;
    }

    protected function getCreateSQL($table) {
        return '';
    }
}

/*

convert news comments

INSERT INTO pn_comments_news
(id,news_id,date,owner,parent,content,edit_date,ip,
rating_down,rating_up,deleted)
(
SELECT
c.id,
c.pole5 as news_id,
c.pole11 as date,
c.pole8 as owner,
c.re as parent,
c.pole3 as content,
c.pole10 as edit_date,
c.pole2 as ip,
c.rating_down,
c.rating_up,
c.del as deleted
FROM `popconnews_comments` as c
INNER JOIN `popconnews_goods_` as g ON (g.id = c.pole5)
WHERE g.goods_id = 2
)
 *
 *
 */