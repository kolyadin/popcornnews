<?php
/**
 * User: anubis
 * Date: 08.10.13 13:56
 */

namespace popcorn\model\dataMaps;

class FashionBattleDataMap extends PollPostDataMap {

    function __construct($settings = array()) {
        parent::__construct($settings);
        $this->class = 'popcorn\\model\\posts\\FashionBattlePost';
    }

}