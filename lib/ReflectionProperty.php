<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 23:33
 */

namespace popcorn\lib;


class ReflectionProperty {

    /**
     * @var \ReflectionProperty
     */
    private $property;

    public function __construct(\ReflectionProperty $property) {
        $this->property = $property;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getProperty() {
        return $this->property;
    }

    public function getName() {
        return $this->property->getName();
    }

    public function getValue($post) {
        return $this->property->getValue($post);
    }

    public function getAnnotations($name = '') {
        $doc = $this->property->getDocComment();
        $result = ReflectionClass::parseAnnotations($doc, $name);
        return !empty($name) && isset($result[$name]) ? $result[$name] : $result;
    }

}