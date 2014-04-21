<?php
/**
 * User: anubis
 * Date: 15.10.13
 * Time: 1:44
 */

namespace popcorn\model\dataMaps;


use popcorn\model\content\AlbumImage;
use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\SaveFirstException;

class AlbumImagesDataMap extends ImageDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = 'popcorn\\model\\content\\AlbumImage';
        $this->deleteStatement = $this->prepare("DELETE FROM pn_album_images WHERE id = :id");
        $this->insertStatement
            = $this->prepare("
                INSERT INTO pn_album_images (albumId, imageId, enable, `order`, `createTime`)
                VALUES (:albumId, :imageId, :enable, :orderVal, :createTime)");
        $this->updateStatement
            = $this->prepare("
                UPDATE pn_album_images
                SET enable = :enable, `order` = :orderVal
                WHERE id = :id");
        $this->findOneStatement
            = $this->prepare("
                SELECT l.id,
                   l.albumId,
                   i.id as imageId,
                   l.enable,
                   l.`order`,
                   l.createTime,
                   i.name,
                   i.title,
                   i.source,
                   i.zoomable,
                   i.description,
                   i.width,
                   i.height FROM pn_images AS i
                INNER JOIN pn_album_images AS l ON (l.imageId = i.id)
                WHERE l.id = :id");
        $this->countAllStatement
            = $this->prepare("SELECT count(id) FROM pn_album_images WHERE albumId=:albumId");
        $this->countEnabledStatement
            = $this->prepare("SELECT count(id) FROM pn_album_images WHERE albumId=:albumId AND enable = 1");
        $this->countDisabledStatement
            = $this->prepare("SELECT count(id) FROM pn_album_images WHERE albumId=:albumId AND enable = 0");
    }

    /**
     * @param $id
     * @param $from
     * @param $count
     * @return AlbumImage[]
     */
    public function findByAlbumId($id, $from, $count) {
        $sql = "SELECT
                   l.id,
                   l.albumId,
                   i.id as imageId,
                   l.enable,
                   l.`order`,
                   l.createTime,
                   i.name,
                   i.title,
                   i.source,
                   i.zoomable,
                   i.description,
                   i.width,
                   i.height
                FROM pn_images AS i
                INNER JOIN pn_album_images AS l ON (l.imageId = i.id)
                WHERE l.albumId = :id ORDER BY l.`order` DESC, l.createTime DESC".$this->getLimitString($from, $count);
        $items = $this->fetchAll($sql, array('id' => $id));
        return $items;
    }

    /**
     * @param $albumId
     * @param int $enabled
     *
     * @return int
     */
    public function getCount($albumId, $enabled = null) {
        if($enabled === 0) {
            $count = $this->count($this->countDisabledStatement, $albumId);
        }
        elseif($enabled === 1) {
            $count = $this->count($this->countEnabledStatement, $albumId);
        }
        else {
            $count = $this->count($this->countAllStatement, $albumId);
        }
        return $count;
    }

    /**
     * @param AlbumImage $item
     */
    protected function itemCallback($item) {
        ($item->{'enable'} == 1) ? $item->enable() : $item->disable();
        unset($item->{'enable'});
        parent::itemCallback($item);
    }

    /**
     * @param AlbumImage $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(':albumId', $item->getAlbumId());
        $this->insertStatement->bindValue(':imageId', $item->getImageId());
        $this->insertStatement->bindValue(':enable', $item->isEnabled(), \PDO::PARAM_BOOL);
        $this->insertStatement->bindValue(':orderVal', $item->getOrder());
        $this->insertStatement->bindValue(':createTime', $item->getCreateTime());
    }

    /**
     * @param AlbumImage $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(':enable', $item->isEnabled(), \PDO::PARAM_BOOL);
        $this->updateStatement->bindValue(':orderVal', $item->getOrder());
        $this->updateStatement->bindValue(':id', $item->getId());
    }

    /**
     * @param \PDOStatement $stmt
     * @param $albumId
     * @return int
     */
    private function count($stmt, $albumId) {
        $stmt->bindValue(':albumId', $albumId);
        $stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();
        return $count;
    }

}