<?php
/**
 * User: anubis
 * Date: 03.04.13 12:52
 */

interface RoomDataMap {

    public function find($query = array(), $offset = 0, $count = 100);

    public function count($query = array());

    public function findOne($query);

    public function findSubscriptions();

    public function subscribe($uid);

    public function unSubscribe($uid);

    public function isSubscribed($uid);

    public function isValidId($id);

    public function saveMessage(&$data);

    public function removeMessage($id);

    public function findCustom($array);

    public function getMessageLevel($id);

    public function getAllUSers();
}