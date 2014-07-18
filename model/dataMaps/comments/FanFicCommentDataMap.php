<?php

namespace popcorn\model\dataMaps\comments;

class FanFicCommentDataMap extends CommentDataMap {

	/**
	 * @var \PDOStatement
	 */
	private $stmtUpdateFanFicCommentsCount;

	public function __construct() {

		parent::__construct();

	}

	protected function initStatements() {
		$this->tablePrefix = 'fanfics';

		parent::initStatements();

		$this->stmtUpdateFanFicCommentsCount =
			$this->prepare('UPDATE pn_persons_fanfics SET comments = (SELECT count(*) FROM pn_comments_fanfics WHERE entityId = :fanficId) WHERE id = :fanficId LIMIT 1');
	}

	/**
	 * @param \popcorn\model\comments\FanFicComment $item
	 */
	protected function onInsert($item) {
		parent::onInsert($item);

		$this->updateCommentsCount($item->getFanFicId());
	}

	protected function onRemove($fanficId) {
		$this->updateCommentsCount($fanficId);
	}

	private function updateCommentsCount($fanficId) {
		$this->stmtUpdateFanFicCommentsCount->execute([
			':fanficId' => $fanficId
		]);
	}
}