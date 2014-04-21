<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\UserHash;

class UserHashDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\system\\users\\UserHash";
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_users_hash (id, securityHash) VALUES (:id, :securityHash)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_users_hash SET id=:id, securityHash=:securityHash WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_users_hash WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_users_hash WHERE id=:id");
	}

	/**
	 * @param UserHash $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":id", $item->getId());
		$this->insertStatement->bindValue(":securityHash", $item->getSecurityHash());
	}

	/**
	 * @param UserHash $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":id", $item->getId());
		$this->updateStatement->bindValue(":securityHash", $item->getSecurityHash());
	}

}