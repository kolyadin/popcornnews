<?php

namespace popcorn\model\dataMaps;

use popcorn\model\exceptions\Exception;
use popcorn\model\groups\GroupFactory;
use popcorn\model\groups\GroupMembers;
use popcorn\model\groups\Group;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\User;

class GroupMembersDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_USER = 2;
	const WITH_GROUP = 4;

	const WITH_ALL = 7;

	private $modifier;

	public function __construct($modifier = self::WITH_NONE) {

		parent::__construct();

		$this->modifier = $modifier;

		$this->class = "popcorn\\model\\groups\\GroupMembers";
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_groups_members (`group`, user, joinTime, confirm, request) VALUES (:group, :user, :joinTime, :confirm, :request)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_groups_members SET `group`=:group, user=:user, joinTime=:joinTime, confirm=:confirm, request=:request WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_groups_members WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_groups_members WHERE id=:id");
	}

	/**
	 * @param GroupMembers $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":group", $item->getGroup()->getId());
		$this->insertStatement->bindValue(":user", $item->getUser()->getId());
		$this->insertStatement->bindValue(":joinTime", $item->getJoinTime());
		$this->insertStatement->bindValue(":confirm", $item->getConfirm());
		$this->insertStatement->bindValue(":request", $item->getRequest());
	}

	/**
	 * @param GroupMembers $item
	 */
	protected function updateBindings($item) {

		$this->updateStatement->bindValue(":group", $item->getGroup()->getId());
		$this->updateStatement->bindValue(":user", $item->getUser()->getId());
		$this->updateStatement->bindValue(":joinTime", $item->getJoinTime());
		$this->updateStatement->bindValue(":confirm", $item->getConfirm());
		$this->updateStatement->bindValue(":request", $item->getRequest());

	}

	/**
	 * @param \popcorn\model\groups\GroupMembers $item
	 * @param int $modifier
	 */
	protected function itemCallback($item) {

		parent::itemCallback($item);

		if ($this->modifier & self::WITH_USER) {
			$item->setUser(UserFactory::getUser($item->getUser()));
		}

		if ($this->modifier & self::WITH_GROUP) {
			$item->setGroup(GroupFactory::get($item->getGroup()));
		}
	}

	/**
	 * @param \popcorn\model\groups\GroupMembers $item
	 */
	protected function onSave($item) {

		$this->updateMembersCount($item->getGroup());

	}

	private function updateMembersCount(Group $group) {

		$this
			->prepare('UPDATE pn_groups SET membersCount = (SELECT count(*) FROM pn_groups_members WHERE `group` = :groupId AND confirm = "y") WHERE id = :groupId')
			->execute([
				':groupId' => $group->getId()
			]);

	}

	/**
	 * @param Group $group
	 * @param array $options
	 * @param int $from
	 * @param int $count
	 * @param int $totalFound
	 *
	 * @return GroupMembers[]
	 */
	public function getMembers(Group $group, array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'orderBy' => [
				'joinTime' => 'desc'
			]
		], $options);

		$sql = 'SELECT * FROM pn_groups_members WHERE `group` = :group AND confirm = "y" AND request = "y"';

		$binds = [
			':group' => $group->getId()
		];

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll($sql, $binds);
	}

	/**
	 * @param Group $group
	 * @param User $user
	 * @return GroupMembers | bool
	 */
	public function memberExists(Group $group, User $user) {

		$member = $this->fetchAll('SELECT * FROM pn_groups_members WHERE `group` = :group AND user = :user LIMIT 1', [
			':group' => $group->getId(),
			':user'  => $user->getId()
		]);

		if (isset($member[0])) {
			return $member;
		}

		return false;
	}

	public function memberStatus(Group $group, User $user) {

		return $this->fetchOne('SELECT * FROM pn_groups_members WHERE `group` = :group AND user = :user LIMIT 1', [
			':group' => $group->getId(),
			':user'  => $user->getId()
		]);

	}

	/**
	 * Добавляем пользователя в участники
	 *
	 * @param Group $group
	 * @param User $user
	 * @throws \popcorn\model\exceptions\Exception
	 */
	public function addMember(Group $group, User $user) {

		$member = $this->memberExists($group, $user);

		if ($member) {
			throw new Exception('Пользователь уже состоит в группе', 1);
		}

		$member = new GroupMembers();
		$member->setGroup($group);
		$member->setUser($user);
		$member->setJoinTime(new \DateTime());
		$member->setConfirm('y');
		$member->setRequest('y');

		$this->save($member);

	}

	/**
	 * Удаляем пользователя из членов
	 *
	 * @param Group $group
	 * @param User $user
	 * @throws \popcorn\model\exceptions\Exception
	 */
	public function removeMember(Group $group, User $user) {

		$member = $this->memberExists($group, $user);

		if (!$member) {
			throw new Exception('Пользователя нет в группе');
		}

		$this
			->prepare('DELETE FROM pn_groups_members WHERE `group` = :groupId AND user = :userId')
			->execute([
				':groupId' => $group->getId(),
				':userId' => $user->getId()
			]);

		$this->updateMembersCount($group);

	}
}