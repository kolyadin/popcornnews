<?php

namespace popcorn\model\dataMaps\comments;

use popcorn\lib\mmc\MMC;

class GuestBookCommentDataMap extends CommentDataMap {


	protected function initStatements() {
		$this->tablePrefix = 'guestbook';

		parent::initStatements();
	}

}