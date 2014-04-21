<?php
/**
 * User: anubis
 * Date: 20.09.13 12:48
 */

namespace popcorn\lib\mmc;


class MemcacheObject {

    /**
     * @var string
     */
    public $key;
    /**
     * @var mixed
     */
    public $value;

    /**
     * @var int
     */
    public $expire = 3600;

    /**
     * @var array
     */
    public $tags = array();

    public function __construct($key, $value, $expire = 3600, $tags = array()) {
        $this->key = $key;
        $this->value = $value;
        $this->expire = $expire;
        $this->tags = $tags;
    }
}