<?php

namespace popcorn\model\persons\facts;

use popcorn\model\dataMaps\DataMap;
use popcorn\model\exceptions\ajax\FactVotingNotAllowException;
use popcorn\model\persons\Person;
use popcorn\model\system\users\User;

class FactDataMap extends DataMap {

	public function __construct() {

		parent::__construct();

		$this->class = "popcorn\\model\\persons\\facts\\Fact";
		$this->initStatements();
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_persons_facts
			(fact, personId, createdAt, userId, trustRating, voteRating)
				VALUES
			(:fact, :personId, :createdAt, :userId, :trustRating, :voteRating)");
		$this->updateStatement =
			$this->prepare("
			UPDATE pn_persons_facts SET fact=:fact,personId=:personId,createdAt=:createdAt,userId=:userId,trustRating=:trustRating,voteRating=:voteRating WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_persons_facts WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_persons_facts WHERE id=:id");
	}

	/**
	 * @param Fact $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":fact", $item->getFact());
		$this->insertStatement->bindValue(":personId", $item->getPersonId());
		$this->insertStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->insertStatement->bindValue(":userId", $item->getUserId());
		$this->insertStatement->bindValue(":trustRating", $item->getTrustRating());
		$this->insertStatement->bindValue(":voteRating", $item->getVoteRating());
	}

	/**
	 * @param Fact $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":fact", $item->getFact());
		$this->updateStatement->bindValue(":personId", $item->getPersonId());
		$this->updateStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->updateStatement->bindValue(":userId", $item->getUserId());
		$this->updateStatement->bindValue(":trustRating", $item->getTrustRating());
		$this->updateStatement->bindValue(":voteRating", $item->getVoteRating());

		$this->updateStatement->bindValue(":id", $item->getId());
	}

	/**
	 * @param Fact $item
	 */
	public function itemCallback($item) {
		parent::itemCallback($item);
	}

	/**
	 * @param Fact $item
	 */
	protected function onInsert($item) {
//		$this->attachImages($item);
//		$this->attachTags($item);
//		$this->attachFashionBattle($item);

//		MMC::delByTag('post');
	}

	/**
	 * @param Fact $item
	 */
	protected function onUpdate($item) {
//		$this->attachImages($item);
//		$this->attachTags($item);
//		$this->attachFashionBattle($item);

//		MMC::delByTag('post');
	}

	/**
	 * @param $factId
	 */
	protected function onRemove($factId) {
		$this->getPDO()->prepare('
			DELETE FROM pn_persons_facts_votes WHERE factId = :factId
		')->execute([
			':factId' => $factId
		]);
	}


	/**
	 * @param \popcorn\model\persons\Person $person
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param int $totalFound
	 * @return \popcorn\model\persons\facts\Fact[]
	 */
	public function findByPerson(Person $person, array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'orderBy' => [
				'createdAt' => 'desc'
			]
		], $options);

		$sql = 'SELECT %s FROM pn_persons_facts WHERE personId = :personId';

		$binds = [
			':personId' => $person->getId()
		];

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'), $binds);
	}

	/**
	 * @param Fact $fact
	 * @param User $user
	 * @param $category
	 * @throws \popcorn\model\exceptions\ajax\FactVotingNotAllowException
	 * @return bool
	 */
	public function isVotingAllow(Fact $fact, User $user, $category) {

		$stmt = $this->prepare('SELECT count(*) FROM pn_persons_facts_votes WHERE factId = :factId AND userId = :userId AND category = :category');
		$stmt->execute([
			':factId'   => $fact->getId(),
			':userId'   => $user->getId(),
			':category' => $category
		]);

		if ($stmt->fetchColumn() > 0) {
			throw new FactVotingNotAllowException();
		}

		return true;

	}

	/**
	 * @param Fact $fact
	 * @param User $user
	 * @param $category
	 * @param $vote
	 */
	public function addVote(Fact $fact, User $user, $category, $vote) {

		$stmt = $this->prepare('INSERT INTO pn_persons_facts_votes SET factId = :factId, userId = :userId, category = :category, vote = :vote');
		$stmt->execute([
			':factId'   => $fact->getId(),
			':userId'   => $user->getId(),
			':category' => $category,
			':vote'     => $vote
		]);

		$subquery1 = 'SELECT FLOOR(SUM(vote)/COUNT(vote)) FROM pn_persons_facts_votes WHERE category = 1 AND factId = :factId';
		$subquery2 = 'SELECT FLOOR(SUM(vote)/COUNT(vote)) FROM pn_persons_facts_votes WHERE category = 2 AND factId = :factId';

		$stmt = $this->prepare("UPDATE pn_persons_facts SET trustRating = ($subquery1), voteRating = ($subquery2) WHERE id = :factId LIMIT 1");
		$stmt->execute([
			':factId' => $fact->getId()
		]);

	}
}