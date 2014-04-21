<?php
/**
 * User: anubis
 * Date: 12.10.13
 * Time: 21:04
 */

namespace popcorn\tests\model\voting;

use popcorn\model\system\users\UserFactory;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\voting\VotingFactory;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class UpDownVotingTest extends PopcornTest {

    public function testCreate() {
        $voting = VotingFactory::createUpDownVoting();
        $this->assertInstanceOf('popcorn\\model\\voting\\UpDownVoting', $voting);
        $this->assertEquals(1, $voting->getId());
        $this->assertEquals('UpDown', $voting->getTitle());

        return $voting;
    }

    /**
     * @param UpDownVoting $voting
     *
     * @depends testCreate
     */
    public function testVote($voting) {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $this->assertTrue(VotingFactory::vote($voting, UpDownVoting::Up));
        $this->assertFalse(VotingFactory::vote($voting, UpDownVoting::Down));
    }

}
