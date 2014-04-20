<?php
/**
 * User: anubis
 * Date: 15.10.13
 * Time: 1:58
 */

namespace popcorn\model\content;

use popcorn\model\dataMaps\AlbumDataMap;
use popcorn\model\system\users\UserFactory;

class AlbumFactory {

    /**
     * @var AlbumDataMap
     */
    private static $dataMap;

    /**
     * @param string $title
     *
     * @return \popcorn\model\content\Album
     */
    public static function create($title) {
        $album = new Album();
        $album->setOwner(UserFactory::getCurrentUser());
        $album->setTitle($title);
        $album->setCreateTime(time());
        $album->setPoster(new NullImage());
        self::save($album);

        return $album;
    }

    /**
     * @param Album $album
     */
    public static function save($album) {
        self::checkDataMap();
        self::$dataMap->save($album);
    }

    private static function checkDataMap() {
        if(is_null(self::$dataMap)) {
            self::$dataMap = new AlbumDataMap();
        }
    }

    /**
     * @param $id
     *
     * @return null|Album
     */
    public static function get($id) {
        self::checkDataMap();

        return self::$dataMap->findById($id);
    }

    /**
     * @param AlbumBuilder $builder
     *
     * @return \popcorn\model\content\Album
     */
    public static function createFromBuilder($builder) {
        $album = $builder->build();
        self::save($album);

        return $album;
    }

}