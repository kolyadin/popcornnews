<?php

namespace popcorn\model\persons\fanfics;

use popcorn\model\persons\fanfics\FanFicDataMap;
use popcorn\model\persons\Person;

class FanFicFactory {

	/**
	 * @var \popcorn\model\persons\fanfics\FanFicDataMap
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
			self::setDataMap(new FanFicDataMap());
		}
	}

	public static function resetDataMap() {
		self::$dataMap = new FanFicDataMap();
	}

	/**
	 * @param $fanficId
	 * @return \popcorn\model\persons\fanfics\FanFic
	 */
	public static function getFanFic($fanficId) {
		self::checkDataMap();

		return self::$dataMap->findById($fanficId);
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
	public static function getFanFicsByPerson(Person $person, array $options = [], $from = 0, $count = 10, &$totalFound = -1) {
		self::checkDataMap();

		return self::$dataMap->findByPerson($person, $options, $from, $count, $totalFound);
	}
}