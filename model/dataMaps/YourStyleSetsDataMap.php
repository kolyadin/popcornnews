<?php

namespace popcorn\model\dataMaps;

use popcorn\model\system\users\User;
use popcorn\model\yourStyle\YourStyleSets;
use popcorn\lib\yourstyle\YourStyleFactory;

class YourStyleSetsDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\yourStyle\\YourStyleSets";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_yourstyle_sets` (`title`, `createTime`, `image`, `editTime`, `isDraft`, `uId`, `rating`)
		    VALUES (:title, :createTime, :image, :editTime, :isDraft, :uId, :rating)");
		$this->updateStatement = $this->prepare("UPDATE `pn_yourstyle_sets`
			SET `title`=:title, `createTime`=:createTime, `image`=:image, `editTime`=:editTime, `isDraft`=:isDraft, `uId`=:uId, `rating`=:rating WHERE `id`=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_yourstyle_sets` WHERE `id`=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_yourstyle_sets` WHERE `id`=:id");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":title", $item->getTitle());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":image", $item->getImage());
		$this->insertStatement->bindValue(":editTime", $item->getEditTime());
		$this->insertStatement->bindValue(":isDraft", $item->getIsDraft());
		$this->insertStatement->bindValue(":uId", $item->getUId());
		$this->insertStatement->bindValue(":rating", $item->getRating());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":title", $item->getTitle());
		$this->updateStatement->bindValue(":createTime", $item->getCreateTime());
		$this->updateStatement->bindValue(":image", $item->getImage());
		$this->updateStatement->bindValue(":editTime", $item->getEditTime());
		$this->updateStatement->bindValue(":isDraft", $item->getIsDraft());
		$this->updateStatement->bindValue(":uId", $item->getUId());
		$this->updateStatement->bindValue(":rating", $item->getRating());
	}

	public function getTopSets() {
		$sql = <<<SQL
			SELECT a.*, b.id uid, b.nick unick, COUNT(DISTINCT c.id) comments, COUNT(DISTINCT v.uid) as votes,
			IF(COUNT(DISTINCT v.uid)>0,(a.rating/COUNT(DISTINCT v.uid)),0) as rate
			FROM pn_yourstyle_sets a
				LEFT JOIN pn_users b ON (a.uid = b.id)
				LEFT JOIN pn_yourstyle_sets_comments c ON (a.id = c.sid)
				LEFT JOIN pn_yourstyle_sets_votes as v ON (v.sid = a.id)
			WHERE `isDraft` = 'n'
			group by a.id
			ORDER BY rate DESC
			LIMIT 0, 20
SQL;

		$stmt = $this->prepare($sql);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		return $items;
	}

	public function getUserSets(User $user) {
		$sql = <<<SQL
			SELECT *
			FROM `pn_yourstyle_sets`
			WHERE `uId` = ?
			ORDER BY `id` DESC
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		foreach($items as &$item) {
            $this->itemCallback($item);
        }

		return $items;
	}

	protected function itemCallback($item) {

		$item->setImage(YourStyleFactory::getWwwUploadSetPath($item->getId(), $item->getImage()));

		parent::itemCallback($item);

	}

}