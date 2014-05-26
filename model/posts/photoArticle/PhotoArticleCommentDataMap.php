<?php

namespace popcorn\model\posts\photoArticle;

use popcorn\lib\mmc\MMC;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\im\CommentPhotoArticle;

class PhotoArticleCommentDataMap extends DataMap {

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
		$this->class = "popcorn\\model\\im\\CommentPhotoArticle";
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_comments_photoarticles (postId, date, owner, parent, content, editDate, ip, abuse, deleted, level, ratingUp, ratingDown, imagesCount) VALUES (:postId, :date, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :ratingUp, :ratingDown, :imagesCount)");

		$this->updateStatement =
			$this->prepare("UPDATE pn_comments_photoarticles SET postId=:postId, date=:date, owner=:owner, parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, ratingUp=:ratingUp, ratingDown=:ratingDown, imagesCount=:imagesCount WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_comments_photoarticles WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_comments_photoarticles WHERE id=:id");
		$this->countStatement = $this->prepare("SELECT count(id) FROM pn_comments_photoarticles WHERE postId = :postId");
		$this->findChildsStatement = $this->prepare("SELECT * FROM pn_comments_photoarticles WHERE parent = :parent AND postId = :postId");
		$this->subscribeStatement =
			$this->prepare("INSERT INTO pn_comments_photoarticles_subscribe (postId, userId) VALUES (:postId, :userId)");
		$this->isSubscribedStatement =
			$this->prepare("SELECT * FROM pn_comments_photoarticles_subscribe WHERE postId = :postId AND userId = :userId");
		$this->subscribedStatement =
			$this->prepare("SELECT userId FROM pn_comments_photoarticles_subscribe WHERE postId = :postId");
		$this->unSubscribeStatement =
			$this->prepare("DELETE FROM pn_comments_photoarticles_subscribe WHERE postId = :postId AND userId = :userId");
		$this->abuseStatement =
			$this->prepare("INSERT INTO pn_comments_photoarticles_abuse (commentId, userId) VALUES (:commentId, :userId)");
		$this->rateStatement =
			$this->prepare("INSERT INTO pn_comments_photoarticles_vote (commentId, userId) VALUES (:commentId, :userId)");
	}

	/**
	 * @param CommentPhotoArticle $item
	 */
	protected function itemCallback($item) {

		parent::itemCallback($item);

		$item->setImages($this->getAttachedImages($item->getId()));

	}

	/**
	 * @param CommentPhotoArticle $item
	 *
	 * @return CommentPhotoArticle
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
	 * @param CommentPhotoArticle $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
		$this->updateKidCommentsCount($item);
		$item->setImagesCount(count($item->getImages()));
	}

	/**
	 * @param CommentPhotoArticle $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
//		$this->updateKidCommentsCount($item);
		$item->setImagesCount(count($item->getImages()));

		//Если коммент обновился (в том числе и флаг удаленного коммента), сбрасываем кэш
		MMC::del(
			MMC::genKey($this->getClass(), $item->getId())
		);

	}


	/**
	 * @param CommentPhotoArticle $item
	 */
	private function updateKidCommentsCount($item) {

		$stmt = $this->prepare('UPDATE pn_kids SET commentsCount = commentsCount+1 WHERE id = ?');
		$stmt->bindValue(1, $item->getpostId(), \PDO::PARAM_INT);
		$stmt->execute();

	}

	/**
	 * @param CommentPhotoArticle $item
	 */
	private function attachImages($item) {


		$stmt = $this->prepare('DELETE FROM pn_comments_photoarticles_images WHERE commentId = ?');
		$stmt->bindValue(1, $item->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->prepare('INSERT INTO pn_comments_photoarticles_images SET commentId = ?, imageId = ?');

		foreach ($item->getImages() as $image) {
			$stmt->bindValue(1, $item->getId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $image->getId(), \PDO::PARAM_INT);
			$stmt->execute();
		}

	}

	private function getAttachedImages($commentId) {

		$stmt = $this->prepare('SELECT imageId FROM pn_comments_photoarticles_images WHERE commentId = ?');
		$stmt->bindValue(1, $commentId, \PDO::PARAM_INT);
		$stmt->execute();

		$attachedImages = $stmt->fetchAll(\PDO::FETCH_COLUMN);

		$images = [];

		foreach ($attachedImages as $imageId) {
			$images[] = ImageFactory::getImage($imageId);
		}

		return $images;
	}

	public function count($postId) {
		$this->countStatement->bindValue(':postId', $postId);
		$this->countStatement->execute();
		$count = $this->countStatement->fetchColumn(0);

		return $count;
	}

	public function getAllComments(PhotoArticlePost $post) {

		$stmt = $this->prepare('SELECT id FROM pn_comments_photoarticles WHERE postId = :postId ORDER BY date ASC');
		$stmt->execute([
			':postId' => $post->getId()
		]);

		$comments = [];

//		$start = microtime(1);

		while ($commentId = $stmt->fetch(\PDO::FETCH_COLUMN)) {

			$comments[] = $this->findById($commentId);

//			$cacheKey = MMC::genKey($this->class, $commentId);

//			MMC::del(md5("comment_kid_$commentId"));
//			$comments[] = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($commentId) {
//				return $this->findById($commentId);
//			});

		}

//		echo microtime(1) - $start , '<hr/>';

		$tree = $this->makeTree($comments, 0);

		return $tree;

	}

	/**
	 * @param $postId
	 * @return CommentPhotoArticle
	 */
	public function getLastComment($postId) {

		$stmt = $this->prepare('SELECT id FROM pn_comments_photoarticles WHERE postId = :postId ORDER BY id DESC LIMIT 1');
		$stmt->execute([
			':postId' => $postId
		]);

		return $this->findById($stmt->fetchColumn());

	}

	/**
	 * @param CommentPhotoArticle[] $comments
	 * @param int $parentId
	 * @return array
	 */
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
	 * @param int $postId
	 *
	 * @return CommentPhotoArticle[]
	 */
	public function findChilds($parentId, $postId) {
		$this->findChildsStatement->bindValue(':parent', $parentId);
		$this->findChildsStatement->bindValue(':postId', $postId);
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
		$this->subscribeStatement->bindParam(':postId', $roomId);
		$this->subscribeStatement->bindParam(':userId', $userId);
		$this->subscribeStatement->execute();
	}

	public function isSubscribed($roomId, $userId) {
		$this->isSubscribedStatement->bindValue(':postId', $roomId);
		$this->isSubscribedStatement->bindValue(':userId', $userId);
		$this->isSubscribedStatement->execute();

		return $this->isSubscribedStatement->rowCount() > 0;
	}

	public function getSubscribed($roomId) {
		$this->subscribedStatement->bindParam(':postId', $roomId);
		$this->subscribedStatement->execute();

		return $this->subscribedStatement->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	public function unSubscribe($roomId, $userId) {
		$this->unSubscribeStatement->bindParam(':postId', $roomId);
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
	 * @param CommentPhotoArticle $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":postId", $item->getPostId());
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
	 * @param CommentPhotoArticle $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":postId", $item->getPostId());
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