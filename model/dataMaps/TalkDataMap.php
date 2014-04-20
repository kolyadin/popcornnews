<?php

namespace popcorn\model\dataMaps;

use popcorn\model\persons\Person;
use popcorn\model\system\users\UserFactory;
use popcorn\model\talks\Talk;
use popcorn\model\voting\VotingFactory;

/**
 * Class TalkDataMap
 * @package popcorn\model\dataMaps
 */
class TalkDataMap extends DataMap {

	/**
	 */
	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}
		parent::__construct();
		$this->class = 'popcorn\\model\\talks\\Talk';
		$this->insertStatement =
			$this->prepare("INSERT INTO `pn_talks` (createTime, owner, title, content, rating) VALUES (:createTime, :owner, :title, :content, :rating)");
		$this->updateStatement = $this->prepare("UPDATE pn_talks SET title=:title, content=:content WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_talks WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_talks WHERE id=:id");
	}

	/**
	 * @param \popcorn\model\talks\Talk $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(':createTime', $item->getCreateTime()->format('Y-m-d H:i:s'));
		$this->insertStatement->bindValue(':owner', $item->getOwner()->getId());
		$this->insertStatement->bindValue(':title', $item->getTitle());
		$this->insertStatement->bindValue(':content', $item->getContent());
		$this->insertStatement->bindValue(':rating', $item->getRating()->getId());
	}

	/**
	 * @param \popcorn\model\talks\Talk $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(':title', $item->getTitle());
		$this->updateStatement->bindValue(':content', $item->getContent());
		$this->updateStatement->bindValue(':id', $item->getId());
	}

	/**
	 * @param Talk $item
	 */
	protected function itemCallback($item) {
		$item->setCreateTime(new \DateTime($item->getCreateTime()));
		$item->setOwner(UserFactory::getUser($item->getOwner()));
//		$item->setRating(VotingFactory::getUpDown($item->getRating()));

		if ($item->getPerson() > 0) {
			$item->setPerson($item->getPerson());
		}

		parent::itemCallback($item);
	}

	/**
	 * @param Talk $item
	 */
	protected function onInsert($item) {

		$stmt = $this->prepare('INSERT INTO pn_talks_persons SET talkId = ?, personId = ?');
		$stmt->bindValue(1, $item->getId());
		$stmt->bindValue(2, $item->getPerson()->getId());
		$stmt->execute();

		parent::onSave($item);
	}

	/**
	 * @param Person $person
	 * @return Talk[]
	 */
	public function findByPerson(Person $person) {
		{
			$stmt = $this->prepare('SELECT talkId FROM pn_talks_persons WHERE personId = ?');
			$stmt->bindValue(1, $person->getId(), \PDO::PARAM_INT);
			$stmt->execute();

			$talks = $stmt->fetchAll(\PDO::FETCH_COLUMN);
		}

		if (!count($talks)){
			return null;
		}

		{
			$stmt = $this->prepare('select * from pn_talks where id in (' . join(',', $talks) . ') order by id desc');
			$stmt->execute();

			$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
		}

		if ($items === false) return null;

		foreach ($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;

	}

	public function findBy(array $where) {
		$out = $val = array();

		foreach ($where as $column => $value) {
			$out[] = sprintf('%s = ?', $column);
			$val[] = $value;
		}

		$stmt = $this->prepare('select * from pn_talks where ' . implode(' and ', $out));

		for ($i = 1; $i <= count($out); $i++) {
			$stmt->bindValue($i, $val[$i - 1]);
		}

		$stmt->execute();
		$item = $stmt->fetchObject($this->class);

		if ($item === false) return null;

		$this->itemCallback($item);

		return $item;
	}
}