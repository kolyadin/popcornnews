<?php
/**
 * User: anubis
 * Date: 15.10.13
 * Time: 1:09
 */

namespace popcorn\model\groups;

use popcorn\model\content\Image;
use popcorn\model\exceptions\SaveFirstException;
use popcorn\model\Model;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

/**
 * Class Album
 * @package popcorn\model\groups
 * @table pn_groups_albums
 */
class Album extends Model {

	//region Fields

	private $groupId;

	private $userId;

	private $createdAt;

	private $editedAt;

	private $title;

	//endregion

	//region Getters


	public function getGroupId() {
		return $this->groupId;
	}

	public function getGroup() {
		return GroupFactory::get($this->getGroupId());
	}

	public function getUserId() {
		return $this->userId;
	}

	public function getUser() {
		return UserFactory::getUser($this->getUserId());
	}

	public function getCreatedAt() {
		return $this->createdAt;
	}

	public function getEditedAt() {
		return $this->editedAt;
	}

	public function getTitle() {
		return $this->title;
	}

	//endregion

	//region Setters

	public function setGroupId($groupId) {
		$this->groupId = $groupId;
	}

	public function setGroup(Group $group) {
		$this->setGroupId($group->getId());
	}

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function setUser(User $user) {
		$this->setUserId($user->getId());
	}

	public function setCreatedAt($time) {
		$this->createdAt = $time;
	}

	public function setEditedAt($time) {
		$this->editedAt = $time;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	//endregion

	public function addPhoto(Image $photo) {
		if(is_null($this->getId())) {
			throw new SaveFirstException;
		}

		if(is_null($photo->getId())) {
			throw new SaveFirstException;
		}




	}
}