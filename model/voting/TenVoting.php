<?php
/**
 * User: anubis
 * Date: 31.10.13 13:59
 */

namespace popcorn\model\voting;


class TenVoting extends Voting {

    function __construct() {
        /** @var Opinion[] $opinions */
        $opinions = array();
        for($i = 1; $i <= 10; $i++) {
            $opinion = new Opinion();
            $opinion->setId($i);
            $opinion->setTitle($i);
            $opinions[] = $opinion;
        }
        $this->setOpinions($opinions);
    }

    public function getOpinions() {
        return array();
    }

} 