<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleGroup;
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

	public function getGroupsByRootId($id, $orderBy = 1, $modifier = 2) {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_groups`
			WHERE `rgId` = ?
			ORDER BY ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $id, \PDO::PARAM_INT);
		$stmt->bindValue(2, $orderBy, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		switch($modifier) {
			case 1:
				$mod = self::WITH_NONE;
				break;
			case 2:
				$mod = self::WITH_TILE;
				break;
			default:
				$mod = self::WITH_NONE;
		}
		foreach($items as &$item) {
            $this->itemCallback($item, $mod);
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