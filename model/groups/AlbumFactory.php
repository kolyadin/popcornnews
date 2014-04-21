<?php

namespace popcorn\model\groups;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\GroupAlbumDataMap;

/**
 * Class GroupFactory
 * @package \popcorn\model\groups
 */
class AlbumFactory {

//region Fields

    /**
     * @var \popcorn\model\dataMaps\GroupAlbumDataMap
     */
    private static $dataMap;

//endregion

    /**
     */
    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {

			$dataMapHelper = new DataMapHelper();
			$dataMapHelper->setRelationship([
				'popcorn\\model\\dataMaps\\GroupAlbumDataMap' => GroupAlbumDataMap::WITH_NONE
			]);

            self::$dataMap = new GroupAlbumDataMap($dataMapHelper);
        }
    }

    /**
     * @param \popcorn\model\groups\Album $item
     */
    public static function save($item) {
        self::checkDataMap();
        self::$dataMap->save($item);
    }

    /**
     * @param int $id
     *
     * @return \popcorn\model\groups\Album
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