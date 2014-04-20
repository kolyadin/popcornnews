<?php
/**
 * User: anubis
 * Date: 27.02.13 13:31
 */
class BlackList {

    private $owner = 0;
    private $list = array();

    public function __construct($owner, $list = array()) {
        $this->owner = $owner;
        $this->list = $list;
    }

    public function isUserExists($userId) {
        if(count($this->list) == 0) return false;
        if(!in_array($userId, $this->list)) return false;
        return true;
    }

    public function addUser($userId) {
        global $modersIds;
        if(in_array($userId, $modersIds)) return;
        $this->list[] = $userId;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function getUserList() {
        return $this->list;
    }

    public function removeUser($userId) {
        $id = array_search($userId, $this->list);
        if($id === false) return;
        unset($this->list[$id]);
    }

}
