<?php

namespace popcorn\model\dataMaps;

use popcorn\model\voting\Voting;

class VotingDataMap extends DataMap {

    /**
     * @var OpinionDataMap
     */
    private $opinions;
    /**
     * @var VoteDataMap
     */
    protected $votes;

    /**
     * @var \PDOStatement
     */
    private $findByParentIdStatement;

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\voting\\Voting";
        $this->insertStatement = $this->prepare("INSERT INTO pn_voting (parentId, title) VALUES (:parentId, :title)");
        $this->updateStatement = $this->prepare("UPDATE pn_voting SET parentId=:parentId, title=:title WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_voting WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_voting WHERE id=:id");
        $this->findByParentIdStatement = $this->prepare("SELECT * FROM pn_voting WHERE parentId=:id");

        $this->opinions = new OpinionDataMap();
        $this->votes = new VoteDataMap();
    }

    /**
     * @param Voting $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":parentId", $item->getParentId());
        $this->insertStatement->bindValue(":title", $item->getTitle());
    }

    /**
     * @param Voting $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":parentId", $item->getParentId());
        $this->updateStatement->bindValue(":title", $item->getTitle());
        $this->updateStatement->bindValue(":id", $item->getId());
    }

    /**
     * @param Voting $item
     */
    protected function itemCallback($item) {
        parent::itemCallback($item);
        $item->setOpinions($this->opinions->findByParentId($item->getId()));
        $item->setVotes($this->votes->findByParentId($item->getId()));
    }

    /**
     * @param Voting $item
     */
    protected function onSave($item) {
        foreach($item->getOpinions() as $opinion) {
            $this->opinions->save($opinion, $item->getId());
        }
        foreach($item->getVotes() as $vote) {
            $this->votes->save($vote);
        }
        parent::onSave($item);
    }

    public function findByParentId($id) {
        $this->findByParentIdStatement->bindValue(':id', $id);
        $this->findByParentIdStatement->execute();
        $item = $this->findByParentIdStatement->fetchObject($this->class);
        if($item === false) return null;
        $this->itemCallback($item);

        return $item;
    }

    protected function getVoteCounts($id) {
        $sql = "SELECT opinionId, count(id) AS cnt FROM pn_votes WHERE votingId = :id GROUP BY opinionId";
        $items = $this->fetchAll($sql, array(':id' => $id), true);
        $result = array();
        foreach($items as $item) {
            $result[$item['opinionId']] = $item['cnt'];
        }

        return $result;
    }

}