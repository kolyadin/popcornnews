<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 23:34
 */

namespace popcorn\lib;


class ReflectionClass extends \ReflectionClass {

    /**
     * @param $doc
     *
     * @param string $name
     *
     * @return array
     */
    public static function parseAnnotations($doc, $name = '') {
        preg_match_all('/\@([\w|\s|\.|\d|\_|\[|\]]+)/is', $doc, $matches, PREG_SET_ORDER);
        $annotations = array();
        if(count($matches) > 0) {
            foreach($matches as $match) {
                $line = explode(' ', trim($match[1]));
                $key = array_shift($line);
                if($name != $key && !empty($name)) continue;
                if($key == 'var') continue;
                $annotations[$key] = $line;
            }
        }

        return $annotations;
    }

    /**
     * @param null $filter
     *
     * @return ReflectionProperty[]
     */
    public function getProperties($filter = null) {
        $props = parent::getProperties($filter);
        foreach($props as $key => $val) {
            $props[$key] = new ReflectionProperty($val);
        }

        return $props;
    }

    public function getAnnotations() {
        $doc = $this->getDocComment();

        return self::parseAnnotations($doc);
    }

}