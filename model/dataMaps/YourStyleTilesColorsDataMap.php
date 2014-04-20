<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleTilesColors;

class YourStyleTilesColorsDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleTilesColors";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_tiles_colors` (`tId`, `createTime`, `html`, `human`, `red`, `green`, `blue`, `alpha`, `pixels`)
		    VALUES (:tId, :createTime, :html, :human, :red, :green, :blue, :alpha, :pixels)");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_tiles_colors` WHERE `tId`=:tId");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":tId", $item->getTId());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":html", $item->getHtml());
		$this->insertStatement->bindValue(":human", $item->getHuman());
		$this->insertStatement->bindValue(":red", $item->getRed());
		$this->insertStatement->bindValue(":green", $item->getGreen());
		$this->insertStatement->bindValue(":blue", $item->getBlue());
		$this->insertStatement->bindValue(":alpha", $item->getAlpha());
		$this->insertStatement->bindValue(":pixels", $item->getPixels());
	}

	public function delete($tId) {

		$this->checkStatement($this->deleteStatement);
		$this->deleteStatement->bindValue(':tId', $tId);

		return $this->deleteStatement->execute();

	}
}