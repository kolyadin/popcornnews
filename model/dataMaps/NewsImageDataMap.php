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
                WHERE l.newsId = :id ORDER BY l.seq ASC");
        $this->cleanStatement = $this->prepare("DELETE FROM pn_news_images WHERE newsId = :id");
        $this->insertStatement = $this->prepare("INSERT INTO pn_news_images (newsId, imageId, seq) VALUES (:id, :modelId, :seq)");
    }

	public function save($items, $id) {
		$this->checkStatement($this->cleanStatement);
		$this->checkStatement($this->insertStatement);
		$this->cleanStatement->bindValue(':id', $id);
		$this->cleanStatement->execute();

		if(empty($items)) {
			return;
		}

		foreach($items as $seq => $model) {
			$this->insertStatement->bindValue(':id', $id);
			$this->insertStatement->bindValue(':modelId', $model->getId());
			$this->insertStatement->bindValue(':seq',$seq);
			$this->insertStatement->execute();
		}
	}

    protected function mainDataMap() {
        return new ImageDataMap();
    }
}