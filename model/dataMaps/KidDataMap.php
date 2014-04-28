<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\ImageFactory;
use popcorn\model\persons\Kid;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\VotingFactory;

class KidDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_PHOTO = 2;
	const WITH_PARENTS = 4;
	const WITH_ALL = 7;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

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
		$sql = "SELECT * FROM pn_kids" . $this->getLimitString($from, $count);

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
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_PHOTO) {

		$modifier = $this->getModifier($this, $modifier);

		parent::itemCallback($item);

		if ($modifier & self::WITH_PHOTO) {
			$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
		}

		if ($modifier & self::WITH_PARENTS) {

			if ((int)$item->getFirstParent() > 0) {
				$item->setFirstParent(PersonFactory::getPerson($item->getFirstParent()));
			}

			if ((int)$item->getSecondParent() > 0) {
				$item->setSecondParent(PersonFactory::getPerson($item->getSecondParent()));
			}
		}

		$item->setBirthDate(new \DateTime($item->getBirthDate()));

	}

	/**
	 * @param Kid $item
	 */
	protected function prepareItem($item) {

		$item->setCommentsCount($item->getCommentsCount());

	}

	public function findWithPaginator(array $orders = [], array &$paginator = []) {

		$orders = array_merge([
			'name' => 'asc'
		], $orders);

		$sql = 'SELECT %s FROM pn_kids';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute();
		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString($orders);
		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		return $this->fetchAll(sprintf($sql, '*'));

	}

	/**
	 * @param Kid $kid
	 */
	public function updateCommentsCount($kid) {

		$stmt = $this->prepare('update pn_kids set commentsCount = commentsCount+1 where id = ?');
		$stmt->bindValue(1, $kid->getId(), \PDO::PARAM_INT);
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