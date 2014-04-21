<?php
/**
 * User: anubis
 * Date: 31.10.13 14:00
 */

namespace popcorn\model\dataMaps;


use popcorn\model\voting\TenVoting;

class TenVotingDataMap extends VotingDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = 'popcorn\\model\\voting\\TenVoting';
    }

    /**
     * @param TenVoting $item
     */
    protected function itemCallback($item) {
        $item->setParentId($item->{'parentId'});
        $item->setTitle($item->{'title'});
        $item->setVotes($this->votes->findByParentId($item->getId()));
        $item->setId($item->{'id'});
        $item->setVoteCounts($this->getVoteCounts($item->getId()));
        unset($item->{'id'});
        unset($item->{'parentId'});
        unset($item->{'title'});
    }

}