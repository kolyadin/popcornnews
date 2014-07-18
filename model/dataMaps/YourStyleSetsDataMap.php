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

	public function getTopSets($offset = 0, $limit = 20, $imgMode = 0) {
		$sql = <<<SQL
			SELECT a.*, b.id uid, b.nick unick, COUNT(DISTINCT c.id) comments, COUNT(DISTINCT v.uid) as votes,
			IF(COUNT(DISTINCT v.uid)>0,(a.rating/COUNT(DISTINCT v.uid)),0) as rate
			FROM pn_yourstyle_sets a
				LEFT JOIN pn_users b ON (a.uid = b.id)
				LEFT JOIN pn_yourstyle_sets_comments c ON (a.id = c.sid)
				LEFT JOIN pn_yourstyle_sets_votes as v ON (v.sid = a.id)
			WHERE `isDraft` = 'n'
			GROUP BY a.id
			ORDER BY rate DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(2, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		if ($imgMode) {
			$params['size'] = '274x274';
			foreach($items as $key => &$item) {
				if ($imgMode == 1 && $key) {
					$params['size'] = '110x110';
				}
				$this->itemCallback($item, $params);
			}
        }

		return $items;
	}

	public function getPersonsSets($offset = 0, $limit = 20, $imgMode = 0, $noParent = 0) {
		$sql = <<<SQL
			SELECT a.id, a.name, a.urlName as eng_name
			FROM pn_persons a
				INNER JOIN pn_yourstyle_sets_tags b ON (b.tid = a.id)
				INNER JOIN pn_yourstyle_sets as s ON (s.id = b.sid)
			WHERE b.tid IS NOT NULL
				AND s.isDraft = 'n'
			GROUP BY a.id
			ORDER BY s.createtime DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(2, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		if ($noParent) {
			$params['noParent'] = 1;
		}
		if ($imgMode) {
			$params['size'] = '274x274';
			foreach($items as $key => &$item) {
				if ($imgMode == 1 && $key) {
					$params['size'] = '110x110';
				}
				$this->itemCallback($item, $params);
			}
        }

		return $items;
	}

	public function getCountPersonsSets() {

		$sql = <<<SQL
			SELECT COUNT(a.id)
			FROM pn_persons a
				INNER JOIN pn_yourstyle_sets_tags b ON (b.tid = a.id)
				INNER JOIN pn_yourstyle_sets as s ON (s.id = b.sid)
			WHERE b.tid IS NOT NULL
				AND s.isDraft = 'n'
SQL;

		$stmt = $this->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();

        return $count;

    }

	public function getSetsByPersons($pId, $offset = 0, $limit = 20, $imgMode = 0) {
		$sql = <<<SQL
			SELECT s.*, COUNT(DISTINCT v.uid) as votes, COUNT(c.id) as comments, u.nick as unick, u.id as uid
			FROM pn_yourstyle_sets as s
				INNER JOIN pn_yourstyle_sets_tags as t ON (s.id = t.sid)
				LEFT JOIN pn_yourstyle_sets_comments as c ON (c.sid = s.id)
				LEFT JOIN pn_yourstyle_sets_votes as v ON (v.sid = s.id)
				LEFT JOIN `pn_users` as u ON (s.uid = u.id)
			WHERE t.tid = ?
				AND s.isDraft = 'n'
			GROUP BY s.id
			ORDER BY s.createtime DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $pId, \PDO::PARAM_INT);
		$stmt->bindValue(2, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(3, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		if ($imgMode) {
			$params['noParent'] = 1;
			$params['size'] = '274x274';
			foreach($items as $key => &$item) {
				if ($imgMode == 3 || ($imgMode == 1 && $key)) {
					$params['size'] = '110x110';
				}
				$this->itemCallback($item, $params);
			}
        }

		return $items;
	}

	public function getCountSetsByPersons($pId) {

		$sql = <<<SQL
			SELECT COUNT(DISTINCT sid) as `count`
			FROM pn_yourstyle_sets_tags
			WHERE tid = ?
			GROUP BY tid
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $pId, \PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();

        return $count;

    }

	public function getUserWithRating($uId) {

		$sql = <<<SQL
			SELECT IFNULL(ROUND(SUM(r.points/r.votes)/COUNT(r.id), 1), 0) as rating
			FROM (
				SELECT s.id, s.uid, s.rating as points, count(DISTINCT v.uid) as votes
				FROM `pn_yourstyle_sets` as s
					LEFT JOIN pn_yourstyle_sets_votes as v ON (v.sid = s.id)
				WHERE s.uid = ? and s.isDraft = 'n'
				GROUP BY s.id
				HAVING votes >= 0
			) as r
				INNER JOIN pn_users as u ON (r.uid = u.id)
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $uId, \PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn(0);
        $stmt->closeCursor();

        return $count;

    }

	public function getNewSets($offset = 0, $limit = 20, $imgMode = 0) {
		$sql = <<<SQL
			SELECT a.*, b.id uid, b.nick unick, COUNT(DISTINCT c.id) comments, COUNT(DISTINCT v.uid) as votes,
			IF(COUNT(DISTINCT v.uid)>0,(a.rating/COUNT(DISTINCT v.uid)),0) as rate
			FROM pn_yourstyle_sets a
				LEFT JOIN pn_users b ON (a.uid = b.id)
				LEFT JOIN pn_yourstyle_sets_comments c ON (a.id = c.sid)
				LEFT JOIN pn_yourstyle_sets_votes as v ON (v.sid = a.id)
			WHERE `isDraft` = 'n'
			GROUP BY a.id
			ORDER BY createtime DESC
			LIMIT ?, ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(2, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if($items === false) return null;

		if ($imgMode) {
			$params['size'] = '274x274';
			foreach($items as $key => &$item) {
				if ($imgMode == 1 && $key) {
					$params['size'] = '110x110';
				}
				$this->itemCallback($item, $params);
			}
        }

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

	protected function itemCallback($item, $params = array()) {

		if (!empty($params['size'])) {
			$size = $params['size'];
		} else {
			$size = null;
		}
		$item->setImage(YourStyleFactory::getWwwUploadSetPath($item->getId(), $item->getImage(), $size));

		if (empty($params['noParent'])) {
			parent::itemCallback($item);
		}

	}

}