<?php

namespace popcorn\model\dataMaps;

use popcorn\model\exceptions\Exception;
use popcorn\model\im\Comment;
use popcorn\model\posts\NewsPost;

class NewsCommentDataMap extends DataMap {

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
        $this->class = "popcorn\\model\\im\\Comment";
        $this->insertStatement =
            $this->prepare("INSERT INTO pn_comments_news (postId, date, owner, parent, content, editDate, ip, abuse, deleted, level, ratingUp, ratingDown) VALUES (:postId, :date, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :ratingUp, :ratingDown)");
        $this->updateStatement =
            $this->prepare("UPDATE pn_comments_news SET postId=:postId, date=:date, owner=:owner, parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, ratingUp=:ratingUp, ratingDown=:ratingDown WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_comments_news WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_comments_news WHERE id=:id");
        $this->countStatement = $this->prepare("SELECT count(id) FROM pn_comments_news WHERE postId = :postId");
        $this->findChildsStatement = $this->prepare("SELECT * FROM pn_comments_news WHERE parent = :parent AND postId = :postId");
        $this->subscribeStatement =
            $this->prepare("INSERT INTO pn_comments_news_subscribe (newsId, userId) VALUES (:newsId, :userId)");
        $this->isSubscribedStatement =
            $this->prepare("SELECT * FROM pn_comments_news_subscribe WHERE newsId = :newsId AND userId = :userId");
        $this->subscribedStatement =
            $this->prepare("SELECT userId FROM pn_comments_news_subscribe WHERE newsId = :newsId");
        $this->unSubscribeStatement =
            $this->prepare("DELETE FROM pn_comments_news_subscribe WHERE newsId = :newsId AND userId = :userId");
        $this->abuseStatement =
            $this->prepare("INSERT INTO pn_comments_news_abuse (commentId, userId) VALUES (:commentId, :userId)");
        $this->rateStatement =
            $this->prepare("INSERT INTO pn_comments_news_vote (commentId, userId) VALUES (:commentId, :userId)");
    }


	/**
	 * Обновляем счетчик комментариев в новостях, при создании нового коммента
	 * @param Comment $item
	 */
	protected function onInsert($item) {

		$stmt = $this->prepare('update pn_news set comments = comments+1 where id = :newsId');
		$stmt->bindValue(':newsId',$item->getPostId(),\PDO::PARAM_INT);
		$stmt->execute();

	}

	/**
	 * @param Comment $item
	 */
	protected function onUpdate($item) {

	}

	/**
	 * @param Comment $item
	 *
	 * @return Comment
	 */
	protected function prepareItem($item) {
		if(is_null($item->getId())) {
			$item->setDate(time());
		}
		if($item->isChanged()) {
			$item->setEditDate(time());
		}

		return parent::prepareItem($item);
	}

    public function count($postId) {
        $this->countStatement->bindValue(':postId', $postId);
        $this->countStatement->execute();
        $count = $this->countStatement->fetchColumn(0);

        return $count;
    }

	public function getAllComments(NewsPost $post) {

		$stmt = $this->prepare('SELECT id FROM pn_comments_news WHERE postId = ? ORDER BY date ASC');
		$stmt->bindValue(1, $post->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if (!$stmt->rowCount()){
			return null;
		}

		$comments = [];

//		$start = microtime(1);

		while ($commentId = $stmt->fetch(\PDO::FETCH_COLUMN)) {

//			$cacheKey = MMC::genKey($this->class, $commentId);

//			MMC::del(md5("comment_kid_$commentId"));
//			$comments[] = MMC::getSet($cacheKey, strtotime('+1 month'), function () use ($commentId) {
				$comments[] = $this->findById($commentId);
//			});

		}

//		echo microtime(1) - $start , '<hr/>';

		$tree = $this->makeTree($comments, 0);

		return $tree;

	}

	/**
	 * @param $postId
	 * @return Comment
	 */
	public function getLastComment($postId) {

		$stmt = $this->prepare('SELECT id FROM pn_comments_news WHERE postId = ? ORDER BY id DESC LIMIT 1');
		$stmt->bindValue(1, $postId, \PDO::PARAM_INT);
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
     * @param int $postId
     *
     * @return Comment[]
     */
    public function findChilds($parentId, $postId) {
        $this->findChildsStatement->bindValue(':parent', $parentId);
        $this->findChildsStatement->bindValue(':postId', $postId);
        $this->findChildsStatement->execute();
        $items = $this->findChildsStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);
        foreach($items as &$item) {
            $this->itemCallback($item);
        }

        return $items;
    }

    /**
     * @param $roomId
     * @param $userId
     */
    public function subscribe($roomId, $userId) {
        $this->subscribeStatement->bindParam(':newsId', $roomId);
        $this->subscribeStatement->bindParam(':userId', $userId);
        $this->subscribeStatement->execute();
    }

    public function isSubscribed($roomId, $userId) {
        $this->isSubscribedStatement->bindValue(':newsId', $roomId);
        $this->isSubscribedStatement->bindValue(':userId', $userId);
        $this->isSubscribedStatement->execute();

        return $this->isSubscribedStatement->rowCount() > 0;
    }

    public function getSubscribed($roomId) {
        $this->subscribedStatement->bindParam(':newsId', $roomId);
        $this->subscribedStatement->execute();

        return $this->subscribedStatement->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function unSubscribe($roomId, $userId) {
        $this->unSubscribeStatement->bindParam(':newsId', $roomId);
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
        }
        catch(\PDOException $e) {
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
        }
        catch(\PDOException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Comment $item
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
    }

    /**
     * @param Comment $item
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
        $this->updateStatement->bindValue(":id", $item->getId());
    }



}