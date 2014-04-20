<?php
/**
 * User: anubis
 * Date: 31.10.13 14:04
 */

namespace popcorn\tests\model\voting;

use popcorn\model\system\users\UserFactory;
use popcorn\model\voting\TenVoting;
use popcorn\model\voting\VotingFactory;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class TenVotingTest extends PopcornTest {

    public function testCreate() {
        $voting = VotingFactory::createTenVoting();
        $this->assertInstanceOf('popcorn\\model\\voting\\TenVoting', $voting);
        $this->assertEquals(1, $voting->getId());
        $this->assertEquals('TenVoting', $voting->getTitle());

        return $voting;
    }

    /**
     * @param TenVoting $voting
     *
     * @depends testCreate
     */
    public function testVote($voting) {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $this->assertTrue(VotingFactory::vote($voting, 1));
        $this->assertFalse(VotingFactory::vote($voting, 2));
        $votes = $voting->getVotes();
        $this->assertCount(1, $votes);
        $this->assertEquals(1, $votes[0]->getOpinionId());
        $this->assertEquals($user->getId(), $votes[0]->getUserId());
        $this->assertEquals(1, $voting->getVoteCount(1));
        $this->assertEquals(0, $voting->getVoteCount(2));
    }
}
 