<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\SphinxClient;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\ajax\AlreadyFriendsException;
use popcorn\model\exceptions\ajax\SameUserAddFriendException;
use popcorn\model\exceptions\MysqlException;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\exceptions\ajax\UserAlreadyInBlackListException;
use popcorn\model\exceptions\UserException;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserRating;
use popcorn\lib\mmc\MemcacheObject;
use popcorn\lib\mmc\MMC;

class UserDataMap extends DataMap {

	const CACHE_TAG_ON_UPDATE = 'user-update';
	const CACHE_TAG_ON_REMOVE = 'user-remove';
	const CACHE_TAG_ON_INSERT = 'user-insert';

	const WITH_NONE = 1;

	const WITH_AVATAR = 2;
	const WITH_INFO = 4;
	const WITH_SETTINGS = 8;
	const WITH_HASH = 16;

	const WITH_ALL = 31;

	/**
	 * @var \PDOStatement
	 */
	private $findByEmailStatement;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();
		$this->class = "popcorn\\model\\system\\users\\User";

		$this->userInfo = new UserInfoDataMap();
		$this->userSettings = new UserSettingsDataMap();
		$this->userHash = new UserHashDataMap();

		$this->insertStatement =
			$this->prepare("
                INSERT INTO pn_users
                    (email, password, type, enabled, nick, avatar, rating, banned, lastVisit, createTime, userInfo, userSettings, userHash)
                VALUES
                    (:email, :password, :type, :enabled, :nick, :avatar, :rating, :banned, :lastVisit, :createTime, :userInfo, :userSettings, :userHash)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_users SET email=:email, password=:password, type=:type, enabled=:enabled, nick=:nick, avatar=:avatar, rating=:rating, banned=:banned, lastVisit=:lastVisit, userInfo=:userInfo, userSettings=:userSettings, userHash=:userHash WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_users WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_users WHERE id=:id");
		$this->findByEmailStatement = $this->prepare("SELECT * FROM pn_users WHERE email = :email");
		$this->findByNickStatement = $this->prepare("SELECT * FROM pn_users WHERE nick = :nick");
	}

	protected function onUpdate($item) {
//		MMC::delByTag('')
	}

	/**
	 * @param User $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":email", $item->getEmail());
		$this->insertStatement->bindValue(":password", $item->getPassword());
		$this->insertStatement->bindValue(":type", $item->getType());
		$this->insertStatement->bindValue(":enabled", $item->getEnabled());
		$this->insertStatement->bindValue(":nick", $item->getNick());
		$this->insertStatement->bindValue(":avatar", $item->getAvatar()->getId());
		$this->insertStatement->bindValue(":rating", $item->getRating()->getPoints());
		$this->insertStatement->bindValue(":banned", $item->getBanned());
		$this->insertStatement->bindValue(":lastVisit", $item->getLastVisit());
		$this->insertStatement->bindValue(":createTime", $item->getCreateTime());
		$this->insertStatement->bindValue(":userInfo", $item->getUserInfo()->getId());
		$this->insertStatement->bindValue(":userSettings", $item->getUserSettings()->getId());
		$this->insertStatement->bindValue(":userHash", $item->getUserHash()->getId());
	}

	/**
	 * @param User $item
	 */
	protected function updateBindings($item) {

		$this->updateStatement->bindValue(":email", $item->getEmail());
		$this->updateStatement->bindValue(":password", $item->getPassword());
		$this->updateStatement->bindValue(":type", $item->getType());
		$this->updateStatement->bindValue(":enabled", $item->getEnabled());
		$this->updateStatement->bindValue(":nick", $item->getNick());
		$this->updateStatement->bindValue(":avatar", $item->getAvatar()->getId());
		$this->updateStatement->bindValue(":rating", $item->getRating()->getPoints());
		$this->updateStatement->bindValue(":banned", $item->getBanned());
		$this->updateStatement->bindValue(":lastVisit", $item->getLastVisit());
		$this->updateStatement->bindValue(":userInfo", $item->getUserInfo()->getId());
		$this->updateStatement->bindValue(":userSettings", $item->getUserSettings()->getId());
		$this->updateStatement->bindValue(":userHash", $item->getUserHash()->getId());
		$this->updateStatement->bindValue(":id", $item->getId());
	}

	/**
	 * @param User $item
	 *
	 * @throws \popcorn\model\exceptions\MysqlException
	 * @return User
	 */
	protected function prepareItem($item) {
		if ($item->isGuest()) {
			throw new MysqlException();
		}
		if (!is_object($item->getAvatar())) {
			$item->setAvatar(ImageFactory::getImage($item->getAvatar()));
		}

		$this->userInfo->save($item->getUserInfo());
		$this->userSettings->save($item->getUserSettings());
		$this->userHash->save($item->getUserHash());


		return parent::prepareItem($item);
	}

	/**
	 * @param User $item
	 * @param int $modifier
	 * @return User
	 */
	protected function itemCallback($item, $modifier = self::WITH_ALL) {
		parent::itemCallback($item);

		$item->setRating($item->getRating()->getPoints());

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_AVATAR) {
			$item->setAvatar(ImageFactory::getImage($item->getAvatar()));
		}

		if ($modifier & self::WITH_INFO) {
			$item->setUserInfo($this->userInfo->findById($item->getUserInfo()));
		}

		if ($modifier & self::WITH_SETTINGS) {
			$item->setUserSettings($this->userSettings->findById($item->getUserSettings()));
		}

		if ($modifier & self::WITH_HASH) {
			$item->setUserHash($this->userHash->findById($item->getUserHash()));
		}


	}

	/**
	 * @param $query
	 * @param array $orders
	 *
	 * @return User[]
	 * @throws \InvalidArgumentException
	 */
	public function findByName($query, $orders = array()) {
		if (empty($query)) {
			throw new \InvalidArgumentException("Empty query not allowed");
		}
		$sql = "SELECT * FROM pn_users WHERE nick LIKE :query";
		$sql .= $this->getOrderString($orders);
		$sql .= ' LIMIT 15';

		return $this->fetchAll($sql, array(':query' => '%' . $query . '%'));
	}

	/**
	 * @param $query
	 * @param int $from
	 * @param $count
	 * @return User[]
	 */
	public function findByName2($query, $from = 0, $count = 10) {

		require_once 'lib/SphinxApi.php';

		$sphinx = new SphinxClient();

		$sphinx->SetServer('localhost', 9312);
		$sphinx->SetConnectTimeout(1);
		$sphinx->SetMaxQueryTime(5);
		$sphinx->SetArrayResult(true);
		$sphinx->SetMatchMode(SPH_MATCH_EXTENDED);
		$sphinx->SetSortMode(SPH_SORT_ATTR_ASC, 'nick_size');
		$sphinx->SetRankingMode(SPH_RANK_WORDCOUNT);
		$sphinx->SetLimits($from, $count);

		$sphinx->SetFieldWeights([
			'nick' => 100
		]);

		$query = $sphinx->EscapeString($query);

		$result = $sphinx->Query(sprintf('@nick =%1$s | *%1$s*', $query), 'usersIndex');

		$users = [];

		if (isset($result['matches']) && count($result['matches'])) {
			foreach ($result['matches'] as $match) {
				$users[] = UserFactory::getUser($match['id']);
			}
		}

		return $users;

	}

	public function findBy(array $where) {
		echo '<pre>', print_r($where, true), '</pre>';

		$out = $val = array();

		foreach ($where as $column => $value) {
			$out[] = sprintf('%s = ?', $column);
			$val[] = $value;
		}

		$stmt = $this->prepare('select * from pn_users where ' . implode(' and ', $out));

		for ($i = 1; $i <= count($out); $i++) {
			$stmt->bindValue($i, $val[$i - 1]);
		}

		$stmt->execute();
		$item = $stmt->fetchObject($this->class);

		if ($item === false) return null;

		$this->itemCallback($item);

		return $item;

	}

	/**
	 * @param string $email
	 *
	 * @return User
	 */
	public function findByEmail($email) {
		$this->findByEmailStatement->bindValue(':email', $email);
		$this->findByEmailStatement->execute();
		$item = $this->findByEmailStatement->fetchObject($this->class);
		if ($item === false) return null;

		$this->itemCallback($item);

		return $item;
	}

	public function findByNick($nick) {
		$this->findByNickStatement->bindValue(':nick', $nick);
		$this->findByNickStatement->execute();
		$item = $this->findByNickStatement->fetchObject($this->class);
		if ($item === false) return null;

		$this->itemCallback($item);

		return $item;
	}

	/**
	 * @param User $user
	 * @param $statusMessage
	 * @return bool
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function statusUpdate(User $user, $statusMessage) {
		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('INSERT INTO pn_users_status SET userId = ?, createdAt = ?, statusMessage = ?');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, time(), \PDO::PARAM_INT);
		$stmt->bindValue(3, $statusMessage, \PDO::PARAM_STR);

		$stmt->execute();

		return true;
	}

	public function getActiveStatus(User $user) {
		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('SELECT statusMessage,createdAt FROM pn_users_status WHERE userId = ? ORDER BY createdAt DESC LIMIT 1');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function getStatuses(User $user) {
		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('SELECT id,statusMessage,createdAt FROM pn_users_status WHERE userId = ? ORDER BY createdAt DESC');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function removeStatus(User $user, $id) {
		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('DELETE FROM `pn_users_status` WHERE `id` = ?');
		$stmt->bindValue(1, $id, \PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}

	/**
	 * @param User $user
	 * @param User $friend
	 * @return bool
	 * @throws \popcorn\model\exceptions\ajax\AlreadyFriendsException
	 * @throws \popcorn\model\exceptions\ajax\SameUserAddFriendException
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function friendRequest(User $user, User $friend) {

		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		if ($user->getId() == $friend->getId()) {
			throw new SameUserAddFriendException();
		}

		$stmt = $this->prepare('SELECT * FROM pn_users_friends WHERE (friendId = :friendId AND userId = :userId) OR (friendId = :userId AND userId = :friendId) AND confirmed = "n"');
		$stmt->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':friendId', $friend->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->rowCount()) {
			throw new AlreadyFriendsException();
		}

		$stmt = $this->prepare('INSERT INTO pn_users_friends SET userId = ?, friendId = ?, confirmed = "n"');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, $friend->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @return bool
	 * @throws \popcorn\model\exceptions\ajax\UserAlreadyInBlackListException
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function addToBlackList(User $user1, User $user2) {
		if ($user1 instanceof GuestUser || $user2 instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('SELECT count(*) FROM pn_users_blacklist WHERE userId1 = ? AND userId2 = ?');
		$stmt->bindValue(1, $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->fetchColumn()) {
			throw new UserAlreadyInBlackListException();
		}

		if (!$stmt->fetchColumn()) {
			$stmt = $this->prepare('INSERT INTO pn_users_blacklist SET userId1 = ?, userId2 = ?');
			$stmt->bindValue(1, $user1->getId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $user2->getId(), \PDO::PARAM_INT);
			$stmt->execute();

			return true;
		}

		return false;
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @return bool
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function checkInBlackList(User $user1, User $user2) {
		if ($user1 instanceof GuestUser || $user2 instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('SELECT count(*) FROM pn_users_blacklist WHERE userId1 = :userId1 AND userId2 = :userId2');
		$stmt->bindValue(':userId1', $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':userId2', $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->fetchColumn()) {
			return true;
		}

		return false;
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @return bool
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function removeFromBlackList(User $user1, User $user2) {
		if ($user1 instanceof GuestUser || $user2 instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('DELETE FROM pn_users_blacklist WHERE userId1 = ? AND userId2 = ?');
		$stmt->bindValue(1, $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}

	/**
	 * @param User $user
	 * @return string
	 */
	public function getNewFriendsCount(User $user) {

		$sql = <<<SQL
		SELECT
			count(*)
		FROM
			pn_users_friends
		WHERE
			(
				(friendId = :currentUserId) OR
				(userId   = :currentUserId)
			) AND
			confirmed = "n"
SQL;
		$stmt = $this->prepare($sql);
		$stmt->bindValue(':currentUserId', $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchColumn();

	}

	/**
	 * @param User $user
	 * @param array $options
	 * @param array $offset
	 * @param array $paginator
	 * @return User[]
	 */
	public function getFriends(User $user, array $options = [], array $offset = [], &$paginator = []) {

		$defaultOptions = [
			'onlineFirst' => true,
			'myFriends' => true
		];

		$options = array_merge($defaultOptions, $options);

		$stmt = $this->prepare(sprintf('SELECT count(*) FROM pn_users_friends WHERE friendId = :currentUserId OR userId = :currentUserId %s'
			, $options['myFriends'] ? '' : 'and confirmed = "y"'
		));
		$stmt->bindValue('currentUserId', $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$paginator['overall'] = $stmt->fetchColumn();

		$sql = <<<SQL
		SELECT
			if(
				friend.userId <> :currentUserId,
				friend.userId,
				friend.friendId
			) realFriendId,
			if(
				((unix_timestamp() - user.lastVisit) <= :threshold),
				1,
				0
			) onlineStatus,
			friend.confirmed,
			friend.userId,
			friend.friendId
		FROM
			pn_users_friends    friend
			INNER JOIN pn_users user ON(user.id = if(friend.userId <> :currentUserId,friend.userId,friend.friendId))
		WHERE
			(
				(friend.friendId = :currentUserId) OR
				(friend.userId   = :currentUserId)
			)
			%s
		ORDER BY
			onlineStatus DESC,
			user.nick    ASC,
			user.rating  DESC
		%s
SQL;

		$stmt = $this->prepare(sprintf($sql,
			$options['myFriends']
				? ''
				: 'and friend.confirmed = "y"',
			count($offset)
				? sprintf('limit %u,%u', $offset[0], $offset[1])
				: ''
		));

		$stmt->bindValue(':currentUserId', $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':threshold', User::ONLINE_TIME_THRESHOLD, \PDO::PARAM_INT);
		$stmt->execute();

		$rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

		$friends = array();

		foreach ($rows as $row) {
			$user = UserFactory::getUser($row->realFriendId);
			$user->setExtra('confirmed', $row->confirmed);
			$user->setExtra('userId', $row->userId);
			$user->setExtra('friendId', $row->friendId);

			$friends[] = $user;
		}

		return $friends;
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @return bool
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function removeFromFriends(User $user1, User $user2) {
		if ($user1 instanceof GuestUser || $user2 instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('DELETE FROM pn_users_friends WHERE (userId = :userId1 AND friendId = :userId2) OR (userId = :userId2 AND friendId = :userId1) AND confirmed = "y"');
		$stmt->bindValue(':userId1', $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':userId2', $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @return bool
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	public function confirmFriendship(User $user1, User $user2) {
		if ($user1 instanceof GuestUser || $user2 instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('UPDATE pn_users_friends SET confirmed = "y" WHERE friendId = ? AND userId = ?');
		$stmt->bindValue(1, $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}

	/**
	 * @param User $user
	 * @param Array $personsId
	 * @return bool
	 */
	public function addToFans(User $user, array $personsId = []) {

		$stmt = $this->prepare('DELETE FROM pn_users_fans WHERE userId = ?');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->prepare('INSERT INTO pn_users_fans SET userId = ?, personId = ?');

		foreach ($personsId as $personId) {
			$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $personId, \PDO::PARAM_INT);
			$stmt->execute();
		}

		return true;
	}

	public function getFans(User $user) {

		$stmt = $this->prepare('SELECT personId FROM pn_users_fans WHERE userId = ?');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$persons = $stmt->fetchAll(\PDO::FETCH_COLUMN);

		foreach ($persons as &$person) {
			$person = PersonFactory::getPerson($person);
		}

		return $persons;
	}

	/**
	 * Проверяем авторизацию
	 *
	 * @param $userId
	 * @param $securityHash
	 * @return mixed
	 */
	public function findByHash($userId, $securityHash) {

		$user = UserFactory::getUser($userId);

		if (!($user instanceof User)) {
			return null;
		}

		if ($securityHash == $user->getUserHash()->getSecurityHash()) {
			return $user;
		}

		return null;
	}

	public function find($from = 0, $count = -1) {
		$sql = "SELECT * FROM pn_users" . $this->getLimitString($from, $count);

		return $this->fetchAll($sql);
	}

	/**
	 * @param int $from
	 * @param $count
	 * @return User[]
	 */
	public function getTopUsers($from = 0, $count = -1) {

		$cacheKey = md5(__METHOD__ . serialize(func_get_args()));

		return $this->fetchAll('select sql_no_cache * from pn_users order by rating desc' . $this->getLimitString($from, $count));

	}

	public function getTopUsersByNick($nick) {
		$stmt = $this->prepare('SELECT @row := @row+1 AS row, user.id FROM pn_users user, (SELECT @row := 0) row ORDER BY user.rating DESC,user.id ASC');
		$stmt->execute();

		$ratingTable = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

		$stmt = $this->prepare('SELECT * FROM pn_users WHERE nick LIKE ? ORDER BY rating DESC,id ASC LIMIT 15');
		$stmt->bindValue(1, "%$nick%", \PDO::PARAM_STR);
		$stmt->execute();

		$users = [];

		while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$loopUser = UserFactory::getUser($row->id);
			$loopUser->setExtra('row', array_search($row->id, $ratingTable));

			$users[] = $loopUser;
		}

		return $users;

	}

	public function getOnlineUsers($from = 0, $count = -1, $order = 'rating desc') {
		return $this->fetchAll('select * from pn_users where (' . time() . ' - lastVisit <= ' . USER::ONLINE_TIME_THRESHOLD . ') order by ' . $order . ' ' . $this->getLimitString($from, $count));
	}

	/**
	 * Количество пользователей онлайн
	 * @cache 1 минута
	 * @return int
	 */
	public function getOnlineUsersCount() {
		static $oneCall = null;

		if ($oneCall === null) {

			$cacheKey = MMC::genKey(__CLASS__, __METHOD__);

			$oneCall = MMC::getSet($cacheKey, strtotime('+1 minute'), function () {
				$stmt = $this->prepare('SELECT count(*) FROM pn_users WHERE (:currentTime - lastVisit <= :threshold)');
				$stmt->bindValue(':currentTime', time(), \PDO::PARAM_INT);
				$stmt->bindValue(':threshold', USER::ONLINE_TIME_THRESHOLD, \PDO::PARAM_INT);
				$stmt->execute();

				return $stmt->fetchColumn();
			});

		}

		return $oneCall;
	}

	/**
	 * @param int $from
	 * @param $count
	 * @return array
	 */
	public function getNewUsers($from = 0, $count = -1) {
		return $this->fetchAll('select * from pn_users where enabled = 1 order by id desc' . $this->getLimitString($from, $count));
	}

	public function getCountriesAndCities() {

		$stmtCountry = $this->prepare('SELECT id,name FROM pn_countries ORDER BY rating ASC');
		$stmtCountry->execute();

		$output['countries'] = $stmtCountry->fetchAll(\PDO::FETCH_OBJ);

		$stmtCity = $this->prepare('SELECT id,name,country_id FROM pn_cities ORDER BY rating ASC');
		$stmtCity->execute();

		while ($city = $stmtCity->fetch(\PDO::FETCH_OBJ)) {
			$output['cities'][$city->country_id][] = $city;
		}

		return $output;
	}

	/**
	 * @param $cityId
	 * @param int $from
	 * @param $count
	 * @return User[]
	 */
	public function getUsersByCity($cityId, $from = 0, $count = -1) {
		$sql = <<<SQL
SELECT
	user.*
FROM
	     pn_users      user
	JOIN pn_users_info info ON (info.id = user.userInfo)
WHERE
	info.cityId = :cityId
ORDER BY
	user.rating DESC
SQL;
		return $this->fetchAll($sql . $this->getLimitString($from, $count),
			[':cityId' => $cityId]
		);
	}


}