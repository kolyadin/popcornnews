<?php

namespace popcorn\model\dataMaps\comments;

class MeetCommentDataMap extends CommentDataMap {

	/**
	 * @var \PDOStatement
	 */
	private $stmtUpdateMeetCommentsCount;

	public function __construct() {

		parent::__construct();

	}

	protected function initStatements() {
		$this->tablePrefix = 'meetings';

		parent::initStatements();

		$this->stmtUpdateMeetCommentsCount =
			$this->prepare('UPDATE pn_meetings SET comments = (SELECT count(*) FROM pn_comments_meetings WHERE entityId = :meetId) WHERE id = :meetId LIMIT 1');
	}

	/**
	 * @param \popcorn\model\comments\MeetComment $item
	 */
	protected function onInsert($item) {
		parent::onInsert($item);

		$this->updateCommentsCount($item->getMeetId());
	}

	protected function onRemove($meetId) {
		$this->updateCommentsCount($meetId);
	}

	private function updateCommentsCount($meetId) {
		$this->stmtUpdateMeetCommentsCount->execute([
			':meetId' => $meetId
		]);
	}
}