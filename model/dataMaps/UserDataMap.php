<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\SphinxClient;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\ajax\AlreadyFriendsException;
use popcorn\model\exceptions\ajax\SameUserAddFriendException;
use popcorn\model\exceptions\MysqlException;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\exceptions\ajax\UserAlreadyInBlackListException;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

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

	private $modifier;

	public function __construct($modifier = self::WITH_ALL) {

		parent::__construct();

		$this->modifier = $modifier;

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
			$this->prepare("UPDATE pn_users SET email=:email, password=:password, type=:type,
			 enabled=:enabled, nick=:nick, avatar=:avatar, rating=:rating, banned=:banned,
			  lastVisit=:lastVisit, userInfo=:userInfo, userSettings=:userSettings, userHash=:userHash WHERE id=:id");

		$this->deleteStatement =
			$this->prepare("DELETE FROM pn_users WHERE id=:id");
		$this->findOneStatement =
			$this->prepare("SELECT * FROM pn_users WHERE id=:id");
		$this->findByEmailStatement =
			$this->prepare("SELECT * FROM pn_users WHERE email = :email");
		$this->findByNickStatement =
			$this->prepare("SELECT * FROM pn_users WHERE nick = :nick");
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
	 * @return User
	 */
	protected function itemCallback($item) {
		parent::itemCallback($item);

		$item->setRating($item->getRating()->getPoints());

		if ($this->modifier & self::WITH_AVATAR) {
			$item->setAvatar(ImageFactory::getImage($item->getAvatar()));
		}

		if ($this->modifier & self::WITH_INFO) {
			$item->setUserInfo($this->userInfo->findById($item->getUserInfo()));
		}

		if ($this->modifier & self::WITH_SETTINGS) {
			$item->setUserSettings($this->userSettings->findById($item->getUserSettings()));
		}

		if ($this->modifier & self::WITH_HASH) {
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

		$stmt = $this->prepare('insert into pn_users_status set userId = ?, createdAt = ?, statusMessage = ?');
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

		$stmt = $this->prepare('select statusMessage,createdAt from pn_users_status where userId = ? order by createdAt desc limit 1');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function getStatuses(User $user) {
		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('select id,statusMessage,createdAt from pn_users_status where userId = ? order by createdAt desc');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function removeStatus(User $user, $id) {
		if ($user instanceof GuestUser) {
			throw new NotAuthorizedException();
		}

		$stmt = $this->prepare('delete from `pn_users_status` where `id` = ?');
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

		$stmt = $this->prepare('select * from pn_users_friends where (friendId = :friendId and userId = :userId) or (friendId = :userId and userId = :friendId) and confirmed = "n"');
		$stmt->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':friendId', $friend->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->rowCount()) {
			throw new AlreadyFriendsException();
		}

		$stmt = $this->prepare('INSERT INTO pn_users_friends SET userId = :userId, friendId = :friendId, confirmed = "n"');
		$stmt->execute([
			':userId'   => $user->getId(),
			':friendId' => $friend->getId()
		]);

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

		$stmt = $this->prepare('select count(*) from pn_users_blacklist where userId1 = ? and userId2 = ?');
		$stmt->bindValue(1, $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(2, $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->fetchColumn()) {
			throw new UserAlreadyInBlackListException();
		}

		if (!$stmt->fetchColumn()) {
			$stmt = $this->prepare('insert into pn_users_blacklist set userId1 = ?, userId2 = ?');
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

		$stmt = $this->prepare('select count(*) from pn_users_blacklist where userId1 = :userId1 and userId2 = :userId2');
		$stmt->bindValue(':userId1', $user1->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':userId2', $user2->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->fetchColumn()) {
			return true;
		}

		return false;
	}

	/**
	 * @param User $user
	 * @param User $friend
	 * @return bool
	 */
	public function checkFriendRequest(User $user, User $friend) {

		$stmt = $this->prepare('SELECT count(*) FROM pn_users_friends WHERE userId=:userId AND friendId=:friendId AND confirmed="n"');
		$stmt->execute([
			':userId'   => $user->getId(),
			':friendId' => $friend->getId()
		]);

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

		$stmt = $this->prepare('delete from pn_users_blacklist where userId1 = ? and userId2 = ?');
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
		select
			count(*)
		from
			pn_users_friends
		where
			(
				(friendId = :currentUserId) or
				(userId   = :currentUserId)
			) and
			confirmed = "n"
SQL;
		$stmt = $this->prepare($sql);
		$stmt->bindValue(':currentUserId', $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchColumn();

	}

	public function isFriends(User $user, User $profile) {
		$stmt = $this->prepare('SELECT count(*) FROM pn_users_friends WHERE ((userId=:userId AND friendId=:friendId) OR (friendId=:userId AND userId=:friendId)) AND confirmed="y"');
		$stmt->execute([
			':userId'   => $user->getId(),
			':friendId' => $profile->getId()
		]);

		return $stmt->fetchColumn() ? true : false;
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
			'myFriends'   => true
		];

		$options = array_merge($defaultOptions, $options);

		$stmt = $this->prepare(sprintf('select count(*) from pn_users_friends where friendId = :currentUserId or userId = :currentUserId %s'
			, $options['myFriends'] ? '' : 'and confirmed = "y"'
		));
		$stmt->bindValue('currentUserId', $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$paginator['overall'] = $stmt->fetchColumn();

		$sql = <<<SQL
		select
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
		from
			pn_users_friends    friend
			inner join pn_users user on(user.id = if(friend.userId <> :currentUserId,friend.userId,friend.friendId))
		where
			(
				(friend.friendId = :currentUserId) or
				(friend.userId   = :currentUserId)
			)
			%s
		order by
			onlineStatus desc,
			user.nick    asc,
			user.rating  desc
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

		$stmt = $this->prepare('delete from pn_users_friends where (userId = :userId1 and friendId = :userId2) or (userId = :userId2 and friendId = :userId1) and confirmed = "y"');
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

		$stmt = $this->prepare('update pn_users_friends set confirmed = "y" where friendId = ? and userId = ?');
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

		$stmt = $this->prepare('delete from pn_users_fans where userId = ?');
		$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->prepare('insert into pn_users_fans set userId = ?, personId = ?');

		foreach ($personsId as $personId) {
			$stmt->bindValue(1, $user->getId(), \PDO::PARAM_INT);
			$stmt->bindValue(2, $personId, \PDO::PARAM_INT);
			$stmt->execute();
		}

		return true;
	}

	public function getFans(User $user) {

		$stmt = $this->prepare('select personId from pn_users_fans where userId = ?');
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

		$user = UserFactory::getUser($userId, ['with' => UserDataMap::WITH_ALL]);

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
		$stmt = $this->prepare('select @row := @row+1 as row, user.id from pn_users user, (select @row := 0) row order by user.rating desc,user.id asc');
		$stmt->execute();

		$ratingTable = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

		$stmt = $this->prepare('select * from pn_users where nick like ? order by rating desc,id asc limit 15');
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
	 * @return int
	 */
	public function getOnlineUsersCount() {
		static $oneCall = null;

		if ($oneCall === null) {
			$stmt = $this->prepare('select count(*) from pn_users where (' . time() . ' - lastVisit <= ' . USER::ONLINE_TIME_THRESHOLD . ')');
			$stmt->execute();

			$oneCall = $stmt->fetchColumn();
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

		$stmtCountry = $this->prepare('select id,name from pn_countries order by rating asc');
		$stmtCountry->execute();

		$output['countries'] = $stmtCountry->fetchAll(\PDO::FETCH_OBJ);

		$stmtCity = $this->prepare('select id,name,country_id from pn_cities order by rating asc');
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
select
	user.*
from
	     pn_users      user
	join pn_users_info info on (info.id = user.userInfo)
where
	info.cityId = :cityId
order by
	user.rating desc
SQL;
		return $this->fetchAll($sql . $this->getLimitString($from, $count),
			[':cityId' => $cityId]
		);
	}


	public function getActiveUsers($offset = 0, $limit = 8) {
		$sql = <<<SQL
			SELECT IFNULL(ROUND(SUM(r.points/r.votes) / COUNT(r.id), 1), 0) as rating, u.id, u.nick, u.avatar
			FROM (
				SELECT s.id, s.uid, s.rating as points, count(DISTINCT v.uid) as votes
				FROM `pn_yourstyle_sets` as s
					LEFT JOIN pn_yourstyle_sets_votes as v ON (v.sid = s.id)
				WHERE s.isDraft = 'n'
				GROUP BY s.id
				HAVING votes >= 0
			) as r
				INNER JOIN pn_users as u ON (r.uid = u.id)
			GROUP BY r.uid
			ORDER BY rating DESC
			LIMIT ?, ?
SQL;
		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $offset, \PDO::PARAM_INT);
		$stmt->bindValue(2, $limit, \PDO::PARAM_INT);
		$stmt->execute();

		$users = [];

		while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$loopUser = UserFactory::getUser($row->id);
			$users[] = $loopUser;
		}

		return $users;

	}


}