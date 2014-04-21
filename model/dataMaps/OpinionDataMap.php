<?php

namespace popcorn\model\dataMaps;

use popcorn\model\voting\Opinion;

class OpinionDataMap extends RelationDataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\voting\\Opinion";
        $this->insertStatement = $this->prepare("INSERT INTO pn_opinions (title, votingId) VALUES (:title, :id)");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_opinions WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_opinions WHERE id=:id");
        $this->findByParentStatement = $this->prepare("SELECT * FROM pn_opinions WHERE votingId=:id");
    }

    /**
     * @param Opinion $object
     * @param $id
     */
    public function save($object, $id) {
        $object->setVotingId($id);
        parent::save($object);
    }

    /**
     * @param Opinion $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":id", $item->getVotingId());
    }

}