<?php
/**
 * User: anubis
 * Date: 18.10.13 11:54
 */

namespace popcorn\tests\model\users;


use popcorn\model\system\users\UserBuilder;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserSettingsBuilder;
use popcorn\tests\model\PopcornTest;

class UserSettingsBuilderTest extends PopcornTest {

    public function testCreateDefaultUserSettings() {
        $userSettings = UserSettingsBuilder::create()->build();

        $this->assertTrue((bool)$userSettings->getAlertGuestBook());
        $this->assertTrue((bool)$userSettings->getAlertMessage());
        $this->assertTrue((bool)$userSettings->getCanInvite());
        $this->assertFalse((bool)$userSettings->getDailySubscribe());
        $this->assertFalse((bool)$userSettings->getShowBirthDate());
    }

    public function testCreateUserSettings() {
        $userSettings = UserSettingsBuilder::create()
                        ->birthDateShown()
                        ->subscribeToDaily()
                        ->dontAlertOnMessage()
                        ->dontAlertOnGuestBook()
                        ->notAllowInviting()
                        ->build();

        $this->assertFalse((bool)$userSettings->getAlertGuestBook());
        $this->assertFalse((bool)$userSettings->getAlertMessage());
        $this->assertFalse((bool)$userSettings->getCanInvite());
        $this->assertTrue((bool)$userSettings->getDailySubscribe());
        $this->assertTrue((bool)$userSettings->getShowBirthDate());
    }

}
