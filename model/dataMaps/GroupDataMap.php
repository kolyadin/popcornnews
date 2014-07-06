<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\SaveFirstException;
use popcorn\model\groups\Group;
use popcorn\model\persons\Person;
use popcorn\model\system\users\UserFactory;
use popcorn\model\content\Album;
use popcorn\model\tags\Tag;

/**
 * Class GroupDataMap
 * @package popcorn\model\dataMaps
 */
class GroupDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_TAGS = 2;
	const WITH_OWNER = 4;
	const WITH_ALBUMS = 8;
	const WITH_MEMBERS = 16;

	const WITH_ALL = 31;

	/**
	 * @var \PDOStatement
	 */
	private $cleanTagsStatement;
	/**
	 * @var \PDOStatement
	 */
	private $getTagsStatement;
	/**
	 * @var \PDOStatement
	 */
	private $cleanAlbumsStatement;
	/**
	 * @var \PDOStatement
	 */
	private $getAlbumsStatement;

	private $modifier;

	/**
	 */
	public function __construct($modifier = self::WITH_NONE) {

		parent::__construct();

		$this->modifier = $modifier;

		$this->groupMembers = new GroupMembersDataMap();

		$this->class = 'popcorn\\model\\groups\\Group';
		$this->insertStatement =
			$this->prepare("INSERT INTO `pn_groups` (title, description, createTime, editTime, private, owner, poster) VALUES (:title, :description, :createTime, :editTime, :private, :owner, :poster)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_groups SET title=:title, description=:description, private=:private, poster=:poster WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_groups WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_groups WHERE id=:id");

		$this->cleanTagsStatement = $this->prepare("DELETE FROM pn_groups_tags WHERE groupId = :id");
		$this->insertTagsStatement = $this->prepare("INSERT INTO pn_groups_tags (groupId, type, entityId) VALUES (:groupId, :type, :entityId)");
		$this->getTagsStatement = $this->prepare("SELECT t.* FROM pn_groups_tags AS l
        INNER JOIN pn_tags AS t ON (l.tagId = t.id)
        WHERE l.groupId = :id");

		$this->cleanAlbumsStatement = $this->prepare("DELETE FROM pn_groups_albums WHERE groupId = :id");
		$this->insertAlbumsStatement = $this->prepare("INSERT INTO pn_groups_albums (groupId, albumId) VALUES (:groupId, :albumId)");
		$this->getAlbumsStatement = $this->prepare("SELECT a.* FROM pn_groups_albums AS ga
        INNER JOIN pn_albums AS a ON (ga.albumId = a.id)
        WHERE ga.albumId = :id");

		$this->cleanMembersStatement = $this->prepare("DELETE FROM pn_groups_members WHERE groupId = :id");
		$this->insertMembersStatement = $this->prepare("INSERT INTO pn_groups_members (groupId, userId, joinTime, confirm, request) VALUES (:groupId, :userId, :joinTime, :confirm, :request)");
		$this->getMembersStatement = $this->prepare("SELECT u.* FROM pn_groups_members AS gm INNER JOIN pn_users AS u ON (gm.userId = u.id) WHERE gm.groupId = :id");

		$this->updateCountMembersStatement = $this->prepare("UPDATE pn_groups SET membersCount = (SELECT count(*) FROM pn_groups_members WHERE groupId = :id) WHERE id = :id");

	}

	/**
	 * @param \popcorn\model\groups\Group $item
	 */
	protected function insertBindings($item) {

		$this->insertStatement->bindValue(':title', $item->getTitle());
		$this->insertStatement->bindValue(':description', $item->getDescription());
		$this->insertStatement->bindValue(':createTime', $item->getCreateTime()->format('Y-m-d H:i:s'));
		$this->insertStatement->bindValue(':editTime', $item->getEditTime()->format('Y-m-d H:i:s'));
		$this->insertStatement->bindValue(':private', $item->isPrivate());
		$this->insertStatement->bindValue(':owner', $item->getOwner()->getId());
		$this->insertStatement->bindValue(':poster', $item->getPoster()->getId());

	}

	/**
	 * @param \popcorn\model\groups\Group $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(':title', $item->getTitle());
		$this->updateStatement->bindValue(':description', $item->getDescription());
		$this->updateStatement->bindValue(':private', $item->isPrivate());
		$this->updateStatement->bindValue(':poster', $item->getPoster()->getId());
		$this->updateStatement->bindValue(':id', $item->getId());

	}

	/**
	 * @param Group $item
	 *
	 * @throws \popcorn\model\exceptions\MysqlException
	 * @return Group
	 */
	protected function prepareItem($item) {
//		$this->groupMembers->save($item->getMembers());

		return parent::prepareItem($item);
	}

	/**
	 * @param Group $item
	 *
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_ALL) {
		$item->setCreateTime(new \DateTime($item->getCreateTime()));
		$item->setEditTime(new \DateTime($item->getEditTime()));

		$item->setPoster(ImageFactory::getImage($item->getPoster()));

		parent::itemCallback($item);

		if ($this->modifier & self::WITH_OWNER) {
			$item->setOwner(UserFactory::getUser($item->getOwner()));
		}

		if ($this->modifier & self::WITH_TAGS) {
			$item->setTags($this->getTags($item->getId()));
		}

		/*
		if ($modifier & self::WITH_ALBUMS) {
			$item->setAlbums($this->getAlbums($item->getId()));
		}
		*/

//		if ($modifier & self::WITH_MEMBERS) {
//			$item->setMembers($this->getMembers($item->getId()));
//		}
	}

	/**
	 * @param \popcorn\model\groups\Group $item
	 */
	protected function onInsert($item) {

	}

	/**
	 * @param Group $item
	 *
	 * @throws \popcorn\model\exceptions\SaveFirstException
	 */
	protected function onSave($item) {
		$this->cleanTagsStatement->bindValue(':id', $item->getId());
		$this->cleanTagsStatement->execute();

		$this->cleanAlbumsStatement->bindValue(':id', $item->getId());
		$this->cleanAlbumsStatement->execute();

		//Удаляем всех привязанных членов
//		$this->cleanMembersStatement->bindValue(':id', $item->getId());
//		$this->cleanMembersStatement->execute();


		if (count($item->getTags()) > 0) {
			$this->insertTagsStatement->bindValue(':groupId', $item->getId());
			$tags = $item->getTags();

			foreach ($tags as $tag) {
				if (is_null($tag->getId())) {
					throw new SaveFirstException();
				}

				if ($tag instanceof Person) {
					$person = $tag;

					$this->insertTagsStatement->bindValue(':type', Tag::PERSON);
					$this->insertTagsStatement->bindValue(':entityId', $person->getId());

				} elseif ($tag instanceof Tag) {
					$this->insertTagsStatement->bindValue(':type', $tag->getType());
					$this->insertTagsStatement->bindValue(':entityId', $tag->getId());
				}

				$this->insertTagsStatement->execute();
			}
		}

		if (count($item->getAlbums()) > 0) {
			$this->insertAlbumsStatement->bindValue(':groupId', $item->getId());
			$albums = $item->getAlbums();
			foreach ($albums as $album) {
				if (is_null($album->getId())) {
					throw new SaveFirstException();
				}
				$this->insertAlbumsStatement->bindValue(':albumId', $album->getId());
				$this->insertAlbumsStatement->execute();
			}
		}


		/*
		if ($item->getMembersCount() > 0) {





			$stmt = $this->prepare("SELECT groupId,userId FROM pn_groups_members WHERE groupId = :groupId");
			$stmt->execute([
				':groupId' => $item->getId()
			]);

			while ($table = $stmt->fetch(\PDO::FETCH_ASSOC)) {



			}


			$stmt = $this->prepare("INSERT INTO pn_groups_members (groupId, userId, joinTime, confirm, request) VALUES (:groupId, :userId, :joinTime, :confirm, :request)");
			$stmt->execute([
				':groupId' => $item->getId(),
				':userId' => $item->getOwner()->getId(),
				':joinTime' => time(),
				':confirm' => 'n',
				':request' => 'n'
			]);

			$members = $item->getMembers();

			foreach ($members as $member) {
				if (is_null($member->getId())) {
					throw new SaveFirstException();
				}

				$this->insertMembersStatement->bindValue(':groupId', $item->getId());
				$this->insertMembersStatement->bindValue(':userId', $member->getId());
				$this->insertMembersStatement->bindValue(':joinTime', $item -);
				$this->insertMembersStatement->bindValue(':confirm', $member->getId());
				$this->insertMembersStatement->bindValue(':request', $member->getId());
				$this->insertMembersStatement->execute();
			}

//			$this->insertMembersStatement = $this->prepare("INSERT INTO pn_groups_members (groupId, userId, joinTime, confirm, request) VALUES (:groupId, :userId, :joinTime, :confirm, :request)");

			$this->updateCountMembersStatement->bindValue(':id', $item->getId());
			$this->updateCountMembersStatement->execute();

		}*/
	}


	private function getMembers($id) {
		$stmt = $this->prepare('SELECT userId FROM pn_groups_members WHERE groupId = :groupId');
		$stmt->execute([':groupId' => $id]);

		$members = [];

		while ($memberId = $stmt->fetch(\PDO::FETCH_COLUMN)) {
			$members[] = UserFactory::getUser($memberId);
		}

		return $members;
	}

	/**
	 * @param $id
	 *
	 * @return \popcorn\model\tags\Tag[]
	 */
	private function getTags($id) {
		$this->getTagsStatement->bindValue(':id', $id);
		$this->getTagsStatement->execute();
		$items = $this->getTagsStatement->fetchAll(\PDO::FETCH_CLASS, 'popcorn\\model\\tags\\Tag');

		return $items;
	}


	/**
	 * @param $id
	 *
	 * @return \popcorn\model\content\Album[]
	 */
	private function getAlbums($id) {
		$this->getAlbumsStatement->bindValue(':id', $id);
		$this->getAlbumsStatement->execute();
		$items = $this->getAlbumsStatement->fetchAll(\PDO::FETCH_CLASS, 'popcorn\\model\\content\\Album');

		return $items;
	}

	public function getNewGroups(array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'orderBy' => [
				'createTime' => 'desc'
			]
		], $options);

		$sql = 'SELECT %s FROM pn_groups WHERE 1=1';

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute();

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'));
	}

}