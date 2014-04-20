<?php
/**
 * User: anubis
 * Date: 11.08.13
 * Time: 15:59
 */

namespace popcorn\tests\model;


use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\tags\Tag;

class TagsDataMapTest extends PopcornTest {

    /**
     * @var TagDataMap
     */
    public $dataMap;

    public function testCreateTag() {
        $tag = new Tag("test", Tag::EVENT);
        $this->dataMap->save($tag);
        $this->assertGreaterThan(0, $tag->getId());
        $savedTag = $this->dataMap->findById($tag->getId());
        $this->assertEquals(get_object_vars($tag), get_object_vars($savedTag));
    }

    public function testUpdateTag() {
        $tag = new Tag("test", Tag::EVENT);
        $this->dataMap->save($tag);
        $this->assertGreaterThan(0, $tag->getId());
        $tag->setName("edited");
        $this->dataMap->save($tag);
        $savedTag = $this->dataMap->findById($tag->getId());
        $this->assertEquals(get_object_vars($tag), get_object_vars($savedTag));

    }

    public function testRemoveTag() {
        $tag = new Tag("test", Tag::EVENT);
        $this->dataMap->save($tag);
        $this->assertGreaterThan(0, $tag->getId());
        $savedTag = $this->dataMap->findById($tag->getId());
        $this->assertEquals(get_object_vars($tag), get_object_vars($savedTag));
        $this->dataMap->delete($tag->getId());
        $savedTag = $this->dataMap->findById($tag->getId());
        $this->assertNull($savedTag);
    }

    public function testFindList() {
        $tags = array(
            new Tag("tag1", Tag::EVENT),
            new Tag("tag2", Tag::PERSON),
        );
        $this->dataMap->save($tags[0]);
        $this->dataMap->save($tags[1]);
        $this->assertGreaterThan(0, $tags[0]->getId());
        $this->assertGreaterThan(0, $tags[1]->getId());
        $savedTags = $this->dataMap->find();
        $this->assertEquals($tags, $savedTags);
        $savedTags = $this->dataMap->find(array('id' => $tags[0]->getId()));
        $this->assertCount(1, $savedTags);
        $this->assertEquals(array($tags[0]), $savedTags);
    }

    protected function setUp() {
        connect();
        $this->dataMap = new TagDataMap();
    }

}
