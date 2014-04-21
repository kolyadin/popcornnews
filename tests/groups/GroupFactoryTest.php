<?php
/**
 * User: anubis
 * Date: 30.10.13 12:20
 */

namespace popcorn\tests\model\groups;


use popcorn\model\content\NullImage;
use popcorn\model\groups\Group;
use popcorn\model\groups\GroupBuilder;
use popcorn\model\groups\GroupFactory;
use popcorn\model\system\users\UserFactory;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;
use popcorn\tests\TestDataGenerator;

class GroupFactoryTest extends \PHPUnit_Framework_TestCase {

    public function testCreate() {
        TestDataGenerator::addUser(2);
        $user = UserFactory::getUser(1);
        $group = GroupBuilder::create()
                             ->title('test')
                             ->description('test group')
                             ->owner($user)
                             ->poster(new NullImage())
                             ->build();

        GroupFactory::save($group);
        $saved = GroupFactory::get($group->getId());
        $this->assertEquals(array($group), array($saved));

        return $group;
    }

    /**
     * @param Group $group
     *
     * @depends testCreate
     */
    public function testUpdate($group) {
        $group->setTitle('updated');
        $group->setDescription('updated group');
        GroupFactory::save($group);
        $saved = GroupFactory::get($group->getId());
        $this->assertEquals(array($group), array($saved));
    }

    /**
     * @param Group $group
     *
     * @depends testCreate
     */
    public function testDelete($group) {
        $oldId = $group->getId();
        $this->assertTrue(GroupFactory::delete($group->getId()));
        $saved = GroupFactory::get($oldId);
        $this->assertNull($saved);
    }

    public function testTags() {
        $user = UserFactory::getUser(1);
        $tags = array(
            new Tag('test'),
            new Tag('test1'),
        );
        TagFactory::save($tags[0]);
        TagFactory::save($tags[1]);
        $group = GroupBuilder::create()
                             ->title('test')
                             ->description('test group')
                             ->owner($user)
                             ->addTag($tags[0])
                             ->poster(new NullImage())
                             ->build();

        GroupFactory::save($group);
        $saved = GroupFactory::get($group->getId());
        $this->assertEquals(array($group), array($saved));

        $group->addTag($tags[1]);
        GroupFactory::save($group);

        $saved = GroupFactory::get($group->getId());
        $this->assertEquals(array($group), array($saved));
    }

    protected function tearDown() { }

    public static function tearDownAfterClass() {
        cleanUp();
    }

}
 