<?php
/**
 * User: anubis
 * Date: 26.10.13
 * Time: 23:28
 */

namespace popcorn\tests\model\talks;

use popcorn\model\im\Comment;
use popcorn\model\system\users\UserFactory;
use popcorn\model\talks\Talk;
use popcorn\model\talks\TalkBuilder;
use popcorn\model\talks\TalkFactory;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class TalkFactoryTest extends PopcornTest {

    public function testCreateTalk() {
        TestDataGenerator::addUser(2);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $talk = TalkBuilder::create()
                           ->owner($user)
                           ->title('test')
                           ->content('content')
                           ->build();

        TalkFactory::save($talk);
        $this->assertEquals(1, $talk->getId());
        $this->assertNotNull($talk->getComments());
        $saved = TalkFactory::get($talk->getId());
        $this->assertEquals(array($talk), array($saved));
        $this->assertNotNull($saved->getComments());
        return $talk;
    }

    /**
     * @depends testCreateTalk
     * @param Talk $talk
     */
    public function testUpdateTalk($talk) {
        $talk->setContent('updated');
        TalkFactory::save($talk);
        $saved = TalkFactory::get($talk->getId());
        $this->assertEquals(array($talk), array($saved));
    }

    /**
     * @depends testCreateTalk
     * @param Talk $talk
     */
    public function testCommentTalk($talk) {
        UserFactory::logout();
        $user = UserFactory::getUser(2);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $msg = new Comment();
        $msg->setContent('test talk');
        $talk->getComments()->save($msg);
        $saved = TalkFactory::get($talk->getId());
        $comments = $saved->getComments();
        $this->assertEquals(1, $comments->getCount());
        $this->assertEquals('test talk', $comments->getMessage(1)->getContent());
    }

    /**
     * @depends testCreateTalk
     * @param Talk $talk
     */
    public function testDeleteTalk($talk) {
        $oldId = $talk->getId();
        TalkFactory::delete($talk->getId());
        $saved = TalkFactory::get($oldId);
        $this->assertNull($saved);
    }

    protected function tearDown() { }

    public static function tearDownAfterClass() {
        cleanUp();
    }

}
 