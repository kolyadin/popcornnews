<?php

namespace popcorn\model\groups;

use popcorn\model\content\Album;
use popcorn\model\exceptions\SaveFirstException;
use popcorn\model\Model;
use popcorn\model\system\users\User;
use popcorn\model\tags\Tag;

/**
 * Class Group
 * @package \popcorn\model\groups
 */
class Group extends Model {

//region Fields

	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var \DateTime
	 */
	private $createdAt;
	/**
	 * @var \DateTime
	 */
	private $editedAt;
	/**
	 * @var bool
	 */
	private $private;
	/**
	 * @var \popcorn\model\system\users\User
	 */
	private $owner;
	/**
	 * @var \popcorn\model\system\users\User[]
	 */
	private $moderators = [];
	/**
	 * @var \popcorn\model\content\Image
	 */
	private $poster;
	/**
	 * @var \popcorn\model\content\Album[]
	 */
	private $albums = [];
	/**
	 * @var \popcorn\model\tags\Tag[]
	 */
	private $tags = [];
	/**
	 * @var \popcorn\model\talks\Talk[]
	 */
	private $talks = [];

	/**
	 * @var \popcorn\model\groups\GroupMembers
	 */
	private $members;

	/**
	 * @var int
	 */
	private $membersCount;

//endregion

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->changed();
	}

	/**
	 * @return string
	 */
	public function getDescription($length = null) {

		if ($length){
			$description = $this->description;

			$output = wordwrap(strip_tags($description), $length);
			$lines = explode("\n", $output);
			$output = $lines[0];

			$output = rtrim($output, '.') . '.';

			if (count($lines) > 1) {
				$output = $output . rtrim($output, '.') . '...';
			}

			return $output;
		}else{
			return $this->description;
		}



	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
		$this->changed();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}

	/**
	 * @param $createdAt
	 *
	 */
	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getEditedAt() {
		return $this->editedAt;
	}

	/**
	 * @param \DateTime $editTime
	 */
	public function setEditedAt($editTime) {
		$this->editedAt = $editTime;
	}

	/**
	 * @return bool
	 */
	public function isPrivate() {
		return $this->private;
	}

	/**
	 * @param bool $private
	 */
	public function setPrivate($private) {
		$this->private = $private;
		$this->changed();
	}

	/**
	 * @return \popcorn\model\system\users\User
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @param \popcorn\model\system\users\User $owner
	 */
	public function setOwner($owner) {
		$this->owner = $owner;
	}

	/**
	 * @return \popcorn\model\system\users\User[]
	 */
	public function getModerators() {
		return $this->moderators;
	}

	/**
	 * @return \popcorn\model\content\Image
	 */
	public function getPoster() {
		return $this->poster;
	}

	/**
	 * @param \popcorn\model\content\Image $poster
	 */
	public function setPoster($poster) {
		$this->poster = $poster;
		$this->changed();
	}

	/**
	 * @return \popcorn\model\tags\Tag[]
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * @return \popcorn\model\talks\Talk[]
	 */
	public function getTalks() {
		return $this->talks;
	}

	/**
	 * @return \popcorn\model\groups\GroupMembers
	 */
	public function getMembers() {
		return $this->members;
	}

	/**
	 * @param \popcorn\model\groups\GroupMembers $groupMembers
	 */
	public function setMembers($groupMembers) {
		$this->members = $groupMembers;
	}

	/**
	 * @param \popcorn\model\system\users\User[] $members
	 * @throws \RuntimeException
	 */
	public function setMembers2($members) {
		if (!is_null($this->getId()) && !empty($this->members)) {
			throw new \RuntimeException('Use addMember');
		}
		$this->members = $members;
	}

	/**
	 * @param \popcorn\model\system\users\User $user
	 *
	 * @throws \InvalidArgumentException
	 * @throws \popcorn\model\exceptions\SaveFirstException
	 */
	public function addMember(User $user) {

//		$this->setMembersCount($this->getMembersCount() + 1);

		$this->members[] = $user;
	}


	/**
	 * @return \popcorn\model\content\Album[]
	 */
	public function getAlbums() {
		return $this->albums;
	}

	/**
	 * @param \popcorn\model\content\Album[] $albums
	 * @throws \RuntimeException
	 */
	public function setAlbums($albums) {
		if (!is_null($this->getId()) && !empty($this->albums)) {
			throw new \RuntimeException('Use addAlbum');
		}
		$this->albums = $albums;
	}

	/**
	 * @param Album $album
	 * @throws \InvalidArgumentException
	 * @throws \popcorn\model\exceptions\SaveFirstException
	 */
	public function addAlbum(Album $album) {

		if (is_null($album->getId())) {
			throw new SaveFirstException();
		}
		if (array_search($album, $this->albums) !== false) {
			throw new \InvalidArgumentException('Album exists');
		}
		$this->albums[] = $album;

	}

	/**
	 * @param \popcorn\model\tags\Tag[] $tags
	 *
	 * @throws \RuntimeException
	 */
	public function setTags($tags) {
		if (!is_null($this->getId()) && !empty($this->tags)) {
			throw new \RuntimeException('Use addTag');
		}
		$this->tags = $tags;
	}

	/**
	 * @param popcorn\\model\\persons\\Person | popcorn\\model\\tags\\Tag | popcorn\\model\\posts\\Movie $entity
	 */
	public function addTag($tag) {
		$this->tags[] = $tag;
		$this->changed();
	}



	/**
	 * @return int
	 */
	public function getMembersCount() {
		return $this->membersCount;
	}

	/**
	 * @param int $count
	 */
	public function setMembersCount($count) {
		$this->membersCount = $count;
	}

}