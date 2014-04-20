<?php

namespace popcorn\model\poll;

use popcorn\model\dataMaps\RelationDataMap;
use popcorn\model\poll\Opinion;

class OpinionDataMap extends RelationDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\poll\\Opinion";
        $this->insertStatement = $this->prepare("INSERT INTO pn_poll_opinions (title, pollId, votes) VALUES (:title, :pollId, :votes)");
		$this->updateStatement = $this->prepare("UPDATE pn_poll_opinions SET title=:title, pollId=:pollId, votes=:votes WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_poll_opinions WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_poll_opinions WHERE id=:id");
        $this->findByParentStatement = $this->prepare("SELECT * FROM pn_poll_opinions WHERE pollId=:id");
    }

    /**
     * @param Opinion $object
     * @param $id
     */
    public function save($object, $id) {
        $object->setPollId($id);
        parent::save($object);
    }

    /**
     * @param Opinion $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":pollId", $item->getPollId());
		$this->insertStatement->bindValue(":votes", $item->getVotes());
    }

	/**
	 * @param Opinion $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":id", $item->getId());
		$this->updateStatement->bindValue(":title", $item->getTitle());
		$this->updateStatement->bindValue(":pollId", $item->getPollId());
		$this->updateStatement->bindValue(":votes", $item->getVotes());
	}

}