<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\ImageFactory;
use popcorn\model\persons\Kid;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\VotingFactory;

class KidDataMap extends DataMap {

    public function __construct() {
        parent::__construct();
        $this->class = "popcorn\\model\\persons\\Kid";
        $this->insertStatement =
            $this->prepare("INSERT INTO pn_kids (firstParent, secondParent, name, sex, description, birthDate, photo, votesUp, votesDown, commentsCount)
            VALUES (:firstParent, :secondParent, :name, :sex, :description, :birthDate, :photo, :votesUp, :votesDown, :commentsCount)");
        $this->updateStatement =
            $this->prepare("
            UPDATE pn_kids SET name=:name, sex=:sex, description=:description, birthDate=:birthDate, photo=:photo, votesUp=:votesUp, votesDown=:votesDown, commentsCount=:commentsCount WHERE id=:id");
        $this->deleteStatement = $this->prepare("DELETE FROM pn_kids WHERE id=:id");
        $this->findOneStatement = $this->prepare("SELECT * FROM pn_kids WHERE id=:id");
    }

    /**
     * @param int $from
     * @param $count
     *
     * @return Kid[]
     */
    public function getKids($from = 0, $count = -1) {
        $sql = "SELECT * FROM pn_kids".$this->getLimitString($from, $count);

        return $this->fetchAll($sql);
    }

    /**
     * @param Kid $item
     */
    protected function insertBindings($item) {
        $this->insertStatement->bindValue(":firstParent", $item->getFirstParent()->convert());
        $this->insertStatement->bindValue(":secondParent", $item->getSecondParent()->convert());
        $this->insertStatement->bindValue(":name", $item->getName());
		$this->insertStatement->bindValue(":sex", $item->getSex());
        $this->insertStatement->bindValue(":description", $item->getDescription());
        $this->insertStatement->bindValue(":birthDate", $item->getBirthDate()->format('Y-m-d'));
        $this->insertStatement->bindValue(":photo", $item->getPhoto()->convert());
		$this->insertStatement->bindValue(':votesUp', $item->getVotesUp());
		$this->insertStatement->bindValue(':votesDown', $item->getVotesDown());
    }

    /**
     * @param Kid $item
     */
    protected function updateBindings($item) {
        $this->updateStatement->bindValue(":name", $item->getName());
		$this->updateStatement->bindValue(":sex", $item->getSex());
        $this->updateStatement->bindValue(":description", $item->getDescription());
        $this->updateStatement->bindValue(":birthDate", $item->getBirthDate()->format('Y-m-d'));
        $this->updateStatement->bindValue(":photo", $item->getPhoto()->convert());
		$this->updateStatement->bindValue(':votesUp', $item->getVotesUp());
		$this->updateStatement->bindValue(':votesDown', $item->getVotesDown());
    }

    /**
     * @param Kid $item
     */
    protected function itemCallback($item) {
        $item->setFirstParent(PersonFactory::getPerson($item->getFirstParent()));
        $item->setSecondParent(PersonFactory::getPerson($item->getSecondParent()));
        $item->setBirthDate(new \DateTime($item->getBirthDate()));
        parent::itemCallback($item);
        $item->setPhoto(ImageFactory::getImage($item->getPhoto()));
    }

	/**
	 * @param Kid $item
	 */
	protected function prepareItem($item){

		$item->setCommentsCount($item->getCommentsCount());

	}

	/**
	 * @param Kid $kid
	 */
	public function updateCommentsCount($kid){

		$stmt = $this->prepare('update pn_kids set commentsCount = commentsCount+1 where id = ?');
		$stmt->bindValue(1,$kid->getId(),\PDO::PARAM_INT);
		$stmt->execute();

		return true;

	}

    /**
     * @param $id
     *
     * @return null|Kid
     */
    public function findById($id) {
        return parent::findById($id);
    }


}