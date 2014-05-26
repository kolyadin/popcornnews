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
                WHERE l.postId = :id ORDER BY l.seq ASC");

		$this->findMainImageStatement =
			$this->prepare("
                SELECT i.* FROM pn_images AS i
                LEFT JOIN pn_photoarticles_images AS l ON (l.imageId = i.id)
                WHERE l.postId = :id ORDER BY l.seq ASC LIMIT 1");

		$this->cleanStatement = $this->prepare("DELETE FROM pn_photoarticles_images WHERE postId = :id");
		$this->insertStatement = $this->prepare("INSERT INTO pn_photoarticles_images (postId, imageId, seq) VALUES (:id, :modelId, :seq)");
	}

	public function getMainImage($postId) {
		$this->checkStatement($this->findMainImageStatement);

		$this->findMainImageStatement->bindValue(':id', $postId);
		$this->findMainImageStatement->execute();

		$item = $this->findMainImageStatement->fetchAll(\PDO::FETCH_CLASS, $this->class)[0];
		$this->mainDataMap()->itemCallback($item);

		return $item;
	}

	protected function mainDataMap() {
		return new ImageDataMap();
	}
}