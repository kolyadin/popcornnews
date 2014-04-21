<?php
/**
 * User: anubis
 * Date: 17.10.13 12:11
 */

namespace popcorn\model\content;


use popcorn\model\IBuilder;
use popcorn\model\system\users\UserFactory;

class AlbumBuilder implements IBuilder {

    private $poster;
    private $title = '';

    public static function create() {
        return new self();
    }

    public function build() {
        $album = new Album();
        $album->setTitle($this->title);
        $album->setCreateTime(time());
        $album->setOwner(UserFactory::getCurrentUser());
        if(!is_a($this->poster, 'popcorn\\model\\content\\Image')) {
            $this->poster = new NullImage();
        }
        $album->setPoster($this->poster);

        return $album;
    }

    /**
     * @param Image $poster
     *
     * @return AlbumBuilder
     */
    public function poster($poster) {
        $this->poster = $poster;

        return $this;
    }

    /**
     * @param string $title
     *
     * @return AlbumBuilder
     */
    public function title($title) {
        $this->title = $title;

        return $this;
    }

}