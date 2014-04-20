<?php
/**
 * User: anubis
 * Date: 01.10.13 13:16
 */

namespace popcorn\model\dataMaps;

use popcorn\model\posts\FashionBattlePost;
use popcorn\model\posts\PollPost;
use popcorn\model\voting\VotingFactory;

class PollPostDataMap extends NewsPostDataMap {

    function __construct() {
        parent::__construct();
        $this->class = 'popcorn\\model\\posts\\PollPost';
    }

    /**
     * @param $id
     *
     * @return PollPost
     */
    public function findById($id) {
        $item = parent::findById($id);

        return $item;
    }

    /**
     * @param FashionBattlePost $item
     */
    public function itemCallback($item) {
        parent::itemCallback($item);
        $item->setVoting(VotingFactory::getByParent($item->getId()));
    }

}