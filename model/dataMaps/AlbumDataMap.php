<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\Album;
use popcorn\model\content\ImageFactory;
use popcorn\model\system\users\UserFactory;

class AlbumDataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\content\\Album";
        $this->insertStatement =
            $this->prepare("
                INSERT INTO pn_albums (title, createTime, poster, owner)
                VALUES (:title, :createTime, :poster, :owner)");
        $this->updateStatement =
            $this->prepare("UPDATE pn_albums SET title=:title, createTime=:createTime, poster=:poster WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_albums WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_albums WHERE id=:id");
    }

    /**
     * @param Album $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":createTime", $item->getCreateTime());
        $this->insertStatement->bindValue(":poster", $item->getPoster()->convert());
        $this->insertStatement->bindValue(":owner", $item->getOwner()->convert());
    }

    /**
     * @param Album $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":title", $item->getTitle());
        $this->updateStatement->bindValue(":createTime", $item->getCreateTime());
        $this->updateStatement->bindValue(":poster", $item->getPoster()->convert());
    }

    /**
     * @param Album $item
     */
    protected function itemCallback($item) {
        $item->setPoster(ImageFactory::getImage($item->getPoster()));
        $item->setOwner(UserFactory::getUser($item->getOwner()));
        parent::itemCallback($item);
    }

    /**
     * @param Album $item
     */
    protected function onSave($item) {
        parent::onSave($item);
        //$this->images->save($item->getImages(), $item->getId());
    }

}