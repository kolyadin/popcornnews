<?php
/**
 * User: anubis
 * Date: 12.10.13
 * Time: 20:54
 */

namespace popcorn\model\voting;

/**
 * Class UpDownVoting2
 * @package popcorn\model\voting
 * @table pn_voting
 */
class UpDownVoting2 extends Voting {

    const Up = 1;
    const Down = 2;

    public function __construct() {
        $up = new Opinion();
        $up->setId(self::Up);
        $up->setTitle('yes');
        $down = new Opinion();
        $down->setId(self::Down);
        $down->setTitle('no');
        $this->setOpinions(array($up, $down));
    }

    public function getOpinions() {
        return array();
    }

}