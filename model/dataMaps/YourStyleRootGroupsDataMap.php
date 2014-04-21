<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleRootGroups;
use popcorn\model\yourStyle\YourStyleGroupsTilesFactory;

class YourStyleRootGroupsDataMap extends DataMap {

	public function __construct() {
        parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleRootGroups";
        $this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_root_groups` (`createTime`, `title`, `tId`)
            VALUES (:createTime, :title, :tId)");
        $this->updateStatement = $this->prepare("
            UPDATE `pn_yourstyle_root_groups` SET `createTime`=:createTime, `title`=:title, `tId`=:tId WHERE `id`=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_root_groups` WHERE `id`=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_root_groups` WHERE `id`=:id");
    }

    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":createTime", $item->getCreateTime());
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":tId", $item->getTId());
    }

    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":createTime", $item->getCreateTime());
        $this->updateStatement->bindValue(":title", $item->getTitle());
        $this->updateStatement->bindValue(":tId", $item->getTId());
    }

	public function getRootGroups() {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_root_groups`
			ORDER BY `id`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;
	}

	/**
	 * @param YourStyleRootGroupsDataMap $item
	 * @param int $modifier
	 */
	protected function itemCallback($item/*, $modifier = self::WITH_ALL*/) {

//		$modifier = $this->getModifier($this, $modifier);

		parent::itemCallback($item);

		$item->setTId(YourStyleGroupsTilesFactory::getTile($item->getTId()));

	}

}