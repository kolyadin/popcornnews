<?php

namespace popcorn\model\dataMaps;

use popcorn\model\YourStyleTilesBrands;

class YourStyleTilesBrandsDataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\yourStyle\\YourStyleTilesBrands";
        $this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_tiles_brands` (`createTime`, `title`, `logo`, `descr`)
            VALUES (:createTime, :title, :logo, :descr)");
        $this->updateStatement = $this->prepare("
            UPDATE `pn_yourstyle_tiles_brands` SET `createTime`=:createTime, `title`=:title, `logo`=:logo, `descr`=:descr WHERE `id`=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_tiles_brands` WHERE `id`=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_tiles_brands` WHERE `id`=:id");
    }

    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":createTime", $item->getCreateTime());
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":logo", $item->getLogo());
        $this->insertStatement->bindValue(":descr", $item->getDescr());
    }

    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":createTime", $item->getCreateTime());
        $this->updateStatement->bindValue(":title", $item->getTitle());
        $this->updateStatement->bindValue(":logo", $item->getLogo());
        $this->updateStatement->bindValue(":descr", $item->getDescr());
    }

	public function getBransByStr($str) {

		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_tiles_brands`
			WHERE `title` LIKE ?
			ORDER BY `id`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $str . '%', \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;

	}
}