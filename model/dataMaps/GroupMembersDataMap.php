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

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}
		parent::__construct();
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
	protected function itemCallback($item, $modifier = self::WITH_ALL) {

		parent::itemCallback($item);

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_USER) {
			$item->setUser(UserFactory::getUser($item->getUser()));
		}

		if ($modifier & self::WITH_GROUP) {
			$item->setGroup(GroupFactory::get($item->getGroup()));
		}
	}

	/**
	 * @param Group $group
	 * @return int
	 */
	public function getMembersCount(Group $group) {
		$stmt = $this->prepare('SELECT count(*) FROM pn_groups_members WHERE `group` = :group');
		$stmt->execute([':group' => $group->getId()]);

		return $stmt->fetchColumn();
	}

	/**
	 * @param Group $group
	 *
	 * @param array $paginator
	 * @return GroupMembers[]
	 */
	public function getMembers(Group $group, array &$paginator) {

		$sql = 'SELECT * FROM pn_groups_members WHERE `group` = :group';

		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$totalFound = $this->getMembersCount($group);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		return $this->fetchAll($sql, [':group' => $group->getId()]);

	}

	/**
	 * @param Group $group
	 * @param User $user
	 * @return GroupMembers | bool
	 */
	public function memberExists(Group $group, User $user) {

		$member = $this->fetchAll('SELECT * FROM pn_groups_members WHERE `group` = :group AND user = :user LIMIT 1', [
			':group' => $group->getId(),
			':user' => $user->getId()
		]);

		if (isset($member[0])) {
			return $member;
		}

		return false;
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
			throw new Exception('Пользователь уже состоит в группе',1);
		}

		$dataMap = new GroupMembersDataMap();

		$member = new GroupMembers();
		$member->setGroup($group);
		$member->setUser($user);
		$member->setJoinTime(new \DateTime());
		$member->setConfirm('y');
		$member->setRequest('y');

		$dataMap->save($member);

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

		$dataMap = new GroupMembersDataMap();
		$dataMap->delete($member->getId());
	}
}