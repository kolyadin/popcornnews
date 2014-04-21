<?php
/**
 * User: anubis
 * Date: 15.08.13 13:01
 */

namespace popcorn\model\content;


use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\DictionaryDataMap;

class Dictionary {

    function __construct($table, $fields = array()) {
        $this->dataMap = new DictionaryDataMap($table, $fields);
    }

    public function getList($orders = array()) {
        return $this->dataMap->getList($orders);
    }

    public function addItem($data) {
        $this->dataMap->save($data);
    }

    public function updateItem($data) {
        return $this->dataMap->save($data);
    }

    public function removeItem($id) {
        return $this->dataMap->delete($id);
    }

    public function getItem($id) {
        return $this->dataMap->findById($id);
    }

}