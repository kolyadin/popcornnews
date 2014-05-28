<?php

namespace popcorn\model\comments;


/**
 * Class PhotoArticleComment
 */
class PhotoArticleComment extends Comment {

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