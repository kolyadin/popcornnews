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

	public function getTilesByGId($gId, $offset, $limit) {
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
            $this->itemCallback($item);
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
			SELECT `html`
			FROM `pn_yourstyle_tiles_colors`
			WHERE `human` = ?
			LIMIT 1
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $color, \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($items[0]['html'])) {
			return null;
		}

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
		$stmt->bindValue(1, $items[0]['html'], \PDO::PARAM_STR);
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
			SELECT `html`
			FROM `pn_yourstyle_tiles_colors`
			WHERE `human` = ?
			LIMIT 1
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $color, \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($items[0]['html'])) {
			return null;
		}

		$sql = <<<SQL
			SELECT count(`tid`) AS `cnt`
			FROM `pn_yourstyle_tiles_colors_new`
			WHERE `color` = ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $items[0]['html'], \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($items[0]['cnt'])) {
			return null;
		}

		return $items[0]['cnt'];
    }


	protected function itemCallback($item) {
		$item->setImage(YourStyleFactory::getWwwUploadTilesPath($item->getGId(), $item->getImage()));

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

}