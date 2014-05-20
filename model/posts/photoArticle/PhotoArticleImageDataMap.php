<?php

namespace popcorn\model\posts\photoArticle;

use popcorn\model\dataMaps\ImageDataMap;
use popcorn\model\dataMaps\NewsImageDataMap;

class PhotoArticleImageDataMap extends NewsImageDataMap {

    function __construct() {
        parent::__construct();

        $this->findLinkedStatement =
            $this->prepare("
                SELECT i.* FROM pn_images AS i
                LEFT JOIN pn_photoarticles_images AS l ON (l.imageId = i.id)
                WHERE l.photoarticleId = :id ORDER BY l.seq ASC");
        $this->cleanStatement = $this->prepare("DELETE FROM pn_photoarticles_images WHERE photoarticleId = :id");
        $this->insertStatement = $this->prepare("INSERT INTO pn_photoarticles_images (photoarticleId, imageId, seq) VALUES (:id, :modelId, :seq)");
    }

    protected function mainDataMap() {
        return new ImageDataMap();
    }
}