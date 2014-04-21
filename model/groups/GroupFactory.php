<?php

namespace popcorn\model\groups;

use popcorn\model\dataMaps\GroupDataMap;

/**
 * Class GroupFactory
 * @package \popcorn\model\groups
 */
class GroupFactory {

//region Fields

    /**
     * @var \popcorn\model\dataMaps\GroupDataMap
     */
    private static $dataMap;

//endregion

    /**
     */
    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new GroupDataMap();
        }
    }

    /**
     * @param \popcorn\model\groups\Group $item
     */
    public static function save($item) {
        self::checkDataMap();
        self::$dataMap->save($item);
    }

    /**
     * @param int $id
     *
     * @return \popcorn\model\groups\Group
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