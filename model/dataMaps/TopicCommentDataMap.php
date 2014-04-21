<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\mmc\MMC;
use popcorn\model\content\ImageFactory;
use popcorn\model\im\CommentTopic;

class TopicCommentDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_IMAGES = 2;
	const WITH_ALL = 3;

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

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();
		$this->class = "popcorn\\model\\im\\CommentTopic";
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_groups_topics_comments (topicId, date, owner, parent, content, editDate, ip, abuse, deleted, level, ratingUp, ratingDown, imagesCount) VALUES (:topicId, :date, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :ratingUp, :ratingDown, :imagesCount)");

		$this->updateStatement =
			$this->prepare("UPDATE pn_groups_topics_comments SET topicId=:topicId, date=:date, owner=:owner, parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, ratingUp=:ratingUp, ratingDown=:ratingDown, imagesCount=:imagesCount WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_groups_topics_comments WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_groups_topics_comments WHERE id=:id");
		$this->countStatement = $this->prepare("SELECT count(id) FROM pn_groups_topics_comments WHERE topicId = :topicId");
		$this->findChildsStatement = $this->prepare("SELECT * FROM pn_groups_topics_comments WHERE parent = :parent AND topicId = :topicId");
		$this->subscribeStatement =
			$this->prepare("INSERT INTO pn_groups_topics_comments_subscribe (topicId, userId) VALUES (:topicId, :userId)");
		$this->isSubscribedStatement =
			$this->prepare("SELECT * FROM pn_groups_topics_comments_subscribe WHERE topicId = :topicId AND userId = :userId");
		$this->subscribedStatement =
			$this->prepare("SELECT userId FROM pn_groups_topics_comments_subscribe WHERE topicId = :topicId");
		$this->unSubscribeStatement =
			$this->prepare("DELETE FROM pn_groups_topics_comments_subscribe WHERE topicId = :topicId AND userId = :userId");
		$this->abuseStatement =
			$this->prepare("INSERT INTO pn_groups_topics_comments_abuse (commentId, userId) VALUES (:commentId, :userId)");
		$this->rateStatement =
			$this->prepare("INSERT INTO pn_groups_topics_comments_vote (commentId, userId) VALUES (:commentId, :userId)");
	}

	/**
	 * @param CommentTopic $item
	 */
	protected function itemCallback($item, $modifier = self::WITH_ALL) {

		parent::itemCallback($item);

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_IMAGES) {
			$item->setImages($this->getAttachedImages($item->getId()));
		}

	}

	/**
	 * @param CommentTopic $item
	 *
	 * @return CommentTopic
	 */
	protected function prepareItem($item) {
		if (is_null($item->getId())) {
			$item->setDate(time());
		}
		if ($item->isChanged()) {
			$item->setEditDate(time());
		}

//		$item->setImages($this->getAttachedImages($item));
		$item->setImagesCount(count($item->getImages()));


		return parent::prepareItem($item);
	}

	/**
	 * @param CommentTopic $item
	 */
	protected function onSave($item) {
		$this->attachImages($item);
		$this->updateTopicCommentsCount($item);

		MMC::del(
			MMC::genKey($this->getClass(), $item->getId())
		);
	}


	/**
	 * Храним кол-во вложенных комментов отдельным полем, чтобы не юзать лишние затратные запросы
	 *
	 * @param CommentTopic $item
	 */
	private function updateTopicCommentsCount($item) {

		$subQuery1 = 'SELECT count(*) FROM pn_groups_topics_comments WHERE topicId = :topicId';
		$subQuery2 = 'SELECT `date` FROM pn_groups_topics_comments WHERE topicId = :topicId ORDER BY id DESC LIMIT 1';

		$stmt = $this->prepare("UPDATE pn_groups_topics SET commentsCount = ($subQuery1), lastCommentTime = ($subQuery2) WHERE id = :topicId");
		$stmt->execute([
			':topicId' => $item->getTopicId()
		]);

	}


	/**
	 * @param CommentTopic $item
	 */
	private function attachImages($item) {


		$stmt = $this->prepare('DELETE FROM pn_groups_topics_comments_images WHERE commentId = ?');
		$stmt->bindValue(1, $item->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->prepare('INSERT INTO pn_groups_topics_comments_images SET commentId = ?, imageId = ?');

		foreach ($item->getImages() as $image) {
			$stmt->bindValue(1, $item->getId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $image->getId(), \PDO::PARAM_INT);
			$stmt->execute();
		}

	}

	private function getAttachedImages($commentId) {

		$stmt = $this->prepare('SELECT imageId FROM pn_groups_topics_comments_images WHERE commentId = ?');
		$stmt->bindValue(1, $commentId, \PDO::PARAM_INT);
		$stmt->execute();

		$attachedImages = $stmt->fetchAll(\PDO::FETCH_COLUMN);

		$images = [];

		foreach ($attachedImages as $imageId) {
			$images[] = ImageFactory::getImage($imageId);
		}

		return $images;
	}

	public function count($topicId) {
		$this->countStatement->bindValue(':topicId', $topicId);
		$this->countStatement->execute();
		$count = $this->countStatement->fetchColumn(0);

		return $count;
	}

	public function getAllComments($topicId) {

		$stmt = $this->prepare('SELECT id FROM pn_groups_topics_comments WHERE topicId = ? ORDER BY date ASC');
		$stmt->bindValue(1, $topicId, \PDO::PARAM_INT);
		$stmt->execute();

		$comments = [];

//		$start = microtime(1);

		while ($commentId = $stmt->fetch(\PDO::FETCH_COLUMN)) {

			$cacheKey = MMC::genKey($this->class, $commentId);

//			MMC::del(md5("comment_kid_$commentId"));
			$comments[] = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($commentId) {
				return $this->findById($commentId);
			});

		}

//		echo microtime(1) - $start , '<hr/>';

		$tree = $this->makeTree($comments, 0);

		return $tree;

	}

	/**
	 * @param $topicId
	 * @return CommentTopic
	 */
	public function getLastComment($topicId) {

		$stmt = $this->prepare('SELECT id FROM pn_groups_topics_comments WHERE topicId = :topicId ORDER BY id DESC LIMIT 1');
		$stmt->execute([':topicId' => $topicId]);

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
	 * @param int $topicId
	 *
	 * @return CommentTopic[]
	 */
	public function findChilds($parentId, $topicId) {
		$this->findChildsStatement->bindValue(':parent', $parentId);
		$this->findChildsStatement->bindValue(':topicId', $topicId);
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
		$this->subscribeStatement->bindParam(':topicId', $roomId);
		$this->subscribeStatement->bindParam(':userId', $userId);
		$this->subscribeStatement->execute();
	}

	public function isSubscribed($roomId, $userId) {
		$this->isSubscribedStatement->bindValue(':topicId', $roomId);
		$this->isSubscribedStatement->bindValue(':userId', $userId);
		$this->isSubscribedStatement->execute();

		return $this->isSubscribedStatement->rowCount() > 0;
	}

	public function getSubscribed($roomId) {
		$this->subscribedStatement->bindParam(':topicId', $roomId);
		$this->subscribedStatement->execute();

		return $this->subscribedStatement->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	public function unSubscribe($roomId, $userId) {
		$this->unSubscribeStatement->bindParam(':topicId', $roomId);
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
	 * @param CommentTopic $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":topicId", $item->getTopicId());
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
	 * @param CommentTopic $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":topicId", $item->getTopicId());
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