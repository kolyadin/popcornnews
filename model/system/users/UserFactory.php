<?php

namespace popcorn\model\system\users;


use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\PersonFanDataMap;
use popcorn\model\dataMaps\UserDataMap;

class UserFactory {

	/**
	 * @var User
	 */
	private static $currentUser;

	/**
	 * @var UserDataMap
	 */
	private static $dataMap = null;

	/**
	 * @param $userId
	 * @param array $options
	 *
	 * @return User
	 */
	public static function getUser($userId, array $options = []) {

		$options = array_merge([
			'with' => UserDataMap::WITH_AVATAR & UserDataMap::WITH_HASH
		], $options);

		$dataMap = new UserDataMap($options['with']);

		return $dataMap->findById($userId);
	}

	/**
	 * @param int $from
	 * @param int $count
	 *
	 * @return User[]
	 */
	public static function getUsers($from = 0, $count = -1) {
		self::checkDataMap();

		return self::$dataMap->find($from, $count);
	}

	/**
	 * @param User $items
	 */
	public static function save(User $items) {
		self::checkDataMap();
		self::$dataMap->save($items);
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::$dataMap = new UserDataMap();
		}
	}

	public static function getCurrentUser() {
		self::checkDataMap();
		if (is_null(self::$currentUser)) {
			self::guestUser();
		}

		return self::$currentUser;
	}


	//@todo перенести в словари
	public static function getCountryNameById($countryId) {
		$stmt = PDOHelper::getPDO()->prepare('select name from pn_countries where id = ? limit 1');
		$stmt->bindValue(1, $countryId, \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchColumn();
	}

	//@todo перенести в словари
	public static function getCityNameById($cityId) {
		$stmt = PDOHelper::getPDO()->prepare('select name from pn_cities where id = ? limit 1');
		$stmt->bindValue(1, $cityId, \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchColumn();
	}

	public static function getCountries() {
		$stmt = PDOHelper::getPDO()->prepare('select id, name from pn_countries order by rating');
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public static function getHeadersChecksum() {
		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];

		return md5(implode('', [$ip, $browser]));
	}

	public static function login($email, $pass) {
		self::checkDataMap();
		$user = self::$dataMap->findByEmail($email);
		if (!is_null($user)) {

			if (password_verify($pass, $user->getPassword())) {
				self::$currentUser = $user;
				return true;
			}
		}

		self::guestUser();

		return false;
	}

	public static function loginByHash($userId, $securityHash) {
		self::checkDataMap();

		$user = self::$dataMap->findByHash($userId, $securityHash);

		if ($user instanceof User) {
			self::$currentUser = $user;
			return true;
		}

		self::guestUser();

		return false;
	}

	/**
	 * @param string $query
	 *
	 * @return User[]
	 */
	public static function searchUsers($query) {
		self::checkDataMap();

		return self::$dataMap->findByName($query, array('nick' => DataMap::ASC));
	}

	/**
	 * @param User $user
	 * @return User[]
	 */
	public static function getSubscribedPersons(User $user) {
		self::checkDataMap();

		return self::$dataMap->getSubscribedPersons($user);
	}


	public static function notifyCounter($user) {


	}

	private static function guestUser() {
		self::$currentUser = new GuestUser();
	}

	public static function logout() {
		self::guestUser();
	}

	public static function banById($id, $time) {
		self::checkDataMap();
		$user = self::getUser($id);
		if (!is_null($user)) {
			$user->setBanned(1);
			$user->getUserInfo()->setBanDate($time);
		}
		self::save($user);
	}

	/**
	 * @param int $userType
	 *
	 * @return bool
	 */
	public static function checkMinUserRights($userType) {
		return self::$currentUser->getType() >= $userType;
	}

	public static function unsubscribeAllPersons(User $user) {
		return (new PersonFanDataMap())->unsubscribeAllPersons($user);
	}
}