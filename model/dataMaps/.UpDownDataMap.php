<?php
/**
 * User: anubis
 * Date: 12.10.13
 * Time: 22:03
 */

namespace popcorn\model\dataMaps;

use popcorn\model\voting\UpDownVoting2;
use popcorn\model\voting\VotingFactory;

class UpDownDataMap2 extends VotingDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = 'popcorn\\model\\voting\\UpDownVoting';
    }

    /**
     * @param UpDownVoting2 $item
     */
    protected function itemCallback($item) {
        $item->setParentId($item->{'parentId'});
        $item->setTitle($item->{'title'});
        $item->setVotes($this->votes->findByParentId($item->getId()));
        $item->setId($item->{'id'});
        unset($item->{'id'});
        unset($item->{'parentId'});
        unset($item->{'title'});
    }

}