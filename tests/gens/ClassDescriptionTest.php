<?php
/**
 * User: anubis
 * Date: 24.10.13 16:08
 */

namespace popcorn\tests\model\gens;


use popcorn\gens\structs\ClassDescription;
use popcorn\gens\structs\FieldDescription;
use popcorn\gens\structs\MethodDescription;
use popcorn\gens\structs\ParamDescription;

class ClassDescriptionTest extends \PHPUnit_Framework_TestCase {

    public function testCreateDefaultClass() {
        $class = new ClassDescription('TestClass');
        $this->assertEquals('class TestClass {}', $class->export());
    }

    public function testCreateClassWithDoc() {
        $class = new ClassDescription('TestClass', 'popcorn\\model');
        $class->useDoc();

        $out = <<<'CLASS'
/**
 * Class TestClass
 * @package popcorn\model
 */
class TestClass {}
CLASS;

        $export = $class->export();
        $this->assertEquals($out, $export);
        $this->assertEquals('popcorn\\model', $class->getNamespace());
    }

    public function testCreateFullClass() {
        $class = new ClassDescription('TestClass', 'popcorn\\model');
        $class->useDoc();
        $field = new FieldDescription('title', 'string');
        $field->setUseGetter();
        $field->setUseSetter();
        $param = new ParamDescription('title');
        $param->setType('string');
        $param->setDescription('Title');
        $method = new MethodDescription('__construct');
        $method->addParam($param);
        $method->setCode('$this->name = $name;');
        $class->addField($field);
        $class->addMethod($method);
        $out = <<<'CLASS'
/**
 * Class TestClass
 * @package popcorn\model
 */
class TestClass {

//region Fields

/**
 * @var string
 */
private $title;

//endregion

/**
 * @return string
 */
public function getTitle() {
return $this->title;
}

/**
 * @param string $title
 */
public function setTitle($title) {
$this->title = $title;
}

/**
 * @param string $title Title
 */
public function __construct($title) {
$this->name = $name;
}

}
CLASS;
        $export = $class->export();
        $this->assertEquals($out, $export, $export);
    }

    public function testCreateClassWithParentAndInterfaces() {
        $class = new ClassDescription('TestClass');
        $class->setParent('\\popcorn\\model\\Model');
        $class->addInterface('\ArrayAccess');
        $out = <<<'CLASS'
class TestClass extends \popcorn\model\Model implements \ArrayAccess {}
CLASS;
        $export = $class->export();
        $this->assertEquals($out, $export);
    }
}
 