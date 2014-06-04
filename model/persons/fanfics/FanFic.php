<?php

namespace popcorn\model\persons\fanfics;

use popcorn\model\Model;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

/**
 * Class FanFic
 * @package popcorn\model\persons\fanfics
 * @table pn_persons_fanfics
 */
class FanFic extends Model {

	const STATUS_ACTIVE = 1;
	const STATUS_NOT_ACTIVE = 0;

	//region Fields

	/**
	 * @var int
	 * @export
	 */
	private $userId;

	/**
	 * @var int
	 * @export
	 */
	private $personId;

	/**
	 * @var \DateTime
	 * @export
	 */
	private $createdAt;

	/**
	 * @var int
	 * @export
	 */
	private $status;

	private $content;

	private $photo;

	private $title;

	private $announce;

	private $views = 0;

	private $comments = 0;

	private $votesUp = 0;

	private $votesDown = 0;

	//endregion

	/**
	 * @return mixed
	 */
	public function getAnnounce() {
		return $this->announce;
	}

	/**
	 * @param mixed $announce
	 */
	public function setAnnounce($announce) {
		$this->announce = $announce;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * @param int $comments
	 */
	public function setComments($comments) {
		$this->comments = $comments;
		$this->changed();
	}

	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param mixed $content
	 */
	public function setContent($content) {
		$this->content = $content;
		$this->changed();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt
	 */
	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getPersonId() {
		return $this->personId;
	}

	/**
	 * @param int $personId
	 */
	public function setPersonId($personId) {
		$this->personId = $personId;
		$this->changed();
	}

	/**
	 * @return \popcorn\model\content\Image
	 */
	public function getPhoto() {
		return $this->photo;
	}

	/**
	 * @param mixed $photo
	 */
	public function setPhoto($photo) {
		$this->photo = $photo;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status) {
		$this->status = $status;
		$this->changed();
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * @param int $userId
	 */
	public function setUserId($userId) {
		$this->userId = $userId;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getViews() {
		return $this->views;
	}

	/**
	 * @param int $views
	 */
	public function setViews($views) {
		$this->views = $views;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getVotesDown() {
		return $this->votesDown;
	}

	/**
	 * @param int $votesDown
	 */
	public function setVotesDown($votesDown) {
		$this->votesDown = $votesDown;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getVotesUp() {
		return $this->votesUp;
	}

	/**
	 * @param int $votesUp
	 */
	public function setVotesUp($votesUp) {
		$this->votesUp = $votesUp;
		$this->changed();
	}






}