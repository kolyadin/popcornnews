<?php

namespace popcorn\model\dataMaps\comments;

use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\comments\Comment;

class CommentDataMap extends DataMap {

	protected $tablePrefix = '';

	//region Statements

	/**
	 * @var \PDOStatement
	 */
	protected $unSubscribeStatement, $subscribedStatement, $isSubscribedStatement,
		$subscribeStatement, $findChildsStatement, $countStatement, $abuseStatement, $rateStatement;

	/**
	 * @var \PDOStatement
	 */
	protected $stmtDeleteAttachedImages, $stmtAttachImages, $stmtFindAttachedImages;

	/**
	 * @var \PDOStatement
	 */
	protected $stmtGetAllComments, $stmtGetLastComment;

	//endregion

	public function __construct() {

		parent::__construct();

		$this->class = "popcorn\\model\\im\\Comment";

		$this->initStatements();

	}

	protected function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_comments_{$this->tablePrefix} (entityId, createdAt, owner, parent, content, editDate, ip, abuse, deleted, level, votesUp, votesDown) VALUES (:entityId, :createdAt, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :votesUp, :votesDown)");

		$this->updateStatement =
			$this->prepare("UPDATE pn_comments_{$this->tablePrefix} SET entityId=:entityId, createdAt=:createdAt, owner=:owner, parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, votesUp=:votesUp, votesDown=:votesDown WHERE id=:id");

		$this->deleteStatement = $this->prepare("DELETE FROM pn_comments_{$this->tablePrefix} WHERE id=:id");

		$this->findOneStatement = $this->prepare("SELECT * FROM pn_comments_{$this->tablePrefix} WHERE id=:id");

		$this->countStatement = $this->prepare("SELECT count(id) FROM pn_comments_{$this->tablePrefix} WHERE entityId = :entityId");

		$this->findChildsStatement = $this->prepare("SELECT * FROM pn_comments_{$this->tablePrefix} WHERE parent = :parent AND entityId = :entityId");

		$this->subscribeStatement =
			$this->prepare("INSERT INTO pn_comments_{$this->tablePrefix}_subscribe (entityId, userId) VALUES (:entityId, :userId)");

		$this->isSubscribedStatement =
			$this->prepare("SELECT * FROM pn_comments_{$this->tablePrefix}_subscribe WHERE entityId = :entityId AND userId = :userId");

		$this->subscribedStatement =
			$this->prepare("SELECT userId FROM pn_comments_{$this->tablePrefix}_subscribe WHERE entityId = :entityId");

		$this->unSubscribeStatement =
			$this->prepare("DELETE FROM pn_comments_{$this->tablePrefix}_subscribe WHERE entityId = :entityId AND userId = :userId");

		$this->abuseStatement =
			$this->prepare("INSERT INTO pn_comments_{$this->tablePrefix}_abuse (commentId, userId) VALUES (:commentId, :userId)");

		$this->rateStatement =
			$this->prepare("INSERT INTO pn_comments_{$this->tablePrefix}_vote (commentId, userId) VALUES (:commentId, :userId)");

		$this->stmtGetAllComments =
			$this->prepare("SELECT id FROM pn_comments_{$this->tablePrefix} WHERE entityId = :entityId ORDER BY createdAt ASC");

		$this->stmtGetLastComment =
			$this->prepare("SELECT id FROM pn_comments_{$this->tablePrefix} WHERE entityId = :entityId ORDER BY id DESC LIMIT 1");

		$this->stmtDeleteAttachedImages =
			$this->prepare("DELETE FROM pn_comments_{$this->tablePrefix}_images WHERE commentId = :commentId LIMIT 1");

		$this->stmtAttachImages =
			$this->prepare("INSERT INTO pn_comments_{$this->tablePrefix}_images SET commentId = :commentId, imageId = :imageId");

		$this->stmtFindAttachedImages =
			$this->prepare("SELECT imageId FROM pn_comments_{$this->tablePrefix}_images WHERE commentId = :commentId");
	}

	/**
	 * @param Comment $item
	 */
	protected function itemCallback($item) {

		parent::itemCallback($item);

		$item->setImages($this->getAttachedImages($item->getId()));

	}

	/**
	 * Обновляем счетчик комментариев в новостях, при создании нового коммента
	 * @param Comment $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
	}

	/**
	 * @param Comment $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
	}


	/**
	 * @param Comment $item
	 */
	protected function attachImages($item) {

		$this->stmtDeleteAttachedImages->execute([
			':commentId' => $item->getId()
		]);

		foreach ($item->getImages() as $image) {
			$this->stmtAttachImages->execute([
				':commentId' => $item->getId(),
				':imageId'   => $image->getId()
			]);
		}

	}

	/**
	 * @param $commentId
	 * @return \popcorn\model\content\Image[]
	 */
	protected function getAttachedImages($commentId) {

		$this->stmtFindAttachedImages->execute([
			':commentId' => $commentId
		]);

		$images = [];

		while ($imageId = $this->stmtFindAttachedImages->fetch(\PDO::FETCH_COLUMN)) {
			$images[] = ImageFactory::getImage($imageId);
		}

		return $images;
	}


	/**
	 * @param Comment $item
	 *
	 * @return Comment
	 */
	protected function prepareItem($item) {
		if (is_null($item->getId())) {
			$item->setCreatedAt(time());
		}
		if ($item->isChanged()) {
			$item->setEditDate(time());
		}

		return parent::prepareItem($item);
	}

	public function count($postId) {
		$this->countStatement->bindValue(':entityId', $postId);
		$this->countStatement->execute();
		$count = $this->countStatement->fetchColumn(0);

		return $count;
	}

	public function getAllComments($entityId) {
		$this->stmtGetAllComments->execute([
			':entityId' => $entityId
		]);

		if (!$this->stmtGetAllComments->rowCount()) {
			return null;
		}

		$comments = [];

		while ($commentId = $this->stmtGetAllComments->fetch(\PDO::FETCH_COLUMN)) {
			$comments[] = $this->findById($commentId);
		}

		$tree = $this->makeTree($comments, 0);

		return $tree;

	}

	public function getLastComment($entityId) {
		$this->stmtGetLastComment->execute([
			':entityId' => $entityId
		]);

		return $this->findById($this->stmtGetLastComment->fetchColumn());
	}

	protected function makeTree(array &$comments, $parentId = 0) {

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
	 * @param int $postId
	 *
	 * @return Comment[]
	 */
	public function findChilds($parentId, $postId) {
		$this->findChildsStatement->bindValue(':parent', $parentId);
		$this->findChildsStatement->bindValue(':entityId', $postId);
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
		$this->subscribeStatement->bindParam(':entityId', $roomId);
		$this->subscribeStatement->bindParam(':userId', $userId);
		$this->subscribeStatement->execute();
	}

	public function isSubscribed($roomId, $userId) {
		$this->isSubscribedStatement->bindValue(':entityId', $roomId);
		$this->isSubscribedStatement->bindValue(':userId', $userId);
		$this->isSubscribedStatement->execute();

		return $this->isSubscribedStatement->rowCount() > 0;
	}

	public function getSubscribed($roomId) {
		$this->subscribedStatement->bindParam(':entityId', $roomId);
		$this->subscribedStatement->execute();

		return $this->subscribedStatement->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	public function unSubscribe($roomId, $userId) {
		$this->unSubscribeStatement->bindParam(':entityId', $roomId);
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
	 * @param \popcorn\model\comments\Comment $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":entityId", $item->getEntityId());
		$this->insertStatement->bindValue(":createdAt", $item->getCreatedAt());
		$this->insertStatement->bindValue(":owner", $item->getOwner()->getId());
		$this->insertStatement->bindValue(":parent", $item->getParent());
		$this->insertStatement->bindValue(":content", $item->getContent());
		$this->insertStatement->bindValue(":editDate", $item->getEditDate());
		$this->insertStatement->bindValue(":ip", $item->getIp());
		$this->insertStatement->bindValue(":abuse", $item->getAbuse());
		$this->insertStatement->bindValue(":deleted", $item->getDeleted());
		$this->insertStatement->bindValue(":level", $item->getLevel());
		$this->insertStatement->bindValue(":votesUp", $item->getVotesUp());
		$this->insertStatement->bindValue(":votesDown", $item->getVotesDown());
	}

	/**
	 * @param \popcorn\model\comments\Comment $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":entityId", $item->getEntityId());
		$this->updateStatement->bindValue(":createdAt", $item->getCreatedAt());
		$this->updateStatement->bindValue(":owner", $item->getOwner()->getId());
		$this->updateStatement->bindValue(":parent", $item->getParent());
		$this->updateStatement->bindValue(":content", $item->getContent());
		$this->updateStatement->bindValue(":editDate", $item->getEditDate());
		$this->updateStatement->bindValue(":ip", $item->getIp());
		$this->updateStatement->bindValue(":abuse", $item->getAbuse());
		$this->updateStatement->bindValue(":deleted", $item->getDeleted());
		$this->updateStatement->bindValue(":level", $item->getLevel());
		$this->updateStatement->bindValue(":votesUp", $item->getVotesUp());
		$this->updateStatement->bindValue(":votesDown", $item->getVotesDown());
		$this->updateStatement->bindValue(":id", $item->getId());
	}


}