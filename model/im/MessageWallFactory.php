<?php 

namespace popcorn\model\im;

use popcorn\model\dataMaps\MessageWallDataMap;
/**
 * Class MessageWallFactory
 * @package \popcron\model\im
 */
class MessageWallFactory {

//region Fields

/**
 * @var \popcorn\model\dataMaps\MessageWallDataMap
 */
private static $dataMap;

//endregion

/**
 */
private static function checkDataMap() {
if(is_null(self::$dataMap)) {
    self::$dataMap = new MessageWallDataMap();
}
}

/**
 * @param \popcron\model\im\MessageWall $item
 */
public static function save($item) {
self::checkDataMap();
self::$dataMap->save($item);
}

/**
 * @param int $id
 * @return \popcron\model\im\MessageWall
 */
public static function get($id) {
self::checkDataMap();
return self::$dataMap->findById($id);
}

/**
 * @param int $id
 * @return bool
 */
public static function delete($id) {
self::checkDataMap();
return self::$dataMap->delete($id);
}

}