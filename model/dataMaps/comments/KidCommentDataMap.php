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
		MMC::del(MMC::genKey('kid', $item->getKidId(), 'html-comments'));
		MMC::del(MMC::genKey($this->class, 'comment', $item->getId()));

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