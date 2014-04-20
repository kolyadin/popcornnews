<?php
/**
 * User: anubis
 * Date: 15.10.13
 * Time: 22:05
 */

namespace popcorn\model\content;

/**
 * Class AlbumImage
 * @package popcorn\model\content
 */
class AlbumImage extends Image {

    /**
     * @var int
     */
    private $seq = 0;
    /**
     * @var bool
     */
    private $enabled = true;

    private $albumId;
    private $imageId;

    public function getOrder() {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    public function getAlbumId() {
        return $this->albumId;
    }

    public function getImageId() {
        return $this->imageId;
    }

    public function setOrder($order) {
        $this->order = $order;
        $this->changed();
    }

    public function enable() {
        $this->enabled = true;
        $this->changed();
    }

    public function disable() {
        $this->enabled = false;
        $this->changed();
    }

    public function setAlbumId($albumId) {
        $this->albumId = $albumId;
    }

    public function setImageId($id) {
        $this->imageId = $id;
    }

    protected function onChanged() {
        AlbumImageFactory::save($this);
    }

}