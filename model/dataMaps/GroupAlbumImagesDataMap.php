<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\AlbumImage;

class GroupAlbumImagesDataMap extends ImageDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = 'popcorn\\model\\groups\\AlbumImage';
        $this->deleteStatement = $this->prepare("DELETE FROM pn_groups_albums_photos WHERE id = :id");
        $this->insertStatement
            = $this->prepare("
                INSERT INTO pn_groups_albums_photos (albumId, imageId, createdAt, `enable`, `seq`)
                VALUES (:albumId, :imageId, :createdAt, :enable, :seq)");
        $this->updateStatement
            = $this->prepare("
                UPDATE pn_groups_albums_photos
                SET `enable` = :enable, `seq` = :seq
                WHERE id = :id");
        $this->findOneStatement
            = $this->prepare("
                SELECT l.id,
                   l.albumId,
                   i.id as imageId,
                   l.enable,
                   l.seq,
                   l.createdAt,
                   i.name,
                   i.title,
                   i.source,
                   i.zoomable,
                   i.description,
                   i.width,
                   i.height FROM pn_images AS i
                INNER JOIN pn_groups_albums_photos AS l ON (l.imageId = i.id)
                WHERE l.id = :id");
        $this->countAllStatement
            = $this->prepare("SELECT count(id) FROM pn_groups_albums_photos WHERE albumId=:albumId");
        $this->countEnabledStatement
            = $this->prepare("SELECT count(id) FROM pn_groups_albums_photos WHERE albumId=:albumId AND enable = 1");
        $this->countDisabledStatement
            = $this->prepare("SELECT count(id) FROM pn_groups_albums_photos WHERE albumId=:albumId AND enable = 0");
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
                INNER JOIN pn_groups_albums_photos AS l ON (l.imageId = i.id)
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
        $this->insertStatement->bindValue(':seq', $item->getOrder());
        $this->insertStatement->bindValue(':createdAt', $item->getCreateTime());
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