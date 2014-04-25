<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\User;

class UserImagesDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\system\\users\\UsersImages";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_users_images` (`cDate`, `fileName`, `fizName`, `moderated`, `descr`, `uId`, `unick`, `width`)
		    VALUES (:cDate, :fileName, :fizName, :moderated, :descr, :uId, :unick, :width)");
		$this->updateStatement = $this->prepare("UPDATE `pn_users_images`
			SET `cDate`=:cDate, `fileName`=:fileName, `fizName`=:fizName, `moderated`=:moderated, `descr`=:descr, `uId`=:uId, `unick`=:unick, `width`=:width WHERE `id`=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_users_images` WHERE `id`=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_users_images` WHERE `id`=:id");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":cDate", $item->getCDate());
		$this->insertStatement->bindValue(":fileName", $item->getFileName());
		$this->insertStatement->bindValue(":fizName", $item->getFizName());
		$this->insertStatement->bindValue(":moderated", $item->getModerated());
		$this->insertStatement->bindValue(":descr", $item->getDescr());
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":unick", $item->getUnick());
		$this->insertStatement->bindValue(":width", $item->getWidth());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":cDate", $item->getCDate());
		$this->updateStatement->bindValue(":fileName", $item->getFileName());
		$this->updateStatement->bindValue(":fizName", $item->getFizName());
		$this->updateStatement->bindValue(":moderated", $item->getModerated());
		$this->updateStatement->bindValue(":descr", $item->getDescr());
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":unick", $item->getUnick());
		$this->updateStatement->bindValue(":width", $item->getWidth());
	}

	public function getCountByUser(User $user) {

		$stmt = $this->prepare('SELECT count(*) FROM `pn_users_images` WHERE `uId`=:uId');
		$stmt->execute([':uId' => $user->getId()]);

		return $stmt->fetchColumn();

	}

}