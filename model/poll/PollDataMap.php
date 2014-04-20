<?php

namespace popcorn\model\poll;

use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\exceptions\ajax\VotingNotAllowException;
use popcorn\model\system\users\UserFactory;

class PollDataMap extends DataMap {

	//Время действия ограничения
	const RESTRICT_INTERVAL = 86400;

	private $opinions;
	private $pdo;

	protected $votes;

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\poll\\Poll";
		$this->insertStatement = $this->prepare("INSERT INTO pn_poll (createdAt, question, status) VALUES (:createdAt, :question, :status)");
		$this->updateStatement = $this->prepare("UPDATE pn_poll SET question=:question, status=:status WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_poll WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_poll WHERE id=:id");

		$this->pdo = PDOHelper::getPDO();

		$this->opinions = new OpinionDataMap();
	}

	/**
	 * @param Poll $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":createdAt", $item->getCreatedAt()->getTimestamp());
		$this->insertStatement->bindValue(":question", $item->getQuestion());
		$this->insertStatement->bindValue(":status", $item->getStatus());
	}

	/**
	 * @param Poll $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":id", $item->getId());
		$this->updateStatement->bindValue(":question", $item->getQuestion());
		$this->updateStatement->bindValue(":status", $item->getStatus());
	}

	/**
	 * @param Poll $item
	 */
	protected function itemCallback($item) {
		parent::itemCallback($item);
		$item->setOpinions($this->opinions->findByParentId($item->getId()));
//        $item->setVotes($this->votes->findByParentId($item->getId()));
	}


	/**
	 * @param Poll $item
	 */
	protected function onSave($item) {

		foreach ($item->getOpinions() as $opinion) {
			$this->opinions->save($opinion, $item->getId());
		}

		/*foreach ($item->getVotes() as $vote) {
			$this->votes->save($vote);
		}*/

		if ($item->getStatus() == 1) {
			PDOHelper::getPDO()->exec('UPDATE pn_poll SET status = 0');

			$stmt = PDOHelper::getPDO()->prepare('UPDATE pn_poll SET status = 1 WHERE id = :pollId');
			$stmt->execute([
				':pollId' => $item->getId()
			]);
		}

		parent::onSave($item);
	}

	/**
	 * @param Poll $poll
	 * @return bool
	 * @throws \popcorn\model\exceptions\ajax\VotingNotAllowException
	 */
	public function isVotingAllow(Poll $poll) {

		$stmt = $this->pdo->prepare('SELECT count(*) FROM pn_poll_voting WHERE checksum = :checksum AND pollId = :pollId AND (:nowTime - votedAt) <= :restrictTime');
		$stmt->bindValue(':checksum', UserFactory::getHeadersChecksum(), \PDO::PARAM_STR);
		$stmt->bindValue(':pollId', $poll->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':nowTime', time(), \PDO::PARAM_INT);
		$stmt->bindValue(':restrictTime', self::RESTRICT_INTERVAL, \PDO::PARAM_INT);

		$stmt->execute();

		if ($stmt->fetchColumn() > 0) {
			throw new VotingNotAllowException();
		} else {
			return true;
		}

	}

	public function find() {
		return $this->fetchAll('SELECT * FROM pn_poll ORDER BY status DESC,createdAt DESC');
	}

	public function findActive() {
		$polls = $this->fetchAll('SELECT * FROM pn_poll WHERE status = 1 ORDER BY createdAt DESC LIMIT 1');

		return $polls[0];
	}

	public function findByParentId($id) {
		$this->findByParentIdStatement->bindValue(':id', $id);
		$this->findByParentIdStatement->execute();
		$item = $this->findByParentIdStatement->fetchObject($this->class);
		if ($item === false) return null;
		$this->itemCallback($item);

		return $item;
	}

	protected function getVoteCounts($id) {
		$sql = "SELECT opinionId, count(id) AS cnt FROM pn_votes WHERE votingId = :id GROUP BY opinionId";
		$items = $this->fetchAll($sql, array(':id' => $id), true);
		$result = array();
		foreach ($items as $item) {
			$result[$item['opinionId']] = $item['cnt'];
		}

		return $result;
	}

}