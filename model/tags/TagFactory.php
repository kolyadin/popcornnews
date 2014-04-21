<?php
/**
 * User: anubis
 * Date: 16.10.13
 * Time: 23:55
 */

namespace popcorn\model\tags;


use popcorn\model\dataMaps\TagDataMap;

class TagFactory {

    /**
     * @var TagDataMap
     */
    private static $dataMap;

    /**
     * @param Tag $tag
     */
    public static function save($tag) {
        self::checkDataMap();
        self::$dataMap->save($tag);
    }

    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new TagDataMap();
        }
    }

    /**
     * @param $id
     *
     * @return null|Tag
     */
    public static function get($id) {
        self::checkDataMap();

        return self::$dataMap->findById($id);
    }

	public static function getByName($name){

		self::checkDataMap();

		return self::$dataMap->findByName($name);

	}

    /**
     * @param array $ids
     *
     * @return Tag[]
     */
    public static function findByIds($ids) {
        self::checkDataMap();

        return self::$dataMap->findByIds($ids);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public static function delete($id) {
        self::checkDataMap();

        return self::$dataMap->delete($id);
    }
}