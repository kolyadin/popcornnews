<?php

namespace popcorn\model\groups;

use popcorn\model\dataMaps\GroupDataMap;
use popcorn\model\dataMaps\GroupMembersDataMap;
use popcorn\model\system\users\User;

/**
 * Class GroupFactory
 * @package \popcorn\model\groups
 */
class GroupFactory {

//region Fields

	/**
	 * @var \popcorn\model\dataMaps\GroupDataMap
	 */
	private static $dataMap;

//endregion

	/**
	 */
	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::$dataMap = new GroupDataMap();
		}
	}

	/**
	 * @param \popcorn\model\groups\Group $item
	 */
	public static function save($item) {
		self::checkDataMap();
		self::$dataMap->save($item);
	}

	/**
	 * @param int $id
	 *
	 * @return \popcorn\model\groups\Group
	 */
	public static function get($id) {
		self::checkDataMap();

		return self::$dataMap->findById($id);
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function delete($id) {
		self::checkDataMap();

		return self::$dataMap->delete($id);
	}

	/**
	 * @return GroupDataMap
	 */
	public static function dataMapProxy() {
		self::checkDataMap();

		return self::$dataMap;
	}

	/**
	 * @param Group $group
	 * @param array $options
	 * @param int $from
	 * @param int $count
	 * @param int $totalFound
	 * @return GroupMembers[]
	 */
	public static function getMembers(Group $group, array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'with' => GroupMembersDataMap::WITH_NONE
		], $options);

		return (new GroupMembersDataMap($options['with']))
			->getMembers($group, $options, $from, $count, $totalFound);
	}

	public static function addMember(Group $group, User $user) {
		(new GroupMembersDataMap())->addMember($group, $user);
	}

	public static function getMemberStatus(Group $group, User $user) {

		return (new GroupMembersDataMap())->memberStatus($group, $user);

	}

}