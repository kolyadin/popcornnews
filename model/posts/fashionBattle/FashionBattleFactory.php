<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 12:30
 */

namespace popcorn\model\posts\fashionBattle;

use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\NewsTagDataMap;

class FashionBattleFactory {

	/**
	 * @var FashionBattleDataMap
	 */
	private static $dataMap = null;

	public static function save(FashionBattle $fashionBattle) {
		self::checkDataMap();
		self::$dataMap->save($fashionBattle);
	}

	/**
	 * @param $id
	 * @return FashionBattle
	 */
	public static function get($id) {
		self::checkDataMap();

		return self::$dataMap->findById($id);
	}


	/**
	 * @param int $id
	 */
	public static function remove($id) {
		self::checkDataMap();
		self::$dataMap->delete($id);
	}

	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new FashionBattleDataMap());
		}
	}
}