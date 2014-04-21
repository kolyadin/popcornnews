<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\PDOHelper;
use popcorn\model\exceptions\ajax\VotingNotAllowException;
use popcorn\model\exceptions\AjaxException;
use popcorn\model\voting\UpDownVoting;
use popcorn\model\voting\VotingFactory;

class UpDownDataMap extends DataMap {

	//Время действия ограничения
	const RESTRICT_INTERVAL = 86400;

	public $class;

	private $pdo;
	private $checksum;

	public function __construct() {
		$this->class = 'popcorn\\model\\voting\\UpDownVoting';

		$this->pdo = PDOHelper::getPDO();

		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];

		$this->checksum = md5(implode('',[$ip, $browser]));

		$this->initStatements();
	}

	/**
	 * @param UpDownVoting $item
	 */
	protected function itemCallback($item) {


	}

	private function initStatements() {
		$this->insertStatement =
			$this->pdo->prepare("INSERT INTO pn_voting_up_down
            (checksum, votedAt, entity, entityId, vote)
            VALUES (:checksum, :votedAt, :entity, :entityId, :vote)");
	}

	/**
	 * @param \popcorn\model\voting\UpDownVoting $object
	 */
	public function save($object) {
		$this->insertStatement->bindValue(':votedAt', $object->getVotedAt()->getTimestamp(), \PDO::PARAM_INT);

		$this->insertStatement->bindValue(':checksum', $object->getChecksum(), \PDO::PARAM_STR);
		$this->insertStatement->bindValue(':entity', $object->getEntity(), \PDO::PARAM_STR);
		$this->insertStatement->bindValue(':entityId', $object->getEntityId(), \PDO::PARAM_INT);
		$this->insertStatement->bindValue(':vote', $object->getVote(), \PDO::PARAM_STR);

		$this->insertStatement->execute();
	}

	/**
	 * @param object $entity
	 * @return bool
	 * @throws \popcorn\model\exceptions\ajax\VotingNotAllowException
	 */
	public function isAllow($entity) {

		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];

		$checksum = implode('',[$ip, $browser]);

		$stmt = $this->pdo->prepare('SELECT count(*) FROM pn_voting_up_down WHERE checksum = :checksum AND entity = :entity AND entityId = :entityId AND (:nowTime - votedAt) <= :restrictTime');
		$stmt->bindValue(':checksum', $this->checksum, \PDO::PARAM_STR);
		$stmt->bindValue(':entity', get_class($entity), \PDO::PARAM_STR);
		$stmt->bindValue(':entityId', $entity->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':nowTime', time(), \PDO::PARAM_INT);
		$stmt->bindValue(':restrictTime', self::RESTRICT_INTERVAL, \PDO::PARAM_INT);

		$stmt->execute();

		if ($stmt->fetchColumn() > 0) {
			throw new VotingNotAllowException();
		} else {
			return true;
		}

	}


	public function get($ip, $entity) {

		$stmt = $this->pdo->prepare('SELECT * FROM pn_voting_up_down WHERE ip = :ip AND entity = :entity AND entityId = :entityId');
		$stmt->bindValue(':checksum', $this->checksum, \PDO::PARAM_STR);
		$stmt->bindValue(':entity', get_class($entity), \PDO::PARAM_STR);
		$stmt->bindValue(':entityId', $entity->getId(), \PDO::PARAM_INT);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

	}

	public function getCount($ip, $entity) {

		$stmt = $this->pdo->prepare('SELECT count(*) FROM pn_voting_up_down WHERE ip = :ip AND entity = :entity AND entityId = :entityId');
		$stmt->bindValue(':checksum', $this->checksum, \PDO::PARAM_STR);
		$stmt->bindValue(':entity', get_class($entity), \PDO::PARAM_STR);
		$stmt->bindValue(':entityId', $entity->getId(), \PDO::PARAM_INT);

		$stmt->execute();

		return $stmt->fetchColumn();

	}


}