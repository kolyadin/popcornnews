<?php
/**
 * User: anubis
 * Date: 01.10.13 14:54
 */

namespace popcorn\tests\model\voting;

use popcorn\model\system\users\UserFactory;
use popcorn\model\voting\Opinion;
use popcorn\model\voting\Voting;
use popcorn\model\voting\VotingFactory;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class VotingFactoryTest extends PopcornTest {

    public function testCreateVoting() {
        $opinions = $this->getTestOpinions();
        $voting = VotingFactory::create($opinions, 'test title', 1);
        $this->assertEquals(1, $voting->getId());
        $this->assertCount(2, $voting->getOpinions());

        $savedVoting = VotingFactory::get($voting->getId());
        $this->assertEquals(array($voting), array($savedVoting));

        return $voting;
    }

    /**
     * @depends testCreateVoting
     *
     * @param Voting $voting
     */
    public function testVote(Voting $voting) {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        VotingFactory::vote($voting, $voting->getOpinion(0)->getId());

        $savedVoting = VotingFactory::get($voting->getId());
        $this->assertCount(1, $savedVoting->getVotes());
        $this->assertEquals($voting->getVotes(), $savedVoting->getVotes());
    }

    public static function tearDownAfterClass() {
        cleanUp();
    }

    /**
     * @return Opinion[]
     */
    private function getTestOpinions() {
        /** @var Opinion[] $items */
        $items = array(
            new Opinion(),
            new Opinion()
        );
        $items[0]->setTitle('test 1');
        $items[1]->SetTitle('test 2');

        return $items;
    }

    public function testCreateFloatedVoting() {
        $opinions = $this->getTestOpinions();
        $voting = VotingFactory::create($opinions);

        $this->assertEquals(2, $voting->getId());
        $this->assertCount(2, $voting->getOpinions());

        $savedVoting = VotingFactory::get($voting->getId());
        $this->assertEquals(array($voting), array($savedVoting));
    }

    protected function tearDown() { }

}
