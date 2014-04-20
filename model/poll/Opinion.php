<?php

namespace popcorn\model\poll;

use popcorn\model\Model;

/**
 * Class Opinion
 * @package popcorn\model\voting
 * @table pn_opinions
 */
class Opinion extends Model {

	/**
	 * @var string
	 * @export readonly
	 */
	private $title = '';

	/**
	 * @var int
	 * @export readonly
	 */
	private $pollId;

	private $votes;

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
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param int $pollId
	 */
	public function setPollId($pollId) {
		$this->pollId = $pollId;
	}

	/**
	 * @return int
	 */
	public function getPollId() {
		return $this->pollId;
	}

	public function setVotes($votes) {
		$this->votes = $votes;
	}

	public function getVotes() {
		return $this->votes;
	}

}