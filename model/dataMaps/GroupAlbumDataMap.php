<?php

namespace popcorn\model\dataMaps;

use popcorn\model\groups\Album;
use popcorn\model\groups\Group;
use popcorn\model\groups\GroupFactory;
use popcorn\model\system\users\UserFactory;

class GroupAlbumDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_GROUP = 2;
	const WITH_USER = 4;
	const WITH_ALL = 7;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();

		$this->class = "popcorn\\model\\groups\\Album";
		$this->insertStatement =
			$this->prepare("
                INSERT INTO pn_groups_albums (groupId, userId, createdAt, editedAt, title)
                VALUES (:groupId, :userId, :createdAt, :editedAt, :title)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_groups_albums SET groupId = :groupId, userId = :userId, createdAt = :createdAt, editedAt = :editedAt, title = :title WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_groups_albums WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_groups_albums WHERE id=:id");
	}

	/**
	 * @param Album $item
	 */
	protected function insertBindings($item) {

		$this->insertStatement->bindValue(':groupId', $item->getGroupId());
		$this->insertStatement->bindValue(':userId', $item->getUserId());
		$this->insertStatement->bindValue(':createdAt', $item->getCreatedAt());
		$this->insertStatement->bindValue(':editedAt', $item->getEditedAt());
		$this->insertStatement->bindValue(':title', $item->getTitle());

	}

	/**
	 * @param Album $item
	 */
	protected function updateBindings($item) {

		$this->updateStatement->bindValue(':groupId', $item->getGroupId());
		$this->updateStatement->bindValue(':userId', $item->getUserId());
		$this->updateStatement->bindValue(':createdAt', $item->getCreatedAt());
		$this->updateStatement->bindValue(':editedAt', $item->getEditedAt());
		$this->updateStatement->bindValue(':title', $item->getTitle());

	}

	/**
	 * @param Album $item
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_ALL) {

//		$item->setCreateTime(\DateTime::createFromFormat('U', $item->getCreateTime()));

		parent::itemCallback($item);

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_GROUP) {
			$item->setGroup(GroupFactory::get($item->getGroupId()));
		}

		if ($modifier & self::WITH_USER){
			$item->setUser(UserFactory::getUser($item->getUserId()));
		}


	}

	/**
	 * @param Album $item
	 */
	protected function onSave($item) {
		parent::onSave($item);
		//$this->images->save($item->getImages(), $item->getId());
	}


	public function find(Group $group, array &$paginator = []) {

		$sql = 'SELECT %s FROM pn_groups_albums WHERE groupId = :groupId';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute([':groupId' => $group->getId()]);
		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString(['editedAt' => 'desc']);
		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		return $this->fetchAll(sprintf($sql, '*'), [':groupId' => $group->getId()]);

	}


}