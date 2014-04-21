<?php

namespace popcorn\model\dataMaps;

use %fullClassName%;

class %className%DataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "%fullClassNameS%";
$this->insertStatement = $this->prepare("%insertSql%");
$this->updateStatement = $this->prepare("%updateSql%");
$this->deleteStatement = $this->prepare("%deleteSql%");
$this->findOneStatement = $this->prepare("%findOneSql%");
}

/**
* @param %className% $item
*/
protected function insertBindings($item) {
%insertBindings%
}

/**
* @param %className% $item
*/
protected function updateBindings($item) {
%updateBindings%
}

}