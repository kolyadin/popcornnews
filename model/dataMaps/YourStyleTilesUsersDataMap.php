<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\User;
use popcorn\model\yourStyle\YourStyleTilesUsers;
use popcorn\lib\yourstyle\YourStyleFactory;
use popcorn\model\dataMaps\UserDataMap;

class YourStyleTilesUsersDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleTilesUsers";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_tiles_users` (`tId`, `uId`, `createTime`)
		    VALUES (:tId, :uId, :createTime)");
		$this->updateStatement = $this->prepare("UPDATE `pn_yourstyle_tiles_users`
			SET `tId`=:tId, `createTime`=:createTime WHERE `uId`=:uId");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_tiles_users` WHERE `tId`=:tId AND `uId`=:uId");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_tiles_users` WHERE `tId`=:tId AND `uId`=:uId");
		$this->countUsersByTile = $this->prepare("SELECT COUNT(DISTINCT(`uId`)) FROM `pn_yourstyle_tiles_users` WHERE `tId` = :tId");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":tId", $item->getTId());
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":tId", $item->getTId());
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
	}

	public function getUserTiles(User $user) {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_tiles_users`
			WHERE `uId` = ?
			ORDER BY `createTime` DESC
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;
	}

	public function delete($tId, User $user) {

		$this->deleteStatement->bindValue(':tId', $tId);
		$this->deleteStatement->bindValue(':uId', $user->getId());

		return $this->deleteStatement->execute();

	}

	public function findById($tId, User $user) {

		$this->findOneStatement->bindValue(':tId', $tId);
		$this->findOneStatement->bindValue(':uId', $user->getId());
		$this->findOneStatement->execute();

		$items = $this->findOneStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

	public function getUsersByTile($tId) {

		$sql = <<<SQL
			SELECT DISTINCT(`uId`) as `uId`
			FROM `pn_yourstyle_tiles_users`
			WHERE `tId` = ?
			ORDER BY `createTime`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $tId, \PDO::PARAM_INT);
		$stmt->execute();

		$users = [];
		$dataMap = new UserDataMap();
		while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$users[] = $dataMap->findById($item['uId']);
		}

		return $users;

	}

	public function getCountUsersByTile($tId) {

		$stmt = $this->countUsersByTile;
		$stmt->bindValue(':tId', $tId);
		$stmt->execute();
		$count = $stmt->fetchColumn(0);
		$stmt->closeCursor();

		return $count;

	}

}