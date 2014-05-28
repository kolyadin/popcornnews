<?php

namespace popcorn\model\dataMaps\comments;

class KidCommentDataMap extends CommentDataMap {

	/**
	 * @var \PDOStatement
	 */
	private $stmtUpdateKidCommentsCount;

	public function __construct() {
		parent::__construct();

		$this->class = "popcorn\\model\\comments\\KidComment";
	}

	protected function initStatements() {
		$this->tablePrefix = 'kids';

		parent::initStatements();

		$this->stmtUpdateKidCommentsCount =
			$this->prepare('UPDATE pn_kids SET comments = (SELECT count(*) FROM pn_comments_kids WHERE entityId = :kidId) WHERE id = :kidId LIMIT 1');
	}

	/**
	 * @param \popcorn\model\comments\KidComment $item
	 */
	protected function onInsert($item) {
		parent::onInsert($item);

		$this->updateCommentsCount($item->getKidId());
	}

	protected function onRemove($kidId) {
		$this->updateCommentsCount($kidId);
	}

	private function updateCommentsCount($kidId) {
		$this->stmtUpdateKidCommentsCount->execute([
			':kidId' => $kidId
		]);
	}
}