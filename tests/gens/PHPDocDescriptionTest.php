<?php
/**
 * User: anubis
 * Date: 24.10.13 14:08
 */

namespace popcorn\tests\model\gens;


use popcorn\gens\structs\PHPDocDescription;

class PHPDocDescriptionTest extends \PHPUnit_Framework_TestCase {

    public function testCreateEmptyDoc() {
        $doc = new PHPDocDescription();
        $this->assertEquals("/**\n */", $doc->export());
    }

    public function testCreateDoc() {
        $doc = new PHPDocDescription();
        $doc->setDescription('test');
        $doc->addAnnotation('var', 'string');
        $this->assertEquals("/**\n * test\n * @var string\n */", $doc->export());
    }
}
 