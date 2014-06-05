<?php

namespace popcorn\model\comments;


/**
 * Class FanFicComment
 */
class FanFicComment extends Comment {

	/**
	 * @var int
	 * @export
	 */
	public $fanficId;

	/**
	 * @return int
	 */
	public function getFanFicId() {
		return $this->fanficId;
	}

	public function getEntityId() {
		return $this->getFanFicId();
	}

	public function setFanFicId($id) {
		$this->fanficId = $id;
	}

}