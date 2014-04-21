<?php
/**
 * User: anubis
 * Date: 01.10.13 12:50
 */

namespace popcorn\model\posts;

use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\Opinion;

class FashionBattlePost extends PollPost {

    /**
     * @param int|Person $firstPerson
     * @param int|Person $secondPerson
     * @param string $title
     *
     * @throws \InvalidArgumentException
     */
    public function createVoting($firstPerson, $secondPerson, $title = '') {
        if(is_numeric($firstPerson)) {
            $firstPerson = PersonFactory::getPerson($firstPerson);
        }
        if(is_numeric($secondPerson)) {
            $secondPerson = PersonFactory::getPerson($secondPerson);
        }
        if(is_null($firstPerson) || is_null($secondPerson)) {
            throw new \InvalidArgumentException("Need two persons");
        }
        $opinions = array();
        $opinions[] = new Opinion(array('title' => $firstPerson->getName()));
        $opinions[] = new Opinion(array('title' => $secondPerson->getName()));
        parent::createVote($opinions, $title);
    }

}