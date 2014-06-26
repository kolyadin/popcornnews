<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleGroupsTiles;
use popcorn\model\dataMaps\YourStyleGroupDataMap;
use popcorn\model\dataMaps\YourStyleTilesBrandsDataMap;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\lib\yourstyle\YourStyleFactory;

class YourStyleGroupsTilesDataMap extends DataMap {

	const WITH_NONE = 1;

	public function __construct() {
        parent::__construct();

		$this->class = "popcorn\\model\\yourStyle\\YourStyleGroupsTiles";
        $this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_groups_tiles` (`gId`, `createTime`, `image`, `width`, `height`, `uId`, `description`, `bId`, `hidden`, `rate`, `price`, `colorMode`)
            VALUES (:gId, :createTime, :image, :width, :height, :uId, :description, :bId, :hidden, :rate, :price, :colorMode)");
        $this->updateStatement = $this->prepare("
            UPDATE `pn_yourstyle_groups_tiles` SET `gId`=:gId, `createTime`=:createTime,`image`=:image, `width`=:width, `height`=:height, `uId`=:uId, `description`=:description, `bId`=:bId, `hidden`=:hidden, `rate`=:rate, `price`=:price, `colorMode`=:colorMode WHERE `id`=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_groups_tiles` WHERE `id`=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_groups_tiles` WHERE `id`=:id");
        $this->countTilesByGIdStatement = $this->prepare("SELECT count(`id`) FROM `pn_yourstyle_groups_tiles` WHERE `gId`=:gId");
		$this->countTilesByBIdStatement = $this->prepare("SELECT count(`id`) FROM `pn_yourstyle_groups_tiles` WHERE `bId`=:bId");

    }

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":gId", $item->getGId());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":image", $item->getImage());
		$this->insertStatement->bindValue(":width", $item->getWidth());
		$this->insertStatement->bindValue(":height", $item->getHeight());
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":description", $item->getDescription());
		$this->insertStatement->bindValue(":bId", $item->getBId());
		$this->insertStatement->bindValue(":hidden", $item->getHidden());
		$this->insertStatement->bindValue(":rate", $item->getRate());
		$this->insertStatement->bindValue(":price", $item->getPrice());
		$this->insertStatement->bindValue(":colorMode", $item->getColorMode());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":gId", $item->getGId());
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
		$this->updateStatement->bindValue(":image", $item->getImage());
		$this->updateStatement->bindValue(":width", $item->getWidth());
		$this->updateStatement->bindValue(":height", $item->getHeight());
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":description", $item->getDescription());
		$this->updateStatement->bindValue(":bId", $item->getBId());
		$this->updateStatement->bindValue(":hidden", $item->getHidden());
		$this->updateStatement->bindValue(":rate", $item->getRate());
		$this->updateStatement->bindValue(":price", $item->getPrice());
		$this->updateStatement->bindValue(":colorMode", $item->getColorMode());
	}

	public function getTilesByGId($gId, $offset, $limit, $imageSize = '') {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_groups_tiles`
			WHERE `gId` = ?
			ORDER BY `id` DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $gId, \PDO::PARAM_INT);
		$stmt->bindValue(2, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(3, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item, $imageSize);
        }

		return $items;

	}

	public function getTilesByBId($bId, $offset, $limit) {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_groups_tiles`
			WHERE `bId` = ?
			ORDER BY `id` DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $bId, \PDO::PARAM_INT);
		$stmt->bindValue(2, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(3, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;

	}

	public function getTilesByColor($color, $offset, $limit) {

		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_groups_tiles`
			WHERE `id` IN (SELECT `tid`
							FROM `pn_yourstyle_tiles_colors_new`
							WHERE `color` = ?)
			ORDER BY `id` DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $color, \PDO::PARAM_STR);
		$stmt->bindValue(2, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(3, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
		if ($items === false) {
			return null;
		}

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;

	}

	public function getCountByGId($gId) {

		$stmt = $this->countTilesByGIdStatement;
        $stmt->bindValue(':gId', $gId);
        $stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();

        return $count;

    }

	public function getCountByBId($bId) {

		$stmt = $this->countTilesByBIdStatement;
        $stmt->bindValue(':bId', $bId);
        $stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();

        return $count;

    }

	public function getCountByColor($color) {

		$sql = <<<SQL
			SELECT count(`tid`) AS `cnt`
			FROM `pn_yourstyle_tiles_colors_new`
			WHERE `color` = ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $color, \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($items[0]['cnt'])) {
			return null;
		}

		return $items[0]['cnt'];
    }


	protected function itemCallback($item, $imageSize = '') {
		$item->setImage(YourStyleFactory::getWwwUploadTilesPath($item->getGId(), $item->getImage(), $imageSize));

		$dataMap = new YourStyleTilesBrandsDataMap();
		$brand = $dataMap->findById($item->getBId());
		if ($brand) {
			$item->setBId($brand->getTitle());
		} else {
			$item->setBId('');
		}

		$dataMapHelper = new DataMapHelper();
		$dataMapHelper->setRelationship([
			'popcorn\\model\\yourStyle\\YourStyleGroupsTiles' => YourStyleGroupDataMap::WITH_NONE,
		]);

		$dataMap = new YourStyleGroupDataMap($dataMapHelper);
		$group = $dataMap->findById($item->getGId());
		if ($group) {
			$item->setGId($group->getTitle());
		} else {
			$item->setGId('');
		}

//		$item->setTitle(($item->getTitle()));

		parent::itemCallback($item);
	}

	public function getTop($offset = 0, $limit = 50, $imageSize = '') {
		$sql = <<<SQL
			SELECT a.*,
				c.title brand,
				COUNT(DISTINCT v.uid) as votes,
				IF(COUNT(DISTINCT v.uid) > 0, ROUND(a.rate/COUNT(DISTINCT v.uid),1), 0) as rating
			FROM `pn_yourstyle_groups_tiles` a
				LEFT JOIN pn_yourstyle_tiles_brands c ON (c.id = a.bid)
				LEFT JOIN pn_yourstyle_groups_tiles_votes as v ON (v.tid = a.id)
			WHERE a.gid != 0
			GROUP BY a.id
			ORDER BY rating DESC, a.createtime DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(2, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item, $imageSize);
        }

		return $items;

	}

	protected function getConditions($group, $brand, $color) {

		$condition = array();
		if ($group) {
			$condition[] = '`gId` = :gId';
		}
		if ($brand) {
			$condition[] = '`bId` = :bId';
		}
		if ($color) {
			$condition[] = '`id` IN (SELECT `tid` FROM `pn_yourstyle_tiles_colors_new` WHERE `color` = :color)';
		}

		if (!count($condition)) {
			return false;
		}
		$condition = ' AND ' . implode(' AND ', $condition);

		return $condition;

	}

	public function getTilesByParams($group, $brand, $color, $offset, $limit, $imageSize = '') {

		$condition = $this->getConditions($group, $brand, $color);
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_groups_tiles`
			WHERE 1 = 1
				$condition
			ORDER BY `id` DESC
			LIMIT :offset, :limit
SQL;

		$stmt = $this->prepare($sql);
		if ($group) {
			$stmt->bindValue(":gId", $group, \PDO::PARAM_INT);
		}
		if ($brand) {
			$stmt->bindValue(":bId", $brand, \PDO::PARAM_INT);
		}
		if ($color) {
			$stmt->bindValue(":color", $color, \PDO::PARAM_STR);
		}
		$stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
		$stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);

		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
		if ($items === false) {
			return null;
		}

		foreach($items as &$item) {
            $this->itemCallback($item, $imageSize);
        }

		return $items;

	}

	public function getTilesTopByParams($group, $brand, $color, $offset, $limit, $imageSize = '') {

		$condition = $this->getConditions($group, $brand, $color);
		$sql = <<<SQL
			SELECT `a`.*,
				`c`.`title` brand,
				COUNT(DISTINCT `v`.`uId`) AS `votes`,
				IF(COUNT(DISTINCT `v`.`uId`) > 0, ROUND(`a`.`rate`/COUNT(DISTINCT `v`.`uId`),1), 0) AS `rating`
			FROM `pn_yourstyle_groups_tiles` AS `a`
				LEFT JOIN `pn_yourstyle_tiles_brands` c ON (`c`.`id` = `a`.`bId`)
				LEFT JOIN `pn_yourstyle_groups_tiles_votes` as v ON (`v`.`tId` = `a`.`id`)
			WHERE `a`.`gId` != 0
				$condition
			GROUP BY a.id
			ORDER BY `rating` DESC, `a`.`createTime` DESC
			LIMIT :offset, :limit
SQL;

		$stmt = $this->prepare($sql);
		if ($group) {
			$stmt->bindValue(":gId", $group, \PDO::PARAM_INT);
		}
		if ($brand) {
			$stmt->bindValue(":bId", $brand, \PDO::PARAM_INT);
		}
		if ($color) {
			$stmt->bindValue(":color", $color, \PDO::PARAM_STR);
		}
		$stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
		$stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);

		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
		if ($items === false) {
			return null;
		}

		foreach($items as &$item) {
            $this->itemCallback($item, $imageSize);
        }

		return $items;

	}

	public function getCountByParams($group, $brand, $color) {

		$condition = $this->getConditions($group, $brand, $color);
		$sql = <<<SQL
			SELECT COUNT(*)
			FROM `pn_yourstyle_groups_tiles`
			WHERE 1 = 1
				$condition
SQL;

		$stmt = $this->prepare($sql);
		if ($group) {
			$stmt->bindValue(":gId", $group, \PDO::PARAM_INT);
		}
		if ($brand) {
			$stmt->bindValue(":bId", $brand, \PDO::PARAM_INT);
		}
		if ($color) {
			$stmt->bindValue(":color", $color, \PDO::PARAM_STR);
		}
		$stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();

        return $count;

	}

	public function getTile($tId) {

		$sql = <<<SQL
			SELECT `a`.*, `b`.`title` AS `brand`, `g`.`title` AS `groupTitle`
			FROM `pn_yourstyle_groups_tiles` AS `a`
				LEFT JOIN `pn_yourstyle_tiles_brands` AS `b` ON (`b`.`id` = `a`.`bid`)
				INNER JOIN `pn_yourstyle_groups` AS `g` ON (`g`.`id` = `a`.`gid`)
			WHERE `a`.`id` = ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $tId, \PDO::PARAM_INT);
		$stmt->execute();

		$item = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($item === false) return null;

		$item = $item[0];
		$item->setImage(YourStyleFactory::getWwwUploadTilesPath($item->getGId(), $item->getImage()));

		return $item;

	}

}