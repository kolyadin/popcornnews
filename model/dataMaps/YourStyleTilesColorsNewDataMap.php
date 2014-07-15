<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleTilesColorsNew;

class YourStyleTilesColorsNewDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleTilesColorsNew";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_tiles_colors_new` (`color`, `tId`, `priority`)
		    VALUES (:color, :tId, :priority)");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_tiles_colors_new` WHERE `tId`=:tId");
		$this->findOneStatement = $this->prepare("SELECT DISTINCT `color`, `human` FROM `pn_yourstyle_tiles_colors_new` JOIN `pn_yourstyle_tiles_colors` ON `html` = `color` WHERE `pn_yourstyle_tiles_colors_new`.`tId`=:tId");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":color", $item->getColor());
		$this->insertStatement->bindValue(":tId", $item->getTId());
		$this->insertStatement->bindValue(":priority", $item->getPriority());
	}

	public function findById($tId) {

		$this->findOneStatement->bindValue(':tId', $tId);
		$this->findOneStatement->execute();

		$items = $this->findOneStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

	public function delete($tId) {

		$this->checkStatement($this->deleteStatement);
		$this->deleteStatement->bindValue(':tId', $tId);

		return $this->deleteStatement->execute();

	}

	public function getColors() {

		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_tiles_colors_new`
			GROUP BY `color`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

	public function getColorsByRootGroup($rgId) {

		$sql = <<<SQL
			SELECT `c`.*
			FROM `pn_yourstyle_tiles_colors_new` AS `c`
				INNER JOIN `pn_yourstyle_groups_tiles` AS `t` ON (`t`.`id` = `c`.`tid`)
				INNER JOIN `pn_yourstyle_groups` AS `g` ON (`g`.`id` = `t`.`gid`)
			WHERE `g`.`rgid` = ?
				AND `t`.`gid` <> 0
			GROUP BY `c`.`color`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $rgId, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

}