<?php
/**
 * User: anubis
 * Date: 24.10.13 14:43
 */

namespace popcorn\tests\model\gens;


use popcorn\gens\structs\MethodDescription;
use popcorn\gens\structs\ParamDescription;

class MethodDescriptionTest extends \PHPUnit_Framework_TestCase {

    public function testCreateDefaultMethod() {
        $method = new MethodDescription('test');
        $this->assertEquals('public function test() {}', $method->export());
    }

    public function testCreateMethodWithCode() {
        $method = new MethodDescription('setTitle');
        $method->addParam(new ParamDescription('title'));
        $method->setCode("\$this->title = \$title;\n\$this->changed();");
        $out = <<<'METHOD'
public function setTitle($title) {
$this->title = $title;
$this->changed();
}
METHOD;
;
        $this->assertEquals($out, $method->export());
    }

    public function testCreateMethodWithDoc() {
        $method = new MethodDescription('getSum');
        $method->useDoc();
        $method->setDescription('get sum of two numbers');
        $method->setType('int');
        $p1 = new ParamDescription('a');
        $p1->setType('int');
        $p1->setDescription('first number');
        $p2 = new ParamDescription('b');
        $p2->setType('int');
        $method->addParam($p1);
        $method->addParam($p2);

        $method->setCode('return $a + $b;');

        $out = <<<'METHOD'
/**
 * get sum of two numbers
 * @param int $a first number
 * @param int $b
 * @return int
 */
public function getSum($a, $b) {
return $a + $b;
}
METHOD;
        $export = $method->export();
        $this->assertEquals($out, $export, $export);

    }

    public function testCreateStaticMethod() {
        $method = new MethodDescription('testStatic');
        $method->setStatic(true);
        $method->setModifier('protected');
        $this->assertEquals('protected static function testStatic() {}', $method->export());
    }

}
 