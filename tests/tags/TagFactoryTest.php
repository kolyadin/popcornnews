<?php
/**
 * User: anubis
 * Date: 08.10.13 17:35
 */

namespace popcorn\tests\model\tags;

use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;
use popcorn\tests\model\PopcornTest;

class TagFactoryTest extends PopcornTest {

    public function testAddTag() {
        $tag = new Tag('test', Tag::PERSON);
        TagFactory::save($tag);
        $this->assertEquals(1, $tag->getId());
        $saved = TagFactory::get($tag->getId());
        $this->assertEquals($tag, $saved);
    }

    public function testUpdateTag() {
        $tag = new Tag();
        $tag->setName('test');
        TagFactory::save($tag);
        $tag->setName('updated');
        $this->assertTrue($tag->isChanged());
        TagFactory::save($tag);
        $this->assertFalse($tag->isChanged());
        $savedTag = TagFactory::get($tag->getId());
        $this->assertEquals('updated', $savedTag->getName());
        $this->assertEquals(Tag::EVENT, $savedTag->getType());
    }

    public function testFindArray() {
        $tags[] = new Tag('test1');
        $tags[] = new Tag('test2', Tag::PERSON);
        TagFactory::save($tags[0]);
        TagFactory::save($tags[1]);
        $savedTags = TagFactory::findByIds(array(1, 2));
        $this->assertEquals($tags, $savedTags);
        $savedTags = TagFactory::findByIds(array(3, 4));
        $this->assertCount(0, $savedTags);
    }

    public function testRemove() {
        $tag = new Tag('test1');
        TagFactory::save($tag);
        $this->assertEquals(1, $tag->getId());
        $this->assertTrue(TagFactory::delete($tag->getId()));
        $savedTag = TagFactory::get($tag->getId());
        $this->assertNull($savedTag);
    }

}
