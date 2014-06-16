<?php

namespace popcorn\model\dataMaps\comments;

use popcorn\lib\mmc\MMC;

class KidCommentDataMap extends CommentDataMap {

	/**
	 * @var \PDOStatement
	 */
	private $stmtUpdateKidCommentsCount;

	public function __construct() {
		parent::__construct();
	}

	protected function initStatements() {
		$this->tablePrefix = 'kids';

		parent::initStatements();

		$this->stmtUpdateKidCommentsCount =
			$this->prepare('UPDATE pn_kids SET comments = (SELECT count(*) FROM pn_comments_kids WHERE entityId = :kidId) WHERE id = :kidId LIMIT 1');
	}

	/**
	 * @param \popcorn\model\comments\Comment $comment
	 */
	protected function onInsert($comment) {
		parent::onInsert($comment);

		$this->updateCommentsCount($comment->getEntityId());
		MMC::del(MMC::genKey('kid', $comment->getEntityId(), 'html-comments'));
		MMC::del(MMC::genKey($this->class, 'comment', $comment->getId()));

	}

	protected function onRemove($commentId) {
		$this->updateCommentsCount($commentId);
	}

	private function updateCommentsCount($kidId) {
		$this->stmtUpdateKidCommentsCount->execute([
			':kidId' => $kidId
		]);
	}
}