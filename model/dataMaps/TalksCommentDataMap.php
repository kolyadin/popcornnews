<?php

namespace popcorn\model\dataMaps;

use popcorn\model\im\Comment;

class TalksCommentDataMap extends DataMap {

    //region Statements

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
            $this->prepare("INSERT INTO pn_comments_talks (talkId, date, owner, parent, content, editDate, ip, abuse, deleted, level, ratingUp, ratingDown) VALUES (:talkId, :date, :owner, :parent, :content, :editDate, :ip, :abuse, :deleted, :level, :ratingUp, :ratingDown)");
        $this->updateStatement =
            $this->prepare("UPDATE pn_comments_talks SET talkId=:talkId, date=:date, owner=:owner, parent=:parent, content=:content, editDate=:editDate, ip=:ip, abuse=:abuse, deleted=:deleted, level=:level, ratingUp=:ratingUp, ratingDown=:ratingDown WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_comments_talks WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_comments_talks WHERE id=:id");
        $this->countStatement = $this->prepare("SELECT count(id) FROM pn_comments_talks WHERE talkId = :talkId");
        $this->findChildsStatement = $this->prepare("SELECT * FROM pn_comments_talks WHERE parent = :parent AND talkId = :talkId");
        $this->abuseStatement =
            $this->prepare("INSERT INTO pn_comments_talks_abuse (commentId, userId) VALUES (:commentId, :userId)");
        $this->rateStatement =
            $this->prepare("INSERT INTO pn_comments_talks_vote (commentId, userId) VALUES (:commentId, :userId)");
    }

    public function count($talkId) {
        $this->countStatement->bindValue(':talkId', $talkId);
        $this->countStatement->execute();
        $count = $this->countStatement->fetchColumn(0);

        return $count;
    }

    /**
     * @param int $parentId
     * @param int $talkId
     *
     * @return Comment[]
     */
    public function findChilds($parentId, $talkId) {
        $this->findChildsStatement->bindValue(':parent', $parentId);
        $this->findChildsStatement->bindValue(':talkId', $talkId);
        $this->findChildsStatement->execute();
        $items = $this->findChildsStatement->fetchAll(\PDO::FETCH_CLASS, $this->class);
        foreach($items as &$item) {
            $this->itemCallback($item);
        }

        return $items;
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
        $this->insertStatement->bindValue(":talkId", $item->getPostId());
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
        $this->updateStatement->bindValue(":talkId", $item->getPostId());
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

}