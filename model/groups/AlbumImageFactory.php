<?php
/**
 * User: anubis
 * Date: 16.10.13 15:11
 */

namespace popcorn\model\content;

use popcorn\model\dataMaps\GroupAlbumDataMap;

class AlbumImageFactory {

    private static function checkDataMap() {
        if(is_null(self::$images)) {
            self::$images = new GroupAlbumDataMap();
        }
    }

    /**
     * @param Image $image
     * @param int $order
     *
     * @return AlbumImage
     */
    public static function createFromImg($image, $order = 0) {
        $albumImg = new AlbumImage();
        $albumImg->setOrder($order);
        $albumImg->enable();
        $albumImg->setCreateTime(time());
        $albumImg->setName($image->getName());
        $albumImg->setDescription($image->getDescription());
        $albumImg->setZoomable($image->isZoomable());
        $albumImg->setWidth($image->getWidth());
        $albumImg->setHeight($image->getHeight());
        $albumImg->setTitle($image->getTitle());
        $albumImg->setImageId($image->getId());
        return $albumImg;
    }

    /**
     * @var GroupAlbumDataMap
     */
    private static $images;

    /**
     * @param AlbumImage $img
     */
    public static function save($img) {
        self::checkDataMap();
        self::$images->save($img);
    }

    /**
     * @param $id
     * @param $from
     * @param $count
     * @return AlbumImage[]
     */
    public static function getImages($id, $from, $count) {
        self::checkDataMap();
        return self::$images->findByAlbumId($id, $from, $count);
    }

    /**
     * @param $imageId
     *
     * @return null|AlbumImage
     */
    public static function get($imageId) {
        self::checkDataMap();
        return self::$images->findById($imageId);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public static function delete($id) {
        self::checkDataMap();
        return self::$images->delete($id);
    }

    public static function getCount($albumId, $enabled = null) {
        self::checkDataMap();
        return self::$images->getCount($albumId, $enabled);
    }

}