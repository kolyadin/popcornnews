<?php
/**
 * User: anubis
 * Date: 18.09.13 14:40
 */

namespace popcorn\model\im;


use popcorn\model\dataMaps\NewsCommentDataMap;

class IMFactory {

    /**
     * @var NewsCommentDataMap
     */
    private static $dataMap = null;

    /**
     * @return NewsCommentDataMap
     */
    public static function getDataMap() {
        self::checkDataMap();
        return self::$dataMap;
    }

    public static function getRoom($id) {
        return new Room($id);
    }

    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new NewsCommentDataMap();
        }
    }
}