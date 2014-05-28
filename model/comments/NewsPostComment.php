<?php

namespace popcorn\model\comments;


/**
 * Class NewsPostComment
 */
class NewsPostComment extends Comment {

	/**
	 * @var int
	 * @export
	 */
	public $postId;

	/**
	 * @return int
	 */
	public function getPostId() {
		return $this->postId;
	}

	public function getEntityId() {
		return $this->getPostId();
	}


	public function setPostId($id) {
		$this->postId = $id;
	}

}