<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class MeetEntity {

    static function getCommands() {

        return [
			new command\meet\Import(),
			new command\meet\ImportComments(),
        ];

    }

}