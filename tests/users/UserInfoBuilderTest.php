<?php
/**
 * User: anubis
 * Date: 18.10.13 12:16
 */

namespace popcorn\tests\model\users;

use popcorn\model\system\users\UserInfoBuilder;
use popcorn\model\system\users\UserMarriage;
use popcorn\model\system\users\UserSex;
use popcorn\tests\model\PopcornTest;

class UserInfoBuilderTest extends PopcornTest {

    public function testCreateDefaultUserInfo() {
        $userInfo = UserInfoBuilder::create()->build();

        $this->assertEquals(UserSex::UNKNOWN, $userInfo->getSex());
        $this->assertEmpty($userInfo->getCredo());
        $this->assertEquals(0, $userInfo->getBirthDate());
        $this->assertEquals(0, $userInfo->getCountryId());
        $this->assertEquals(0, $userInfo->getCityId());
        $this->assertEquals(0, $userInfo->getMarried());
        $this->assertEquals(0, $userInfo->getMeetPerson());
    }

    public function testCreateUserInfo() {
        $userInfo = UserInfoBuilder::create()
                    ->name('name')
                    ->male()
                    ->credo('credo')
                    ->bornAt('1990-01-01')
                    ->fromCountry(1)
                    ->fromCity(1)
                    ->married()
                    ->wantMeetWith(1)
                    ->build();

        $this->assertEquals(UserSex::MALE, $userInfo->getSex());
        $this->assertEquals('credo', $userInfo->getCredo());
        $this->assertEquals(strtotime('1990-01-01'), $userInfo->getBirthDate());
        $this->assertEquals(1, $userInfo->getCountryId());
        $this->assertEquals(1, $userInfo->getCityId());
        $this->assertEquals(UserMarriage::MARRIED, $userInfo->getMarried());
        $this->assertEquals(1, $userInfo->getMeetPerson());
    }

    public function testCreateFemaleUserInfo() {
        $userInfo = UserInfoBuilder::create()
                    ->female()
                    ->single()
                    ->build();

        $this->assertEquals(UserSex::FEMALE, $userInfo->getSex());
        $this->assertEquals(UserMarriage::SINGLE, $userInfo->getMarried());
    }

}
