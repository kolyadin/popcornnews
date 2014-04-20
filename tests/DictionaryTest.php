<?php
/**
 * User: anubis
 * Date: 15.08.13 13:01
 */

namespace popcorn\tests\model;


use popcorn\lib\PDOHelper;
use popcorn\model\content\Dictionary;
use popcorn\model\dataMaps\DataMap;

class DictionaryTest extends PopcornTest {

    private $customTable = 'pn_dict_created';

    public function testGetDictionary() {
        $dict = new Dictionary('pn_dictionary_test', array('id', 'name'));
        $list = $dict->getList();
        $this->assertCount(10, $list);
        $this->assertEquals('test 0', $list[0]['name']);
        $list = $dict->getList(array('id' => DataMap::DESC));
        $this->assertEquals('test 9', $list[0]['name']);

        $item = $dict->getItem($list[0]['id']);
        $this->assertEquals($list[0], $item);
    }

    public function testAddItem() {
        $dict = new Dictionary('pn_dictionary_test', array('id', 'name'));
        $dict->addItem(array('name' => 'custom'));

        $list = $dict->getList(array('id' => DataMap::DESC));
        $this->assertEquals('custom', $list[0]['name']);
    }

    public function testEditItem() {
        $dict = new Dictionary('pn_dictionary_test', array('id', 'name'));
        $dict->addItem(array('name' => 'test'));

        $list = $dict->getList(array('id' => DataMap::DESC));
        $result = $dict->updateItem(array('name' => 'edited', 'id' => $list[0]['id']));
        $this->assertEquals(1, $result);

        $list = $dict->getList(array('id' => DataMap::DESC));
        $this->assertEquals('edited', $list[0]['name']);
    }

    public function testRemoveItem() {
        $dict = new Dictionary('pn_dictionary_test', array('id', 'name'));
        $dict->addItem(array('name' => 'test'));

        $list = $dict->getList(array('id' => DataMap::DESC));
        $this->assertCount(11, $list);

        $this->assertTrue($dict->removeItem($list[0]['id']));

        $list = $dict->getList(array('id' => DataMap::DESC));
        $this->assertCount(10, $list);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyDataSave() {
        $dict = new Dictionary('pn_dictionary_test', array('name'));
        $dict->addItem(null);
    }

    public function testCreateTable() {
        $st = PDOHelper::getPDO()->query("SHOW TABLES LIKE '{$this->customTable}'");
        $this->assertEquals(0, $st->rowCount());
        new Dictionary($this->customTable, array('name'));
        $st = PDOHelper::getPDO()->query("SHOW TABLES LIKE '{$this->customTable}'");
        $this->assertEquals(1, $st->rowCount());
    }

    protected function setUp() {
        for($i = 0; $i < 10; $i++) {
            PDOHelper::getPDO()->query("INSERT INTO pn_dictionary_test (name) VALUES ('test ".$i."')");
        }
    }

    protected function tearDown() {
        PDOHelper::getPDO()->query("TRUNCATE pn_dictionary_test");
        PDOHelper::getPDO()->query("DROP TABLE IF EXISTS ".$this->customTable);
        parent::tearDown();
    }

}
