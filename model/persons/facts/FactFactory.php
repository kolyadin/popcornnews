<?php

namespace popcorn\model\persons\facts;

use popcorn\model\persons\Person;
use popcorn\model\system\users\User;

class FactFactory {

	/**
	 * @var FactDataMap
	 */
	private static $dataMap = null;

	public static function saveFact(Fact $fact) {
		self::checkDataMap();
		self::$dataMap->save($fact);
	}

	/**
	 * @param int $id
	 */
	public static function removeFact($id) {
		self::checkDataMap();
		self::$dataMap->delete($id);
	}

	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new FactDataMap());
		}
	}

	public static function resetDataMap() {
		self::$dataMap = new FactDataMap();
	}

	/**
	 * @param $factId
	 * @return \popcorn\model\persons\facts\Fact
	 */
	public static function getFact($factId) {
		self::checkDataMap();

		return self::$dataMap->findById($factId);
	}


	/**
	 * Факты по дате, новые выше
	 *
	 * @param \popcorn\model\persons\Person $person
	 * @param array $options
	 * @param int $from
	 * @param int $count
	 * @param int $totalFound
	 * @return \popcorn\model\persons\facts\Fact[]
	 */
	public static function getFacts(Person $person, array $options = [], $from = 0, $count = 10, &$totalFound = -1) {
		self::checkDataMap();

		return self::$dataMap->findByLimit($person, $options, $from, $count, $totalFound);
	}

	/**
	 * @param Fact $fact
	 * @param User $user
	 * @param $category
	 * @return bool
	 */
	public static function isVotingAllow(Fact $fact, User $user, $category) {
		self::checkDataMap();

		return self::$dataMap->isVotingAllow($fact, $user, $category);
	}

	public static function addVote(Fact $fact, User $user, $category, $vote) {
		self::checkDataMap();

		self::$dataMap->addVote($fact, $user, $category, $vote);
	}
}