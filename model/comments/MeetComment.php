<?php

namespace popcorn\model\comments;


/**
 * Class MeetComment
 */
class MeetComment extends Comment {

	/**
	 * @var int
	 * @export
	 */
	public $meetId;

	/**
	 * @return int
	 */
	public function getMeetId() {
		return $this->meetId;
	}

	public function getEntityId() {
		return $this->getMeetId();
	}

	public function setMeetId($id) {
		$this->meetId = $id;
	}

}