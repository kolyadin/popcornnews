<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\Image;

class ImageDataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\content\\Image";
        $this->insertStatement =
            $this->prepare("
            INSERT INTO pn_images (name, title, source, zoomable, description, createTime, width, height)
            VALUES (:name, :title, :source, :zoomable, :description, :createTime, :width, :height)");
        $this->updateStatement =
            $this->prepare("
            UPDATE pn_images
            SET name=:name, title=:title, source=:source, zoomable=:zoomable, description=:description WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_images WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_images WHERE id=:id");
    }

    /**
     * @param Image $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":name", $item->getName());
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":source", $item->getSource());
        $this->insertStatement->bindValue(":zoomable", $item->isZoomable());
        $this->insertStatement->bindValue(":description", $item->getDescription());
        $this->insertStatement->bindValue(":createTime", $item->getCreateTime());
        $this->insertStatement->bindValue(":width", $item->getWidth());
        $this->insertStatement->bindValue(":height", $item->getHeight());
    }

    /**
     * @param Image $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":name", $item->getName());
        $this->updateStatement->bindValue(":title", $item->getTitle());
        $this->updateStatement->bindValue(":source", $item->getSource());
        $this->updateStatement->bindValue(":zoomable", $item->isZoomable());
        $this->updateStatement->bindValue(":description", $item->getDescription());
        $this->updateStatement->bindValue(":id", $item->getId());
    }

}