<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class UserEntity{

	static function getCommands(){

		return array(
			new command\user\Import(),
			new command\user\RemoveAll(),
			new command\user\RemoveOne(),
			new command\user\TestFriends(),
			new command\user\ImportMessages()
		);

	}

}