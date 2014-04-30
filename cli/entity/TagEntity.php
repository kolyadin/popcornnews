<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class TagEntity {

	static function getCommands() {

		return array(
			new command\tag\ImportMovies(),
		);

	}

}