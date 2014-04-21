<?php

namespace popcorn\model\voting;

/**
 * Class UpDownVoting
 * @package popcorn\model\voting
 * @table pn_voting_up_down
 */
class UpDownVoting {

	const Up = 'up';
	const Down = 'down';

	private $checksum;

	private $votedAt;

	private $entity;

	private $entityId;

	private $vote;

	public function __construct() {

		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];

		$this->checksum = md5(implode('', [$ip, $browser]));

		$this->setVotedAt(new \DateTime());
	}

	public function getChecksum() {
		return $this->checksum;
	}

	/**
	 * @param \DateTime $votedAt
	 */
	public function setVotedAt($votedAt) {
		$this->votedAt = $votedAt;
	}

	/**
	 * @return \Datetime
	 */
	public function getVotedAt() {
		return $this->votedAt;
	}

	public function setEntity($entity) {
		$this->entity = $entity;
	}

	public function getEntity() {
		return $this->entity;
	}

	public function setEntityId($id) {
		$this->entityId = $id;
	}

	public function getEntityId() {
		return $this->entityId;
	}

	public function setVote($vote) {
		$this->vote = $vote;
	}

	public function getVote() {
		return $this->vote;
	}

}