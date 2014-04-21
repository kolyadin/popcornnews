<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleTilesBrands;
use popcorn\lib\yourstyle\YourStyleFactory;

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

	public function getBrands() {

		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_tiles_brands`
			WHERE `id` <> 140
			ORDER BY `title`
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

	public function getTopBrands($limit) {

		$sql = <<<SQL
			SELECT b.*, COUNT(DISTINCT st.sid) as sets
			FROM pn_yourstyle_tiles_brands as b
				INNER JOIN pn_yourstyle_groups_tiles as t ON (t.bid = b.id)
				INNER JOIN pn_yourstyle_sets_tiles as st ON (t.id = st.tid)
				INNER JOIN pn_yourstyle_sets as s ON (s.id = st.sid)
			WHERE t.gid <> 0
				AND b.logo > ''
				AND s.isDraft = 'n'
			GROUP BY b.id
			ORDER BY sets DESC
			LIMIT ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;

	}

	protected function itemCallback($item) {

		$item->setLogo(YourStyleFactory::getWwwUploadBrandsPath($item->getId(), $item->getLogo(), '100x100'));

		parent::itemCallback($item);

	}

	public function getBrandsByRootGroop($rgId) {

		$sql = <<<SQL
			SELECT b.*
			FROM `pn_yourstyle_tiles_brands` as b
				INNER JOIN pn_yourstyle_groups_tiles as t ON (t.bid = b.id)
				INNER JOIN pn_yourstyle_groups as g ON (t.gid = g.id)
			WHERE g.rgid = ?
				AND b.id <> 140
			GROUP BY b.id
			ORDER BY b.title
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $rgId, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;

	}


}