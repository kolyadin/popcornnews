<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\mmc\MMC;
use popcorn\model\content\ImageFactory;
use popcorn\model\im\CommentMeeting;
use popcorn\model\persons\MeetingFactory;

class MeetingsCommentDataMap extends DataMap {

	//region Statements

	/**
	 * @var \PDOStatement
	 */
	private $unSubscribeStatement;
	/**
	 * @var \PDOStatement
	 */
	private $subscribedStatement;
	/**
	 * @var \PDOStatement
	 */
	private $isSubscribedStatement;
	/**
	 * @var \PDOStatement
	 */
	private $subscribeStatement;
	/**
	 * @var \PDOStatement
	 */
	private $findChildsStatement;
	/**
	 * @var \PDOStatement
	 */
	private $countStatement;
	/**
	 * @var \PDOStatement
	 */
	private $abuseStatement;
	/**
	 * @var \PDOStatement
	 */
	private $rateStatement;

	//endregion

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\im\\CommentMeeting";
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_comments_meetings (meetId, date, owner, parent, content, editDate, ip, abuse, deleted, level, ratingUp, ratingDown, imagesCount) VALUES (:meetId, :date, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :ratingUp, :ratingDown, :imagesCount)");

		$this->updateStatement =
			$this->prepare("UPDATE pn_comments_meetings SET meetId=:meetId, date=:date, owner=:owner, parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, ratingUp=:ratingUp, ratingDown=:ratingDown, imagesCount=:imagesCount WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_comments_meetings WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_comments_meetings WHERE id=:id");
		$this->countStatement = $this->prepare("SELECT count(id) FROM pn_comments_meetings WHERE meetId = :meetId");
		$this->findChildsStatement = $this->prepare("SELECT * FROM pn_comments_meetings WHERE parent = :parent AND meetId = :meetId");
		$this->subscribeStatement =
			$this->prepare("INSERT INTO pn_comments_meetings_subscribe (meetId, userId) VALUES (:meetId, :userId)");
		$this->isSubscribedStatement =
			$this->prepare("SELECT * FROM pn_comments_meetings_subscribe WHERE meetId = :meetId AND userId = :userId");
		$this->subscribedStatement =
			$this->prepare("SELECT userId FROM pn_comments_meetings_subscribe WHERE meetId = :meetId");
		$this->unSubscribeStatement =
			$this->prepare("DELETE FROM pn_comments_meetings_subscribe WHERE meetId = :meetId AND userId = :userId");
		$this->abuseStatement =
			$this->prepare("INSERT INTO pn_comments_meetings_abuse (commentId, userId) VALUES (:commentId, :userId)");
		$this->rateStatement =
			$this->prepare("INSERT INTO pn_comments_meetings_vote (commentId, userId) VALUES (:commentId, :userId)");
	}

	/**
	 * @param CommentMeeting $item
	 */
	protected function itemCallback($item) {

		parent::itemCallback($item);

		$item->setImages($this->getAttachedImages($item->getId()));

	}

	/**
	 * @param CommentMeeting $item
	 *
	 * @return CommentMeeting
	 */
	protected function prepareItem($item) {
		if (is_null($item->getId())) {
			$item->setDate(time());
		}
		if ($item->isChanged()) {
			$item->setEditDate(time());
		}

		$item->setImagesCount(count($item->getImages()));

		return parent::prepareItem($item);
	}

	/**
	 * @param CommentMeeting $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
		$this->updateMeetingCommentsCount($item);
		$item->setImagesCount(count($item->getImages()));
	}

	/**
	 * @param CommentMeet $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
		$item->setImagesCount(count($item->getImages()));

		//Если коммент обновился (в том числе и флаг удаленного коммента), сбрасываем кэш
		MMC::del(
			MMC::genKey($this->getClass(), $item->getId())
		);

	}


	/**
	 * @param CommentMeeting $item
	 */
	private function updateMeetingCommentsCount($item) {

		$stmt = $this->prepare('UPDATE pn_meetings SET commentsCount = commentsCount+1 WHERE id = ?');
		$stmt->bindValue(1, $item->getMeetId(), \PDO::PARAM_INT);
		$stmt->execute();

	}

	/**
	 * @param CommentMeeting $item
	 */
	private function attachImages($item) {


		$stmt = $this->prepare('DELETE FROM pn_comments_meetings_images WHERE commentId = ?');
		$stmt->bindValue(1, $item->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->prepare('INSERT INTO pn_comments_meetings_images SET commentId = ?, imageId = ?');

		foreach ($item->getImages() as $image) {
			$stmt->bindValue(1, $item->getId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $image->getId(), \PDO::PARAM_INT);
			$stmt->execute();
		}

	}

	private function getAttachedImages($commentId) {

		$stmt = $this->prepare('SELECT imageId FROM pn_comments_meetings_images WHERE commentId = ?');
		$stmt->bindValue(1, $commentId, \PDO::PARAM_INT);
		$stmt->execute();

		$attachedImages = $stmt->fetchAll(\PDO::FETCH_COLUMN);

		$images = [];

		foreach ($attachedImages as $imageId) {
			$images[] = ImageFactory::getImage($imageId);
		}

		return $images;
	}

	public function count($meetId) {
		$this->countStatement->bindValue(':meetId', $meetId);
		$this->countStatement->execute();
		$count = $this->countStatement->fetchColumn(0);

		return $count;
	}

	public function getAllComments($meetId) {

		$stmt = $this->prepare('SELECT id FROM pn_comments_meetings WHERE meetId = ? ORDER BY date ASC');
		$stmt->bindValue(1, $meetId, \PDO::PARAM_INT);
		$stmt->execute();

		$comments = [];

		while ($commentId = $stmt->fetch(\PDO::FETCH_COLUMN)) {

			$cacheKey = MMC::genKey($this->class, $commentId);

			$comments[] = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($commentId) {
				return $this->findById($commentId);
			});

		}

		$tree = $this->makeTree($comments, 0);

		return $tree;

	}

	/**
	 * @param $meetId
	 * @return CommentMeeting
	 */
	public function getLastComment($meetId) {

		$stmt = $this->prepare('SELECT id FROM pn_comments_meetings WHERE meetId = ? ORDER BY id DESC LIMIT 1');
		$stmt->bindValue(1, $meetId, \PDO::PARAM_INT);
		$stmt->execute();

		return $this->findById($stmt->fetchColumn());

	}

	private function makeTree(array &$comments, $parentId = 0) {

		$branch = array();

		foreach ($comments as $element) {
			if ($element->getParent() == $parentId) {
				$children = $this->makeTree($comments, $element->getId());
				if ($children) {
					$element->setChilds($children);
				}
				$branch[$element->getId()] = $element;
			}
		}

		return $branch;

	}

	/**
	 * @param int $parentId
	 * @param int $meetId
	 *
	 * @return CommentMeet[]
	 */
	public function findChilds($parentId, $meetId) {
		$this->findChildsStatement->bindValue(':parent', $parentId);
		$this->findChildsStatement->bindValue(':meetId', $meetId);
		$this->findChildsStatement->execute();
		$items = $this->findChildsStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);
		foreach ($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;
	}

	/**
	 * @param $roomId
	 * @param $userId
	 */
	public function subscribe($roomId, $userId) {
		$this->subscribeStatement->bindParam(':meetId', $roomId);
		$this->subscribeStatement->bindParam(':userId', $userId);
		$this->subscribeStatement->execute();
	}

	public function isSubscribed($roomId, $userId) {
		$this->isSubscribedStatement->bindValue(':meetId', $roomId);
		$this->isSubscribedStatement->bindValue(':userId', $userId);
		$this->isSubscribedStatement->execute();

		return $this->isSubscribedStatement->rowCount() > 0;
	}

	public function getSubscribed($roomId) {
		$this->subscribedStatement->bindParam(':meetId', $roomId);
		$this->subscribedStatement->execute();

		return $this->subscribedStatement->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	public function unSubscribe($roomId, $userId) {
		$this->unSubscribeStatement->bindParam(':meetId', $roomId);
		$this->unSubscribeStatement->bindParam(':userId', $userId);
		$this->unSubscribeStatement->execute();

		return $this->unSubscribeStatement->rowCount() > 0;
	}

	public function abuse($msgId, $userId) {
		$result = true;
		$this->abuseStatement->bindParam(':commentId', $msgId);
		$this->abuseStatement->bindParam(':userId', $userId);
		try {
			$this->abuseStatement->execute();
		} catch (\PDOException $e) {
			$result = false;
		}

		return $result;
	}

	public function rate($msgId, $userId) {
		$result = true;
		$this->rateStatement->bindParam(':commentId', $msgId);
		$this->rateStatement->bindParam(':userId', $userId);
		try {
			$this->rateStatement->execute();
		} catch (\PDOException $e) {
			$result = false;
		}

		return $result;
	}

	/**
	 * @param CommentMeet $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":meetId", $item->getMeetId());
		$this->insertStatement->bindValue(":date", $item->getDate());
		$this->insertStatement->bindValue(":owner", $item->getOwner()->getId());
		$this->insertStatement->bindValue(":parent", $item->getParent());
		$this->insertStatement->bindValue(":content", $item->getContent());
		$this->insertStatement->bindValue(":editDate", $item->getEditDate());
		$this->insertStatement->bindValue(":ip", $item->getIp());
		$this->insertStatement->bindValue(":abuse", $item->getAbuse());
		$this->insertStatement->bindValue(":deleted", $item->getDeleted());
		$this->insertStatement->bindValue(":level", $item->getLevel());
		$this->insertStatement->bindValue(":ratingUp", $item->getRatingUp());
		$this->insertStatement->bindValue(":ratingDown", $item->getRatingDown());
		$this->insertStatement->bindValue(":imagesCount", $item->getImagesCount());
	}

	/**
	 * @param CommentMeet $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":meetId", $item->getMeetId());
		$this->updateStatement->bindValue(":date", $item->getDate());
		$this->updateStatement->bindValue(":owner", $item->getOwner()->getId());
		$this->updateStatement->bindValue(":parent", $item->getParent());
		$this->updateStatement->bindValue(":content", $item->getContent());
		$this->updateStatement->bindValue(":editDate", $item->getEditDate());
		$this->updateStatement->bindValue(":ip", $item->getIp());
		$this->updateStatement->bindValue(":abuse", $item->getAbuse());
		$this->updateStatement->bindValue(":deleted", $item->getDeleted());
		$this->updateStatement->bindValue(":level", $item->getLevel());
		$this->updateStatement->bindValue(":ratingUp", $item->getRatingUp());
		$this->updateStatement->bindValue(":ratingDown", $item->getRatingDown());
		$this->updateStatement->bindValue(":imagesCount", $item->getImagesCount());

		$this->updateStatement->bindValue(":id", $item->getId());
	}


}