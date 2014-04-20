<?php
/**
 * User: anubis
 * Date: 30.10.13 12:19
 */

namespace popcorn\tests\model\groups;

use popcorn\model\content\NullImage;
use popcorn\model\groups\GroupBuilder;
use popcorn\model\system\users\UserFactory;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;
use popcorn\tests\model\PopcornTest;
use popcorn\tests\TestDataGenerator;

class GroupBuilderTest extends PopcornTest {

    public function testCreate() {
        TestDataGenerator::addUser(1);
        $tag = new Tag('test');
        TagFactory::save($tag);
        $user = UserFactory::getUser(1);
        $group = GroupBuilder::create()
                             ->title('test')
                             ->description('test group')
                             ->owner($user)
                             ->poster(new NullImage())
                             ->addTag($tag)
                             ->notPublic()
                             ->build();
        $this->assertEquals('test', $group->getTitle());
        $this->assertEquals('test group', $group->getDescription());
        $this->assertEquals($user, $group->getOwner());
        $this->assertInstanceOf('popcorn\\model\\content\\Image', $group->getPoster());
        $this->assertCount(0, $group->getModelrators());
        $this->assertCount(1, $group->getTags());
        $this->assertCount(0, $group->getTalks());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyOwner() {
        GroupBuilder::create()->build();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyTitle() {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        GroupBuilder::create()->owner($user)->build();
    }

    public function testDefaultPoster() {
        TestDataGenerator::addUser(1);
        $user = UserFactory::getUser(1);
        $group = GroupBuilder::create()->owner($user)->title('test')->build();
        $this->assertInstanceOf('popcorn\\model\\content\\NullImage', $group->getPoster());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithDuplicatedTags() {
        TestDataGenerator::addUser(1);
        $tag = new Tag('test');
        TagFactory::save($tag);
        $user = UserFactory::getUser(1);
        GroupBuilder::create()
                    ->title('test')
                    ->description('test group')
                    ->owner($user)
                    ->poster(new NullImage())
                    ->addTag($tag)
                    ->addTag($tag)
                    ->notPublic()
                    ->build();
    }

}
 