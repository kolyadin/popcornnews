<?php

namespace popcorn\model\dataMaps\comments;

use popcorn\lib\MailHelper;
use popcorn\lib\mmc\MMC;
use popcorn\model\comments\CommentFactory;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\comments\Comment;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\persons\KidFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

class CommentDataMap extends DataMap {

	protected $tablePrefix = '';

	//region Statements

	/**
	 * @var \PDOStatement
	 */
	protected $unSubscribeStatement, $subscribedStatement, $isSubscribedStatement,
		$subscribeStatement, $findChildsStatement, $countStatement, $abuseStatement, $rateStatement, $rateFindStatement;

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

		$this->class = "popcorn\\model\\comments\\Comment";

		parent::__construct();

		$this->initStatements();
	}

	protected function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_comments_{$this->tablePrefix}
			(entityId, createdAt, owner, parent, content, editDate, ip, abuse, deleted, level, votesUp, votesDown) VALUES
			(:entityId, :createdAt, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :votesUp, :votesDown)");

		$this->updateStatement =
			$this->prepare("UPDATE pn_comments_{$this->tablePrefix} SET entityId=:entityId, createdAt=:createdAt, owner=:owner,
			parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, votesUp=:votesUp, votesDown=:votesDown WHERE id=:id");

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
			$this->prepare("REPLACE INTO pn_comments_{$this->tablePrefix}_vote (commentId, userId) VALUES (:commentId, :userId)");

		$this->rateFindStatement =
			$this->prepare("SELECT count(*) FROM pn_comments_{$this->tablePrefix}_vote WHERE commentId = :commentId AND userId = :userId");

		$this->stmtGetAllComments =
			$this->prepare("SELECT id,parent FROM pn_comments_{$this->tablePrefix} WHERE entityId = :entityId ORDER BY createdAt ASC");

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

		if ((int)$item->getExtra('subscribe')) {
			$subscribed = $this->isSubscribed($item->getEntityId(), UserFactory::getCurrentUser());

			if (!$subscribed) {
				$this->subscribe($item->getEntityId(), UserFactory::getCurrentUser());
			}
		} else {
			$this->unSubscribe($item->getEntityId(), UserFactory::getCurrentUser());
		}

		$this->sendNotifyMessage($item);
	}

	/**
	 * @param Comment $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
	}


	/**
	 * Отправляем уведомление всем подписавшимся на коммент юзверям
	 *
	 * @param Comment $item
	 */
	private function sendNotifyMessage($item) {

		$users = $this->getSubscribed($item->getEntityId());

		foreach ($users as $user) {

			$mail = MailHelper::getInstance();

			$mail->setFrom('robot@popcornnews.ru');
//			$mail->addAddress($user->getEmail());
			$mail->addAddress('ak@t-agency.ru');
			$mail->Subject = sprintf('Уведомление о новом комментарии на сайте %s', $_SERVER['HTTP_HOST']);
			$mail->msgHTML(
				$this
					->getApp()
					->getTwig()
					->render('/mail/CommentSubscribe.twig', [
						'user'  => $user,
						'title' => sprintf(' &laquo;Звездные дети - %s&raquo;', KidFactory::get($item->getEntityId())->getName())
					])
			);
			$mail->send();
		}

		/*sprintf(
                '%1$s<br>Пользователь %2$s оставил новый комментарий к новости "<a href="%3$s">%4$s</a>, за которой Вы следите (<a href="%3$s">%3$s</a>)<br><br>'.
                'Если Вы больше не хотите получать уведомления, пожалуйста, перейдите по ссылке: <a href="http://www.popcornnews.ru/unsubs/%5$s">http://www.popcornnews.ru/unsubs/%5$s</a>',
                date('d/m/Y H:i'), $ui->user['nick'], $link, $title, $roomName
            )
		 */


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

		while ($comment = $this->stmtGetAllComments->fetch(\PDO::FETCH_ASSOC)) {
//			$cacheKey = MMC::genKey($this->class, __METHOD__, $entityId, $commentId);

//			$comments[] = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($commentId) {
			$comments[] = $comment;
//			});
		}

//		$cacheKey = MMC::genKey($this->class, __METHOD__, $entityId);

//		$tree = MMC::getSet($cacheKey, strtotime('+1 month'), function () use($comments) {
		$tree = $this->makeArrayTree($comments);
		$this->makeTree($tree);


//			return $tree;
//		});


		return $tree;

	}

	private function makeTree(array &$comments) {

		$branch = [];

		foreach ($comments as &$element) {

			$childs = null;

			if (isset($element['childs'])) {
				$childs = $this->makeTree($element['childs']);
			}

//			$cacheKey = MMC::genKey($this->class, 'comment', $element['id']);

			/** @var \popcorn\model\comments\Comment $element */
//			$element = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($element) {
			$element = $this->findById($element['id']);
//			});

			$branch[$element->getId()] = $element;

			if ($childs) {
				$element->setChilds($childs);
			}

		}

		return $branch;

	}

	protected function makeArrayTree(array &$comments, $parentId = 0) {

		$branch = array();

		/** @var \popcorn\model\comments\Comment $element */
		foreach ($comments as $element) {
			if ($element['parent'] == $parentId) {
				$children = $this->makeArrayTree($comments, $element['id']);
				if ($children) {
					$element['childs'] = $children;
				}
				$branch[$element['id']] = $element;
			}
		}

		return $branch;

	}

	public function getLastComment($entityId) {
		$this->stmtGetLastComment->execute([
			':entityId' => $entityId
		]);

		return $this->findById($this->stmtGetLastComment->fetchColumn());
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
	 * @param \popcorn\model\system\users\User $user
	 */
	public function subscribe($roomId, User $user) {
		$this->subscribeStatement->bindParam(':entityId', $roomId);
		$this->subscribeStatement->bindParam(':userId', $user->getId());
		$this->subscribeStatement->execute();
	}

	/**
	 * @param $roomId
	 * @param \popcorn\model\system\users\User $user
	 * @return bool
	 */
	public function isSubscribed($roomId, User $user) {
		$this->isSubscribedStatement->bindValue(':entityId', $roomId);
		$this->isSubscribedStatement->bindValue(':userId', $user->getId());
		$this->isSubscribedStatement->execute();

		return $this->isSubscribedStatement->rowCount() > 0;
	}

	/**
	 * @param $roomId
	 * @return \popcorn\model\system\users\User[]
	 */
	public function getSubscribed($roomId) {
		$this->subscribedStatement->bindParam(':entityId', $roomId);
		$this->subscribedStatement->execute();

		$users = [];

		while ($userId = $this->subscribedStatement->fetchColumn()) {
			$users[] = UserFactory::getUser($userId, ['with' => UserDataMap::WITH_INFO]);
		}

		return $users;
	}

	public function unSubscribe($roomId, User $user) {
		$this->unSubscribeStatement->bindParam(':entityId', $roomId);
		$this->unSubscribeStatement->bindParam(':userId', $user->getId());
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

	public function rate(Comment &$comment, User $user, $action) {


		$doRate = function (Comment $comment, User $user, $action) {
			$this->rateStatement->bindParam(':commentId', $comment->getId());
			$this->rateStatement->bindParam(':userId', $user->getId());

			try {

				$this->rateStatement->execute();

				if ($action == 'up') {
					$comment->addVotesUp();
				} elseif ($action == 'down') {
					$comment->addVotesDown();
				}

				CommentFactory::saveComment($this->tablePrefix, $comment);

			} catch (\PDOException $e) {
			}
		};

		$this->rateFindStatement->execute([
			':commentId' => $comment->getId(),
			':userId'    => $user->getId()
		]);

		$already = $this->rateFindStatement->fetchColumn();

		if (!$already){
			$doRate($comment,$user,$action);
		}
	}

}