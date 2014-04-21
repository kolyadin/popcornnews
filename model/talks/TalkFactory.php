<?php

namespace popcorn\model\talks;

use popcorn\model\dataMaps\TalkDataMap;
use popcorn\model\persons\Person;

/**
 * Class TalkFactory
 * @package \popcorn\model\talks
 */
class TalkFactory {

//region Fields

    /**
     * @var \popcorn\model\dataMaps\TalkDataMap
     */
    private static $dataMap;

//endregion

    /**
     */
    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new TalkDataMap();
        }
    }

    /**
     * @param \popcorn\model\talks\Talk $item
     */
    public static function save($item) {
        self::checkDataMap();
        self::$dataMap->save($item);
    }

    /**
     * @param int $id
     *
     * @return \popcorn\model\talks\Talk
     */
    public static function get($id) {
        self::checkDataMap();

        return self::$dataMap->findById($id);
    }


    /**
     * @param int $id
     *
     * @return bool
     */
    public static function delete($id) {
        self::checkDataMap();

        return self::$dataMap->delete($id);
    }

}