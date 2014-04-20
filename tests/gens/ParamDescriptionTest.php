<?php
/**
 * User: anubis
 * Date: 24.10.13 15:28
 */

namespace popcorn\tests\model\gens;

use popcorn\gens\structs\ParamDescription;

class ParamDescriptionTest extends \PHPUnit_Framework_TestCase {

    public function testCreateSimpleParam() {
        $param = new ParamDescription('test');
        $this->assertEquals("\$test", $param->export());
    }

    public function testCreateParamWithDefaultValue() {
        $param = new ParamDescription('title', '');
        $this->assertEquals("\$title = ''", $param->export());
    }

    public function testCreateReferenceParam() {
        $param = new ParamDescription('item', null, true);
        $this->assertEquals("&\$item", $param->export());
    }

    public function testCreateParamWithType() {
        $param = new ParamDescription('item');
        $param->setType('popcorn\\model\\Model');
        $param->setDescription('test');
        $this->assertEquals('popcorn\\model\\Model', $param->getType());
        $this->assertEquals('test', $param->getDescription());
    }

}
 