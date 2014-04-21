<?php

namespace popcorn\model\dataMaps;

use popcorn\model\voting\Vote;

class VoteDataMap extends RelationDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\voting\\Vote";
        $this->insertStatement =
            $this->prepare("
                INSERT INTO pn_votes (userId, IP, date, opinionId, votingId)
                VALUES (:userId, :IP, :date, :opinionId, :votingId)");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_votes WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_votes WHERE id=:id");
        $this->findByParentStatement = $this->prepare("SELECT * FROM pn_votes WHERE votingId=:id");
    }

    /**
     * @param Vote $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":userId", $item->getUserId());
        $this->insertStatement->bindValue(":IP", $item->getIP());
        $this->insertStatement->bindValue(":date", $item->getDate());
        $this->insertStatement->bindValue(":opinionId", $item->getOpinionId());
        $this->insertStatement->bindValue(":votingId", $item->getVotingId());
    }

}