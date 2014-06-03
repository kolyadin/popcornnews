<?php

namespace popcorn\model\persons\facts;

use popcorn\model\Model;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;

/**
 * Class Fact
 * @package popcorn\model\persons\facts
 * @table pn_persons_facts
 */
class Fact extends Model {

	//region Fields

	/**
	 * @var string
	 * @export
	 */
	private $fact = '';

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
	private $userId;

	/**
	 * @var int
	 * @export
	 */
	private $trustRating = 0;

	/**
	 * @var int
	 * @export
	 */
	private $voteRating = 0;

	//endregion



	public function getFact() {
		return $this->fact;
	}

	public function getPersonId() {
		return $this->personId;
	}

	public function getPerson() {
		return PersonFactory::getPerson($this->personId);
	}

	public function getCreatedAt() {
		return $this->createdAt;
	}

	public function getUserId() {
		return $this->userId;
	}

	public function getUser() {
		return UserFactory::getUser($this->userId);
	}

	public function getTrustRating() {
		return $this->trustRating;
	}

	public function getVoteRating() {
		return $this->voteRating;
	}


	public function setFact($fact) {
		$this->fact = $fact;
		$this->changed();
	}

	public function setPersonId($personId) {
		$this->personId = $personId;
		$this->changed();
	}

	public function setPerson(Person $person) {
		$this->personId = $person->getId();
		$this->changed();
	}

	public function setCreatedAt(\DateTime $createdAt) {
		$this->createdAt = $createdAt;
		$this->changed();
	}

	public function setUserId($userId) {
		$this->userId = $userId;
		$this->changed();
	}

	public function setUser(User $user) {
		$this->userId = $user->getId();
		$this->changed();
	}

	public function setTrustRating($rating) {
		$this->trustRating = $rating;
		$this->changed();
	}

	public function setVoteRating($rating) {
		$this->voteRating = $rating;
		$this->changed();
	}

}