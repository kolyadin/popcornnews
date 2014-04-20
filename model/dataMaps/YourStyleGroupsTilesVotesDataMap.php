<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleGroupsTilesVotes;

class YourStyleGroupsTilesVotesDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleGroupsTilesVotes";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_groups_tiles_votes` (`uId`, `tId`, `ip`, `createTime`)
		    VALUES (:uId, :tId, :ip, :createTime)");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_groups_tiles_votes` WHERE `uId`=:uId AND `tId`=:tId");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":tId", $item->getTId());
		$this->insertStatement->bindValue(":ip", $item->getIp());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
	}

	public function getCountVotes($uId, $tId) {

		$sql = <<<SQL
			SELECT COUNT(*) AS `cnt`
			FROM `pn_yourstyle_groups_tiles_votes`
			WHERE `uId` = ?
				AND `tId` = ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $uId, \PDO::PARAM_STR);
		$stmt->bindValue(2, $tId, \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return 0;

		return $items[0]->cnt;

	}
}