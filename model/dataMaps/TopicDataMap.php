<?php

namespace popcorn\model\dataMaps;

use popcorn\model\groups\Group;
use popcorn\model\groups\GroupFactory;
use popcorn\model\persons\Person;
use popcorn\model\system\users\UserFactory;
use popcorn\model\talks\Talk;

/**
 * Class TopicDataMap
 * @package popcorn\model\dataMaps
 */
class TopicDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_GROUP = 2;
	const WITH_OWNER = 4;
	const WITH_LAST_COMMENT = 8;
	const WITH_ALL = 15;

	/**
	 * @param DataMapHelper $helper
	 */
	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();

		$this->class = 'popcorn\\model\\groups\\Topic';
		$this->insertStatement =
			$this->prepare("INSERT INTO `pn_groups_topics` (`group`, owner, createTime, name, content, poll, votesUp, votesDown) VALUES (:group, :owner, :createTime, :name, :content, :poll, :votesUp, :votesDown)");
		$this->updateStatement = $this->prepare("UPDATE pn_groups_topics SET `group`=:group, owner=:owner, createTime=:createTime, name=:name, content=:content, poll=:poll, votesUp=:votesUp, votesDown=:votesDown WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_groups_topics WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_groups_topics WHERE id=:id");
	}

	/**
	 * @param \popcorn\model\groups\Topic $item
	 */
	protected function insertBindings($item) {

		$this->insertStatement->bindValue(':group', $item->getGroup()->getId());
		$this->insertStatement->bindValue(':owner', $item->getOwner()->getId());
		$this->insertStatement->bindValue(':createTime', $item->getCreateTime()->format('Y-m-d H:i:s'));
		$this->insertStatement->bindValue(':name', $item->getName());
		$this->insertStatement->bindValue(':content', $item->getContent());
		$this->insertStatement->bindValue(':poll', $item->getPoll());
		$this->insertStatement->bindValue(':votesUp', $item->getVotesUp());
		$this->insertStatement->bindValue(':votesDown', $item->getVotesDown());
	}

	/**
	 * @param \popcorn\model\groups\Topic $item
	 */
	protected function updateBindings($item) {

		$this->updateStatement->bindValue(':group', $item->getGroup()->getId());
		$this->updateStatement->bindValue(':owner', $item->getOwner()->getId());
		$this->updateStatement->bindValue(':createTime', $item->getCreateTime()->format('Y-m-d H:i:s'));
		$this->updateStatement->bindValue(':name', $item->getName());
		$this->updateStatement->bindValue(':content', $item->getContent());
		$this->updateStatement->bindValue(':poll', $item->getPoll());
		$this->updateStatement->bindValue(':votesUp', $item->getVotesUp());
		$this->updateStatement->bindValue(':votesDown', $item->getVotesDown());
	}

	/**
	 * @param \popcorn\model\groups\Topic $item
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_ALL) {

		$item->setCreateTime(\DateTime::createFromFormat('U', $item->getCreateTime()));

		parent::itemCallback($item);

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_GROUP) {
			$item->setGroup(GroupFactory::get($item->getGroup()));
		}

		if ($modifier & self::WITH_OWNER) {
			$item->setOwner(UserFactory::getUser($item->getOwner()));
		}

		if ($modifier & self::WITH_LAST_COMMENT) {

			$dataMap = new TopicCommentDataMap();
			$lastComment = $dataMap->getLastComment($item->getId());

			if ($lastComment) {
				$item->setLastComment($lastComment);
			}
		}
	}

	/**
	 * @param Talk $item
	 */
	protected function onInsert($item) {

	}

	public function getLastTopicInGroup(Group $group) {

//		$topic = $this->fetchAll('select * from pn_groups_topics where `group` = :group and ');

	}

	public function findByGroup(Group $group, array &$paginator = []) {

		$sql = 'SELECT %s FROM pn_groups_topics WHERE `group` = :groupId';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute([':groupId' => $group->getId()]);
		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString(['lastCommentTime' => 'desc', 'createTime' => 'desc']);
		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		return $this->fetchAll(sprintf($sql, '*'), [':groupId' => $group->getId()]);

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

		if (!count($talks)) {
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