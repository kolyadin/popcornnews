<?php
/**
 * User: anubis
 * Date: 17.10.13
 * Time: 23:30
 */

namespace popcorn\tests\model\users;


use popcorn\lib\PDOHelper;
use popcorn\model\content\NullImage;
use popcorn\model\system\users\UserBuilder;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserInfo;
use popcorn\model\system\users\UserSettings;
use popcorn\tests\model\PopcornTest;

class UserBuilderTest extends PopcornTest {

    public function testCreateUser() {
        $user = UserBuilder::create()
                ->email('test@example.com')
                ->generatePassword()
                ->nick('user')
                ->avatar(new NullImage())
                ->enabled()
                ->userInfo(new UserInfo())
                ->userSettings(new UserSettings())
                ->build();
        UserFactory::save($user);
        $saved = UserFactory::getUser($user->getId());
        $this->assertEquals($user, $saved);
    }

    public function testCreateModerator() {
        $moderator = UserBuilder::create()
                     ->moderator()
                     ->email('test1@example.com')
                     ->password('123')
                     ->nick('moderator')
                     ->build();
        UserFactory::save($moderator);
        $saved = UserFactory::getUser($moderator->getId());
        $this->assertEquals($moderator, $saved);
        $this->assertTrue($saved->isModerator());
    }

    public function testCreateEditor() {
        $editor = UserBuilder::create()
                  ->editor()
                  ->email('test2@example.com')
                  ->password('123')
                  ->nick('editor')
                  ->build();
        UserFactory::save($editor);
        $saved = UserFactory::getUser($editor->getId());
        $this->assertEquals($editor, $saved);
        $this->assertTrue($saved->isEditor());
    }

    public function testCreateAdmin() {
        $admin = UserBuilder::create()->admin()
                 ->email('admin@example.com')
                 ->generatePassword()
                 ->nick('admin')
                 ->build();
        UserFactory::save($admin);
        $saved = UserFactory::getUser($admin->getId());
        $this->assertEquals($admin, $saved);
        $this->assertTrue($saved->isAdmin());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyEmail() {
        UserBuilder::create()->build();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyPassword() {
        UserBuilder::create()->email('test@example.com')->build();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyNick() {
        UserBuilder::create()->email('test@example.com')->password('123')->build();
    }

}
