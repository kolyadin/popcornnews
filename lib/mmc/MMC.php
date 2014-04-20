<?php
/**
 * User: anubis
 * Date: 20.09.13 11:52
 */

namespace popcorn\lib\mmc;

use popcorn\lib\Config;

class MMC {

    const TAGS_PREFIX = 'memcache_tags_';

    /**
     * @var \Memcache
     */
    private static $mmc = null;

    /**
     * @param MemcacheObject $object
     *
     * @return bool
     */
    public static function set($object) {
        self::check();
        if(!empty($object->tags)) {
            MMC::assignTags($object);
        }
        return self::$mmc->set($object->key, $object->value, 0, $object->expire);
    }

	public static function genKey(){
		return md5(serialize(func_get_args()));
	}

	public static function genTag(){

		$args = func_get_args();

		sort($args);

		return md5(serialize($args));
	}

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key) {
        self::check();
        $val = self::$mmc->get($key);
        if($val === false) {
            return null;
        }
        return $val;
    }

	/**
	 * @return mixed
	 */
	public static function getSet(){
		self::check();

		/*array_walk(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),function($value,$key){
			echo '<pre>',print_r($value,true),'</pre>';
		});*/

//		echo '<pre>',print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),true),'</pre>';

		$cacheKey = func_get_args()[0];

		$fromCache = MMC::get($cacheKey);

		//В кэше есть нужные нам данные
		if (!is_null($fromCache)){
			return $fromCache;
		}

		$expire = func_get_args()[1];
		$tags  = [];
		$callback = function(){};

		//Теги
		if (is_array(func_get_args()[2])){
			$tags = func_get_args()[2];
			$callback = func_get_args()[3];
		}elseif(is_callable(func_get_args()[2])){
			$callback = func_get_args()[2];
		}

		$callbackOutput = $callback();

		MMC::set(new MemcacheObject($cacheKey,$callbackOutput,$expire,$tags));

		return $callbackOutput;
	}

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function exists($key) {
        self::check();
        return !is_null(self::get($key));
    }

    /**
     * @param string $key
     */
    public static function del($key) {
        self::check();
        self::$mmc->delete($key);
    }

    private static function check() {
        if(is_null(self::$mmc)) {
            self::$mmc = new \Memcache();
        }
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if(!@self::$mmc->getversion()) {
            self::$mmc->connect(Config::getMMCHost(), Config::getMMCPort());
        }
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public static function getByTag($tag) {
        $keys = self::getTaggedKeys($tag);
        $values = array();
        if(!is_null($keys)) {
            foreach($keys as $key) {
                $value = self::get($key);
                if(!is_null($value)) {
                    $values[] = $value;
                }
            }
        }
        return $values;
    }

    /**
     * @param string $tag
     */
    public static function delByTag($tag) {
        $keys = self::getTaggedKeys($tag);
        if(!is_null($keys)) {
            foreach($keys as $key) {
                self::del($key);
            }
            self::del(self::TAGS_PREFIX.$tag);
        }
    }

    /**
     * @param MemcacheObject $object
     */
    private static function assignTags($object) {
        foreach($object->tags as $tag) {
            $tags = MMC::get(self::TAGS_PREFIX.$tag);
            if(is_null($tags)) {
                $tags = array();
            }
            $tags[] = $object->key;
            MMC::set(new MemcacheObject(self::TAGS_PREFIX.$tag, $tags, 0));
        }
    }

    public static function flush() {
        self::check();
        self::$mmc->flush();
    }

    /**
     * @param $tag
     *
     * @return mixed
     */
    private static function getTaggedKeys($tag) {
        $keys = self::get(self::TAGS_PREFIX.$tag);
        return $keys;
    }

}