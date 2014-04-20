<?php
/**
 * User: anubis
 * Date: 15.10.13
 * Time: 1:09
 */

namespace popcorn\model\content;

use popcorn\model\exceptions\SaveFirstException;
use popcorn\model\Model;
use popcorn\model\system\users\User;

/**
 * Class Album
 * @package popcorn\model\content
 * @table pn_albums
 */
class Album extends Model {

    //region Fields

    /**
     * @var string
     * @export
     */
    private $title = '';
    /**
     * @var int
     * @export
     */
    private $createTime = 0;
    /**
     * @var Image
     * @export
     */
    private $poster;
    /**
     * @var User
     * @export ro
     */
    private $owner;

    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @param int $from
     * @param $count
     *
     * @return \popcorn\model\content\AlbumImage[]
     */
    public function getImages($from = 0, $count = -1) {
        return AlbumImageFactory::getImages($this->getId(), $from, $count);
    }

    /**
     * @return \popcorn\model\content\AlbumImage
     */
    public function getPoster() {
        return $this->poster;
    }

    /**
     * @return User
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    //endregion

    //region Setters

    /**
     * @param int $createTime
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
    }

    /**
     * @param \popcorn\model\content\AlbumImage $poster
     */
    public function setPoster($poster) {
        $this->poster = $poster;
        $this->changed();
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changed();
    }

    /**
     * @param User $user
     */
    public function setOwner($user) {
        $this->owner = $user;
    }

    //endregion

    /**
     * @param Image $img
     * @param int $order чем больше, тем раньше показывается
     *
     * @throws \popcorn\model\exceptions\SaveFirstException
     */
    public function addImage($img, $order = 0) {
        if(is_null($this->getId())) {
            throw new SaveFirstException;
        }
        if(is_null($img->getId())) {
            throw new SaveFirstException;
        }
        $albumImg = AlbumImageFactory::createFromImg($img, $order);
        $albumImg->setAlbumId($this->getId());
        AlbumImageFactory::save($albumImg);
    }

    /**
     * @param $id
     *
     * @return AlbumImage
     */
    public function getImage($id) {
        $img = AlbumImageFactory::get($id);
        if(is_null($img)) {
            return null;
        }
        if($img->getAlbumId() != $this->getId()) {
            return null;
        }
        return $img;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteImage($id) {
        return AlbumImageFactory::delete($id);
    }

    /**
     * @param int $enabled  0 - количество задизабленных, 1 - количество видимых, любое другое - количество всех картинок
     *
     * @return int
     */
    public function getImagesCount($enabled = null) {
        return AlbumImageFactory::getCount($this->getId(), $enabled);
    }

}