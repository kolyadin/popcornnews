<?php

namespace popcorn\model\dataMaps;

use popcorn\model\persons\Person;
use popcorn\model\system\users\User;

class PersonFanDataMap extends CrossLinkedDataMap {

	private $findStatement;


	public function __construct() {


		parent::__construct();

		$this->findStatement =
			"SELECT %s FROM pn_users AS u
                JOIN pn_persons_fans AS f ON (f.userId = u.id)
                WHERE f.personId = :id
                ";

		$this->findLinkedStatement = $this->prepare(sprintf($this->findStatement, 'u.*'));
		$this->cleanStatement = $this->prepare("DELETE FROM pn_persons_fans WHERE personId = :id");
		$this->insertStatement = $this->prepare("INSERT INTO pn_persons_fans (personId, userId) VALUES (:id, :modelId)");
	}

	/**
	 * @param $personId
	 * @return int
	 */
	public function getOverallCount($personId) {
		static $totalFound = null;

		if ($totalFound === null) {
			$stmt = $this->prepare(sprintf($this->findStatement, 'count(*)'));

			$stmt->execute([':id' => $personId]);

			$totalFound = $stmt->fetchColumn();
		}

		return $totalFound;
	}

	/**
	 * @param $id
	 * @param array $orders
	 * @param array $paginator
	 * @internal param array $offset
	 * @return User[]
	 */
	public function findById($id, array $orders = [], array &$paginator = []) {

		$sql = sprintf($this->findStatement, 'u.*');

		{
			if ($orders) {
				$sql .= $this->getOrderString($orders);
			}

			if ($paginator) {
				$sql .= $this->getLimitString($paginator[0], $paginator[1]);

				$totalFound = $this->getOverallCount($id);
				$paginator['overall'] = $totalFound;
				$paginator['pages'] = ceil($totalFound / $paginator[1]);
			}
		}


		{
			$stmt = $this->prepare($sql);
			$stmt->execute([':id' => $id]);

			$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
		}

		foreach ($items as &$item) {
			$this->getDataMap()->itemCallback($item);
		}

		return $items;
	}


	public function isFan(User $user, Person $person) {

		$stmt = $this->prepare('select count(*) from pn_persons_fans where userId = :userId and personId = :personId');
		$stmt->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':personId', $person->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return ($stmt->fetchColumn() ? true : false);
	}


	/**
	 * @param $personId
	 * @param array $where
	 * @param array $orders
	 * @param array $paginator
	 * @return User[]
	 */
	public function find($personId, array $where = [], array $orders = [], array &$paginator = []) {

		$sql = <<<SQL
SELECT
	users.*
FROM
	     pn_users        users
	JOIN pn_users_info   info ON (info.id = users.userInfo)
	JOIN pn_persons_fans fans ON (fans.userId = users.id)
WHERE
	fans.personId = :personId
SQL;

		{
			if ($where) {
				foreach ($where as $field => $condition) {
					$sql .= sprintf(' AND %s = %u', $field, $condition);
				}
			}

			if ($orders) {
				$sql .= $this->getOrderString($orders);
			}

			if ($paginator) {
				$sql .= $this->getLimitString($paginator[0], $paginator[1]);

				$totalFound = $this->getOverallCount($personId);
				$paginator['overall'] = $totalFound;
				$paginator['pages'] = ceil($totalFound / $paginator[1]);
			}
		}

		{
			$stmt = $this->prepare($sql);
			$stmt->execute([':personId' => $personId]);

			$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
		}

		foreach ($items as &$item) {
			$this->getDataMap()->itemCallback($item);
		}

		return $items;
	}


	protected function mainDataMap() {
		return new UserDataMap(UserDataMap::WITH_ALL);
	}
}