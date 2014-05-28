<?php

namespace popcorn\model\dataMaps\comments;

class PhotoArticleCommentDataMap extends CommentDataMap {

	/**
	 * @var \PDOStatement
	 */
	private $stmtUpdatePostCommentsCount;

	public function __construct() {
		parent::__construct();

		$this->class = "popcorn\\model\\comments\\PhotoArticleComment";
	}

	protected function initStatements() {
		$this->tablePrefix = 'photoarticles';

		parent::initStatements();

		$this->stmtUpdatePostCommentsCount =
			$this->prepare('UPDATE pn_photoarticles SET comments = (SELECT count(*) FROM pn_comments_photoarticles WHERE entityId = :postId) WHERE id = :postId LIMIT 1');
	}

	/**
	 * @param \popcorn\model\comments\PhotoArticleComment $item
	 */
	protected function onInsert($item) {
		parent::onInsert($item);

		$this->updateCommentsCount($item->getPostId());
	}

	protected function onRemove($postId) {
		$this->updateCommentsCount($postId);
	}

	private function updateCommentsCount($postId) {
		$this->stmtUpdatePostCommentsCount->execute([
			':postId' => $postId
		]);
	}
}