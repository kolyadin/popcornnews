<?php

namespace popcorn\model\groups;
use popcorn\model\content\AlbumImageFactory;
use popcorn\model\content\Image;

/**
 * Class AlbumImage
 * @package popcorn\model\groups
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

    public function getSeq() {
        return $this->seq;
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

    public function setSeq($seq) {
        $this->seq = $seq;
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