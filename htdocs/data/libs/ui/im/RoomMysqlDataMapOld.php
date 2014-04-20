<?php
/**
 * User: anubis
 * Date: 03.04.13 13:01
 */

require_once 'RoomDataMap.php';

class RoomMysqlDataMapOld implements RoomDataMap {

    public function __construct($roomId) {
        $room = explode('-', $roomId);
        $this->roomId = $room[1];
    }

    public function find($query = array(), $offset = 0, $count = null) {
        $o_c = new VPA_table_comments_extend();
        $query = $this->convertQuery($query);
        $items = $o_c->get_fetch($query, array('pole1 ASC'), $offset, $count);
        foreach($items as $id => $item) {
            $items[$id] = $this->convertDataFormat($item);
        }
        return $items;
    }

    private function convertDataFormat($item) {
        $item['content'] = $this->clearCommentText($item['content']);

        $item['parent'] = $item['re'];
        $item['owner'] = $item['user_id'];
        $item['date'] = $item['utime'];
        $item['editDate'] = $item['etime'];
        $item['deleted'] = $item['del'];
        $item['rating'] = array($item['rating_down'], $item['rating_up']);
        $item['abuse'] = $item['complain'];

        unset($item['re']);
        unset($item['user_id']);
        unset($item['ctime']);
        unset($item['new_id']);
        unset($item['etime']);
        unset($item['utime']);
        unset($item['del']);
        unset($item['complain']);
        unset($item['rating_up']);
        unset($item['rating_down']);

        return $item;
    }

    private function clearCommentText($text) {
        if(preg_match_all('@\[b\]Ответ\s+на\s+сообщение\s+от(.+)\[\/b\](.+)\z@si', $text, $matches1)) {
            if(preg_match_all('@\[b\]Ответ\s+на\s+сообщение\s+от\s+(.+),(.+),(.+)\[\/b\]@', $matches1[0][0], $matches2)) {
                $commentText = $matches1[2][0];
                $ar = explode('[/quote]', $commentText);
                return trim(end($ar));
            }
        }

        return $text;
    }

    public function count($query = array()) {
        $query = $this->convertQuery($query);
        $o_c = new VPA_table_comments_extend();
        $count = $o_c->get_num_fetch($query);
        return $count;
    }

    private function convertQuery($query) {
        if(array_key_exists('parent', $query)) {
            $query['re'] = $query['parent'];
            unset($query['parent']);
            if(is_null($query['re'])) $query['re'] = 0;
        }

        $query = array_merge($query, array('new_id' => $this->roomId));

        return $query;
    }

    public function findOne($query) {
        $query = $this->convertQuery($query);
        // TODO: Implement findOne() method.
    }

    public function findSubscriptions() {
        // TODO: Implement findSubscriptions() method.
    }

    public function subscribe($uid) {
        // TODO: Implement subscribe() method.
    }

    public function unSubscribe($uid) {
        // TODO: Implement unSubscribe() method.
    }

    public function isSubscribed($uid) {
        // TODO: Implement isSubscribed() method.
    }

    public function isValidId($id) {
        // TODO: Implement isValidId() method.
    }

    public function saveMessage(&$data) {
        // TODO: Implement saveMessage() method.
    }

    public function removeMessage($id) {
        // TODO: Implement removeMessage() method.
    }

    public function findCustom($array) {
        // TODO: Implement findCustom() method.
    }

    public function getMessageLevel($id) {
        // TODO: Implement getMessageLevel() method.
    }
}


class VPA_table_comments_extend extends VPA_table_comments {

    public function __construct() {
        parent::__construct();
        $this->add_where('re', 're = $', WHERE_INT);
    }

}