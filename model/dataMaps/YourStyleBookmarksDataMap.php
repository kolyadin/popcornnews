<?php

namespace popcorn\model\dataMaps;

use popcorn\model\yourStyle\YourStyleBookmarks;
use popcorn\model\dataMaps\YourStyleTilesBrandsDataMap;
use popcorn\lib\yourstyle\YourStyleBackEnd;

class YourStyleBookmarksDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleBookmarks";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_bookmarks` (`uId`, `title`, `createTime`, `type`, `gId`, `searchText`, `tabColor`, `rGid`)
		    VALUES (:uId, :title, :createTime, :type, :gId, :searchText, :tabColor, :rGid)");
		$this->updateStatement = $this->prepare("UPDATE `pn_yourstyle_bookmarks`
			SET `uId`=:uId, `title`=:title, `createTime`=:createTime, `type`=:type, `gId`=:gId, `searchText`=:searchText, `tabColor`=:tabColor, `rGid`=:rGid WHERE `id`=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_bookmarks` WHERE `id`=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_bookmarks` WHERE `id`=:id");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":title", $item->getTitle());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":type", $item->getType());
		$this->insertStatement->bindValue(":gId", $item->getGId());
		$this->insertStatement->bindValue(":searchText", $item->getSearchText());
		$this->insertStatement->bindValue(":tabColor", $item->getTabColor());
		$this->insertStatement->bindValue(":rGid", $item->getRGid());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":title", $item->getTitle());
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
		$this->updateStatement->bindValue(":type", $item->getType());
		$this->updateStatement->bindValue(":gId", $item->getGId());
		$this->updateStatement->bindValue(":searchText", $item->getSearchText());
		$this->updateStatement->bindValue(":tabColor", $item->getTabColor());
		$this->updateStatement->bindValue(":rGid", $item->getRGid());
	}

	public function getBookmarksByUId($uId) {

		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_bookmarks`
			WHERE `uId` = ?
			ORDER BY `id`
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $uId, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;
	}

	/**
	 * @param YourStyleBookmarksDataMap $item
	 */
	protected function itemCallback($item) {

		parent::itemCallback($item);

		if(!empty($item->getTabColor())) {
			$color = YourStyleBackEnd::$humanColors[$item->getTabColor()];
			$item->setTabColor(
				array(
					'val' => $item->getTabColor(),
					'en' => $color['en'],
					'ru' => $color['ru']
				)
			);
		}

	    if(!empty($item->getSearchText())) {
			$bId = $item->getSearchText();

			$dataMap = new YourStyleTilesBrandsDataMap();
			$brand = $dataMap->findById($bId);

			if ($brand) {
				$item->setSearchText(array('brand' => $brand->getTitle(), 'id' => $bId));
			}
	    }

	}

}