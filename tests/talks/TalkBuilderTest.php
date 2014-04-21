<?php
/**
 * User: anubis
 * Date: 26.10.13
 * Time: 23:29
 */

namespace popcorn\tests\model\talks;

use popcorn\model\system\users\UserFactory;
use popcorn\model\talks\TalkBuilder;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class TalkBuilderTest extends PopcornTest {

    public function testCreateTalk() {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        $talk = TalkBuilder::create()
                           ->owner($user)
                           ->title('test')
                           ->content('content')
                           ->build();

        $this->assertEquals($user, $talk->getOwner());
        $this->assertEquals('test', $talk->getTitle());
        $this->assertEquals('content', $talk->getContent());
        $this->assertEquals(1, $talk->getRating()->getId());
    }
}
 