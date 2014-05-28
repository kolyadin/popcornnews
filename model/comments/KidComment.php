<?php

namespace popcorn\model\comments;


/**
 * Class KidComment
 */
class KidComment extends Comment {

	/**
	 * @var int
	 * @export
	 */
	public $kidId;

	/**
	 * @return int
	 */
	public function getKidId() {
		return $this->kidId;
	}

	public function getEntityId() {
		return $this->getKidId();
	}

	public function setKidId($id) {
		$this->kidId = $id;
	}

}