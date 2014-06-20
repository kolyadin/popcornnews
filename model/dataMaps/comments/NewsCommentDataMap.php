<?php

namespace popcorn\model\dataMaps\comments;

class NewsCommentDataMap extends CommentDataMap {

	/**
	 * @var \PDOStatement
	 */
	private $stmtUpdatePostCommentsCount;

	public function __construct() {
		parent::__construct();
	}

	protected function initStatements() {
		$this->tablePrefix = 'news';

		parent::initStatements();

		$this->stmtUpdatePostCommentsCount =
			$this->prepare('UPDATE pn_news SET comments = (SELECT count(*) FROM pn_comments_news WHERE entityId = :postId) WHERE id = :postId LIMIT 1');
	}

	protected function onInsert($item) {
		parent::onInsert($item);

		$this->updateCommentsCount($item->getEntityId());
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