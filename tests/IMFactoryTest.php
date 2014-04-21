<?php
/**
 * User: anubis
 * Date: 18.09.13 14:41
 */

namespace popcorn\tests\model;

use popcorn\model\im\Comment;
use popcorn\model\im\IMFactory;
use popcorn\model\im\Room;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\tests\TestDataGenerator;

class IMFactoryTest extends PopcornTest {

    public function testGetRoom() {
        TestDataGenerator::addTopNewsPosts(1);
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $room = IMFactory::getRoom(1);
        $this->assertInstanceOf('popcorn\\model\\im\\Room', $room);

        return $room;
    }

    /**
     * @depends testGetRoom
     *
     * @param Room $room
     */
    public function testAddMessage(Room $room) {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $msg = new Comment();
        $msg->setContent('test comment');
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $msg->getIp());
        $this->assertEquals(UserFactory::getCurrentUser(), $msg->getOwner());

        $this->assertTrue($room->save($msg));
        $this->assertGreaterThan(0, $msg->getId());

        $savedMsg = $room->getMessage($msg->getId());
        $this->assertEquals(array($msg), array($savedMsg));

        UserFactory::logout();
        $msg = new Comment();
        $msg->setContent('wrong');
        $this->assertFalse($room->save($msg));
    }

    /**
     * @depends testGetRoom
     *
     * @param Room $room
     */
    public function testUpdateMessage($room) {
        TestDataGenerator::addUser(2);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $msg = new Comment();
        $msg->setContent('test comment');
        $this->assertTrue($room->save($msg));
        $msg->setContent('updated');
        $this->assertTrue($room->save($msg));
        $savedMsg = $room->getMessage($msg->getId());
        $this->assertEquals(get_object_vars($msg), get_object_vars($savedMsg));
        $this->assertGreaterThan(0, $savedMsg->getEditDate());

        $user = UserFactory::getUser(2);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $msg->setContent('test');
        $this->assertFalse($room->save($msg));
    }

    public function testCommentsTree() {
        TestDataGenerator::addUser(2);
        $room = IMFactory::getRoom(1);

        UserFactory::logout();
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $msg = new Comment();
        $msg->setContent('root comment 1');

        $room->save($msg);

        $msg2 = new Comment();
        $msg2->setContent('root comment 2');
        $room->save($msg2);

        UserFactory::logout();
        $user = UserFactory::getUser(2);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $msg1 = new Comment();
        $msg1->setContent('test answer');
        $msg1->setParent($msg);
        $msg->addChild($msg1);

        $room->save($msg1);

        $this->assertNotEquals($msg->getOwner(), $msg1->getOwner());
        $this->assertEquals(3, $room->getCount());

        $savedParent = $room->getMessage($msg1->getParent());
        $testMsg = clone $msg;
        $testMsg->clearChilds();
        $this->assertEquals($testMsg, $savedParent);

        $tree = array($msg, $msg2);
        $savedTree = $room->getMessages();

        /*$treeFile = __DIR__.'/../tmp/tree.txt';
        $savedTreeFile = __DIR__.'/../tmp/savedTree.txt';
        unlink($treeFile);
        unlink($savedTreeFile);
        file_put_contents($treeFile, print_r($tree, true));
        file_put_contents($savedTreeFile, print_r($savedTree, true));*/

        $this->assertEquals($tree, $savedTree);
        $msg = $room->getMessage($savedTree[0]->getChild(0)->getId());
        $this->assertEquals($savedTree[0]->getChild(0)->getLevel(), $msg->getLevel());
    }

    public function testSubscription() {
        $room = IMFactory::getRoom(1);
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $this->assertTrue($room->subscribe($user->getId()));
        $this->assertFalse($room->subscribe($user->getId()));

        $this->assertTrue($room->isSubscribed($user->getId()));
        $this->assertFalse($room->isSubscribed(2));

        $subscribed = $room->getSubscribed();
        $this->assertEquals(array($user->getId()), $subscribed);

        $this->assertTrue($room->unSubscribe($user->getId()));
        $this->assertFalse($room->unSubscribe($user->getId()));
    }

    public function testAbusing() {
        $room = IMFactory::getRoom(1);
        TestDataGenerator::addUser(2);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $msg = new Comment();
        $msg->setContent('test');
        $room->save($msg);

        $this->assertFalse($room->abuse($msg->getId()));
        $user = UserFactory::getUser(2);
        $this->assertTrue(UserFactory::login($user->getEmail(), $user->getPassword()));

        $this->assertTrue($room->abuse($msg->getId()));
        $this->assertFalse($room->abuse($msg->getId()));
    }

    public function testRatings() {
        $room = IMFactory::getRoom(1);
        TestDataGenerator::addUser(2);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $msg = new Comment();
        $msg->setContent('test');
        $room->save($msg);

        $this->assertFalse($room->rateUp($msg->getId()));
        $this->assertFalse($room->rateDown($msg->getId()));

        $user = UserFactory::getUser(2);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $this->assertTrue($room->rateUp($msg->getId()));
        $this->assertFalse($room->rateDown($msg->getId()));
    }

    public function testDelete() {
        $room = IMFactory::getRoom(1);
        TestDataGenerator::addUser(3);
        $user = UserFactory::getUser(1);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $msg = new Comment();
        $msg->setContent('test');
        $room->save($msg);

        $this->assertTrue($room->delete($msg));
        $this->assertFalse($room->delete($msg));

        $msg->setDeleted(0);
        $room->save($msg);

        $user = UserFactory::getUser(2);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $this->assertFalse($room->delete($msg));

        $user = UserFactory::getUser(3);
        $user->setType(User::MODERATOR);
        UserFactory::save($user);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $this->assertTrue($room->delete($msg));

        $this->assertFalse($room->delete(-1));
    }

    protected function setUp() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }
}
