<?php
/**
 * User: anubis
 * Date: 12.09.13 13:28
 */

namespace popcorn\tests\model\users;

use popcorn\lib\PDOHelper;
use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserBuilder;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserInfo;
use popcorn\model\system\users\UserSettings;
use popcorn\tests\model\PopcornTest;

/**
 * Class UserFactoryTest
 * @package popcorn\tests\model
 */
class UserFactoryTest extends PopcornTest {

    public function testAddUser() {
        $user = $this->createTestUser();
        UserFactory::save($user);
        $this->assertGreaterThan(0, $user->getId());
        $this->assertGreaterThan(0, $user->getUserInfo()->getId());
        $this->assertGreaterThan(0, $user->getUserSettings()->getId());
    }

    /**
     * @expectedException \PDOException
     */
    public function testAddDuplicateUser() {
        $user1 = $this->createTestUser();
        $user2 = clone $user1;

        UserFactory::save($user1);
        UserFactory::save($user2);
    }

    /**
     * @expectedException \popcorn\model\exceptions\MysqlException
     */
    public function testSaveGuestUser() {
        $guest = $this->createGuestUser();
        UserFactory::save($guest);
    }

    public function testUserTypes() {
        $user = new User();
        $this->assertTrue($user->isGuest());
        $this->assertFalse($user->isNormal());

        $user = new User();
        $user->setType(USER::USER);
        $this->assertTrue($user->isNormal());

        $user = new User();
        $user->setType(USER::MODERATOR);
        $this->assertTrue($user->isModerator());

        $user = new User();
        $user->setType(User::EDITOR);
        $this->assertTrue($user->isEditor());

        $user = new User();
        $user->setType(User::ADMIN);
        $this->assertTrue($user->isAdmin());
    }

    public function testGetUser() {
        $user = $this->createTestUser();
        UserFactory::save($user);

        $savedUser = UserFactory::getUser($user->getId());
        $this->assertEquals(array($user), array($savedUser));
    }

    public function testEditUser() {
        $user = $this->createTestUser();
        UserFactory::save($user);
        $this->assertGreaterThan(0, $user->getId());

        $user->setNick('edited');
        $user->getUserInfo()->setCredo('updated');
        $user->getUserSettings()->setCanInvite(0);
        $oldId = $user->getId();
        UserFactory::save($user);
        $this->assertEquals($oldId, $user->getId());
        $savedUser = UserFactory::getUser($user->getId());
        $this->assertEquals(array($user), array($savedUser));
    }

    public function testGetUsers() {
        $users = array(
            $this->createTestUser(),
            $this->createTestUser()
        );
        $users[1]->setEmail('test1@test.ru');

        UserFactory::save($users[0]);
        UserFactory::save($users[1]);
        $this->assertNotEquals($users[0]->getId(), $users[1]->getId());

        $savedUsers = UserFactory::getUsers();
        $this->assertEquals($users, $savedUsers);

        $savedUser = UserFactory::getUser($users[1]->getId());
        $this->assertEquals(array($users[1]), array($savedUser));
    }

    public function testLogin() {
        UserFactory::logout();
        $user = UserFactory::getCurrentUser();
        $this->assertTrue($user->isGuest());

        $user = $this->createTestUser();
        UserFactory::save($user);

        $result = UserFactory::login('test@test.ru', 'pass');
        $this->assertTrue($result);
        $currentUser = UserFactory::getCurrentUser();
        $this->assertEquals($user, $currentUser);
    }

    public function testLogout() {
        $user = $this->createTestUser();
        UserFactory::save($user);
        UserFactory::login($user->getEmail(), $user->getPassword());

        $currentUser = UserFactory::getCurrentUser();
        $this->assertEquals($user, $currentUser);

        UserFactory::logout();

        $currentUser = UserFactory::getCurrentUser();
        $this->assertTrue($currentUser->isGuest());
    }

    public function testBan() {
        $user = $this->createTestUser();
        UserFactory::save($user);
        UserFactory::banById($user->getId(), -1);

        $savedUser = UserFactory::getUser($user->getId());
        $this->assertTrue((bool)$savedUser->getBanned());
        $this->assertEquals(-1, $savedUser->getUserInfo()->getBanDate());
    }

    public function testWrongLogin() {
        UserFactory::login('', '');
        $this->assertTrue(UserFactory::getCurrentUser()->isGuest());
    }

    public function testCheckMinUserRights() {
        $this->assertTrue(UserFactory::checkMinUserRights(User::GUEST));
        $this->assertFalse(UserFactory::checkMinUserRights(User::USER));
        $user = $this->createTestUser();
        UserFactory::save($user);
        UserFactory::login($user->getEmail(), $user->getPassword());
        $this->assertTrue(UserFactory::checkMinUserRights(User::USER));
    }

    /**
     * @return User
     */
    private function createTestUser() {
        $user = new User();
        $user->setNick('nick');
        $user->setEmail('test@test.ru');
        $user->setPassword('pass');
        $user->setLastVisit(time());
        $user->setEnabled(1);
        $user->setRating(0);
        $user->setType(User::USER);

        $img = new Image();
        $img->setName('img.png');
        ImageFactory::save($img);
        $user->setAvatar($img);

        $user->setUserInfo(new UserInfo());
        $user->getUserInfo()->setBirthDate(strtotime("1.1.1990"));
        $user->getUserInfo()->setCityId(1);
        $user->getUserInfo()->setCountryId(1);
        $user->getUserInfo()->setCredo("blablabla");

        $user->setUserSettings(new UserSettings());
        $user->getUserSettings()->setAlertGuestBook(1);
        $user->getUserSettings()->setAlertMessage(1);
        $user->getUserSettings()->setCanInvite(1);
        $user->getUserSettings()->setDailySubscribe(1);
        $user->getUserSettings()->setShowBirthDate(1);

        return $user;
    }


    /**
     * @return User
     */
    private function createGuestUser() {
        $user = new User();
        $user->setType(User::GUEST);

        return $user;
    }
}