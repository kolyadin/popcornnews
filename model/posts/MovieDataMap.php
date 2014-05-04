<?php

namespace popcorn\model\posts;

use popcorn\model\posts\Movie;
use popcorn\model\dataMaps\DataMap;

class MovieDataMap extends DataMap {

	public function __construct() {

		parent::__construct();

		$this->class = "popcorn\\model\\posts\\Movie";
		$this->initStatements();
	}

	private function initStatements() {
		$this->insertStatement = null;
		$this->updateStatement = null;
		$this->deleteStatement = null;
		$this->findOneStatement = $this->prepare("SELECT * FROM ka_movies WHERE id=:id");
	}

	/**
	 * @param Movie $item
	 */
	protected function insertBindings($item) {
	}

	/**
	 * @param Movie $item
	 */
	protected function updateBindings($item) {
	}



	public function findByDate($from = 0, $count = -1) {
		$sql = "SELECT * FROM pn_news ORDER BY createDate DESC";
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll($sql);
	}

}