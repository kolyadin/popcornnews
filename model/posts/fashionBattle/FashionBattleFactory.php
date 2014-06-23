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
use popcorn\model\posts\NewsPost;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

class FashionBattleFactory {

	/**
	 * @var FashionBattleDataMap
	 */
	private static $dataMap = null;

	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new FashionBattleDataMap());
		}
	}

	public static function canVote(User $user, FashionBattle $fb) {
		self::checkDataMap();
		return self::$dataMap->canVote($user, $fb);
	}

	public static function doVoting(User $user, FashionBattle $fb, $option) {
		self::checkDataMap();
		return self::$dataMap->doVoting($user, $fb, $option);
	}

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


}