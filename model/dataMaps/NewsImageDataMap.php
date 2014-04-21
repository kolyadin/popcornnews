<?php
/**
 * User: anubis
 * Date: 02.09.13 15:04
 */

namespace popcorn\model\dataMaps;

class NewsImageDataMap extends CrossLinkedDataMap {

    function __construct() {
        parent::__construct();
        $this->findLinkedStatement =
            $this->prepare("
                SELECT i.* FROM pn_images AS i
                LEFT JOIN pn_news_images AS l ON (l.imageId = i.id)
                WHERE l.newsId = :id");
        $this->cleanStatement = $this->prepare("DELETE FROM pn_news_images WHERE newsId = :id");
        $this->insertStatement = $this->prepare("INSERT INTO pn_news_images (newsId, imageId) VALUES (:id, :modelId)");
    }

    protected function mainDataMap() {
        return new ImageDataMap();
    }
}