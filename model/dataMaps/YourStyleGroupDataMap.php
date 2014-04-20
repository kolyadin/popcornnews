<?php

namespace popcorn\model\dataMaps;

use popcorn\model\YourStyleGroup;
use popcorn\model\yourStyle\YourStyleGroupsTilesFactory;

class YourStyleGroupDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_TILE = 2;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleGroup";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_groups` (`createTime`, `title`, `rgId`, `tId`)
			VALUES (:createTime, :title, :rgId, :tId)");
		$this->updateStatement = $this->prepare("
			UPDATE `pn_yourstyle_groups` SET `createTime`=:createTime, `title`=:title, `rgId`=:rgId, `tId`=:tId WHERE `id`=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_groups` WHERE `id`=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_groups` WHERE `id`=:id");
    }

    protected function insertBindings($item) {
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":title", $item->getTitle());
		$this->insertStatement->bindValue(":rgId", $item->getRgId());
		$this->insertStatement->bindValue(":tId", $item->getTId());
    }

    protected function updateBindings($item) {
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
		$this->updateStatement->bindValue(":title", $item->getTitle());
		$this->updateStatement->bindValue(":rgId", $item->getRgId());
		$this->updateStatement->bindValue(":tId", $item->getTId());
    }

	public function getGroupsById($id) {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_groups`
			WHERE `rgId` = ?
			ORDER BY `id`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $id, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item, self::WITH_TILE);
        }

		return $items;
	}

	/**
	 * @param YourStyleGroupsDataMap $item
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_NONE) {

		$modifier = $this->getModifier($this, $modifier);

		parent::itemCallback($item);

		if ($modifier & self::WITH_TILE) {
			$item->setTId(YourStyleGroupsTilesFactory::getTile($item->getTId()));
		}

	}

}