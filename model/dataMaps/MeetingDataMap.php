<?php

namespace popcorn\model\dataMaps;

use popcorn\model\persons\Meeting;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\VotingFactory;

class MeetingDataMap extends DataMap {

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

        parent::__construct();
        $this->class = "popcorn\\model\\persons\\Meeting";
        $this->insertStatement = $this->prepare("
            INSERT INTO pn_meetings (firstPerson, secondPerson, title, description, votesUp, votesDown, commentsCount)
            VALUES (:firstPerson, :secondPerson, :title, :description, :votesUp, :votesDown, :commentsCount)");
        $this->updateStatement = $this->prepare("UPDATE pn_meetings SET title=:title, description=:description, votesUp=:votesUp, votesDown=:votesDown, commentsCount=:commentsCount WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_meetings WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_meetings WHERE id=:id");
    }

    /**
     * @param Meeting $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":firstPerson", $item->getFirstPerson()->getId());
        $this->insertStatement->bindValue(":secondPerson", $item->getSecondPerson()->getId());
        $this->insertStatement->bindValue(":title", $item->getTitle());
        $this->insertStatement->bindValue(":description", $item->getDescription());
		$this->insertStatement->bindValue(":votesUp", $item->getVotesUp());
		$this->insertStatement->bindValue(":votesDown", $item->getVotesDown());
		$this->insertStatement->bindValue(":commentsCount", $item->getCommentsCount());
    }

    /**
     * @param Meeting $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":title", $item->getTitle());
        $this->updateStatement->bindValue(":description", $item->getDescription());
        $this->updateStatement->bindValue(":id", $item->getId());
		$this->updateStatement->bindValue(":votesUp", $item->getVotesUp());
		$this->updateStatement->bindValue(":votesDown", $item->getVotesDown());
		$this->updateStatement->bindValue(":commentsCount", $item->getCommentsCount());
    }

    /**
     * @param Meeting $item
     */
    protected function onInsert($item) {
        parent::onInsert($item);
        $item->setVoting(VotingFactory::createUpDownVoting($item->getId()));
    }

    /**
     * @param Meeting $item
     */
    protected function itemCallback($item) {
        parent::itemCallback($item);
        $item->setFirstPerson(PersonFactory::getPerson($item->getFirstPerson()));
        $item->setSecondPerson(PersonFactory::getPerson($item->getSecondPerson()));
    }

    public function find($from = 0, $count = -1) {
        $sql = "SELECT * FROM pn_meetings".$this->getLimitString($from, $count);
        $items = $this->fetchAll($sql);

		return $items;
    }

	public function findByLimit(array $options = [], $from = 0, $count = -1, &$totalFound = 0) {

		$sql = 'SELECT %s FROM pn_meetings';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute();

		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'));

	}

}