<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleSetsTags;

class YourStyleSetsTagsDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleSetsTags";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_sets_tags` (`sId`, `tId`, `uId`, `createTime`)
		    VALUES (:sId, :tId, :uId, :createTime)");
		$this->updateStatement = $this->prepare("UPDATE `pn_yourstyle_sets_tags`
			SET `tId`=:tId, `uId`=:uId, `createTime`=:createTime WHERE `sId`=:sId");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_sets_tags` WHERE `sId`=:sId");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_sets_tags` WHERE `sId`=:sId");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":sId", $item->getSId());
		$this->insertStatement->bindValue(":tId", $item->getTId());
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":tId", $item->getTId());
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
	}

}