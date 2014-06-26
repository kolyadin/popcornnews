<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\User;
use popcorn\model\yourStyle\YourStyleSetsTiles;
use popcorn\lib\yourstyle\YourStyleFactory;

class YourStyleSetsTilesDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleSetsTiles";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_sets_tiles`
			(`sId`, `tId`, `width`, `height`, `leftOffset`, `topOffset`, `vFlip`, `hFlip`, `createTime`, `sequence`, `image`, `uId`, `underlay`)
		    VALUES (:sId, :tId, :width, :height, :leftOffset, :topOffset, :vFlip, :hFlip, :createTime, :sequence, :image, :uId, :underlay)");
		$this->updateStatement = $this->prepare("UPDATE `pn_yourstyle_sets_tiles`
			SET `tId`=:tId, `width`=:width, `height`=:height, `leftOffset`=:leftOffset, `topOffset`=:topOffset, `vFlip`=:vFlip, `hFlip`=:hFlip,	`createTime`=:createTime, `sequence`=:sequence, `image`=:image, `uId`=:uId, `underlay`=:underlay WHERE `sId`=:sId");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_sets_tiles` WHERE `sId`=:sId");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_sets_tiles` WHERE `sId`=:sId");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":sId", $item->getSId());
		$this->insertStatement->bindValue(":tId", $item->getTId());
		$this->insertStatement->bindValue(":width", $item->getWidth());
		$this->insertStatement->bindValue(":height", $item->getHeight());
		$this->insertStatement->bindValue(":leftOffset", $item->getLeftOffset());
		$this->insertStatement->bindValue(":topOffset", $item->getTopOffset());
		$this->insertStatement->bindValue(":vFlip", $item->getVFlip());
		$this->insertStatement->bindValue(":hFlip", $item->getHFlip());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":sequence", $item->getSequence());
		$this->insertStatement->bindValue(":image", $item->getImage());
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":underlay", $item->getUnderlay());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":tId", $item->getTId());
		$this->updateStatement->bindValue(":width", $item->getWidth());
		$this->updateStatement->bindValue(":height", $item->getHeight());
		$this->updateStatement->bindValue(":leftOffset", $item->getLeftOffset());
		$this->updateStatement->bindValue(":topOffset", $item->getTopOffset());
		$this->updateStatement->bindValue(":vFlip", $item->getVFlip());
		$this->updateStatement->bindValue(":hFlip", $item->getHFlip());
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
		$this->updateStatement->bindValue(":sequence", $item->getSequence());
		$this->updateStatement->bindValue(":image", $item->getImage());
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":underlay", $item->getUnderlay());
	}

	public function getSetTiles($sId) {

		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_sets_tiles`
			WHERE `sId` = ?
			ORDER BY `sequence`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $sId, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;

	}

	protected function itemCallback($item) {

		$dataMap = new YourStyleGroupsTilesDataMap();
		$gTiles = $dataMap->findById($item->getTId());
		//die(json_encode(['error' => print_r($gTiles, 1)]));
		//$item->setImage(YourStyleFactory::getWwwUploadTilesPath($gTiles->getGId(), $item->getImage()));
		$item->setImage($gTiles->getImage());
		$item->setVFlip($item->getVFlip() == 'y' ? true : false);
		$item->setHFlip($item->getHFlip() == 'y' ? true : false);
		$item->setUnderlay($item->getUnderlay() == 'y' ? true : false);

	}

	public function delete($sId) {

		$this->deleteStatement->bindValue(':sId', $sId);

		return $this->deleteStatement->execute();

	}

	public function findById($sId) {//А зачем она вообще неужна тут? Она должна возвращать несколько строк... Но это уже делает getSetTiles

		$this->checkStatement($this->findOneStatement);
		$this->findOneStatement->bindValue(':sId', $sId);
		$this->findOneStatement->execute();
		$item = empty($this->class)
			? $this->findOneStatement->fetch(PDO::FETCH_ASSOC)
			: $this->findOneStatement->fetchObject($this->class);
		$this->findOneStatement->closeCursor();
		if ($item === false) {
			return null;
		} else {
			$this->itemCallback($item);
			return $item;
		}

	}

	public function getTilesInSet($sId) {

		$sql = <<<SQL
			SELECT `b`.*, `c`.`title` AS `brand`, `g`.`title` AS `group`
			FROM `pn_yourstyle_sets_tiles` a
				JOIN `pn_yourstyle_groups_tiles` AS `b` ON (`b`.`id` = `a`.`tId`)
				LEFT JOIN `pn_yourstyle_tiles_brands` AS `c` ON (`c`.`id` = `b`.`bId`)
				LEFT JOIN `pn_yourstyle_groups` AS `g` ON (`g`.`id` = `b`.`gId`)
			WHERE `a`.`sId` = ?
			GROUP BY `b`.`id`
			ORDER BY `createTime`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $sId, \PDO::PARAM_INT);
		$stmt->execute();

		$tiles = [];
		while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$item['logo'] = YourStyleFactory::getWwwUploadTilesPath($item['gId'], $item['image']);
			$tiles[] = $item;
		}

		return $tiles;

	}

	public function getSetsByTile($tId, $offset, $limit) {

		$sql = <<<SQL
			SELECT DISTINCT(`sId`) as `sId`
			FROM `pn_yourstyle_sets_tiles`
			WHERE `tId` = ?
			ORDER BY `sId`
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $tId, \PDO::PARAM_INT);
		$stmt->bindValue(2, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(3, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$sets = [];
		$dataMap = new YourStyleSetsDataMap();
		while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$sets[] = $dataMap->findById($item['sId']);
		}

		return $sets;

	}

}