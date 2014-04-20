<?php
/**
 * User: anubis
 * Date: 24.10.13 13:50
 */

namespace popcorn\tests\model\gens;

use popcorn\gens\structs\FieldDescription;

class FieldDescriptionTest extends \PHPUnit_Framework_TestCase {

    public function testCreateField() {
        $field = new FieldDescription('title');
        $this->assertEquals('title', $field->getName());
        $this->assertEquals('private', $field->getModifier());
        $this->assertEquals('private $title;', $field->export());
        $field->setType('string');
        $this->assertEquals('string', $field->getType());
    }

    public function testCreatePublicField() {
        $field = new FieldDescription('title');
        $field->setModifier('public');
        $this->assertEquals('public', $field->getModifier());
        $this->assertEquals('public $title;', $field->export());
    }

    public function testCreateFieldWithDocs() {
        $field = new FieldDescription('title', 'string');
        $field->useDoc();
        $out = <<<'EOL'
/**
 * @var string
 */
private $title;
EOL;
        $this->assertEquals($out, $field->export());
    }

    public function testCreateFieldWithGettersAndSetters() {
        $field = new FieldDescription('visible', 'bool');
        $field->setUseGetter('isVisible');
        $field->setUseSetter();

        $this->assertEquals('isVisible', $field->getter());
        $this->assertEquals('setVisible', $field->setter());
    }

    public function testCreateStaticField() {
        $field = new FieldDescription('staticTest');
        $field->setStatic(true);
        $this->assertEquals('private static $staticTest;', $field->export());
    }

}
 