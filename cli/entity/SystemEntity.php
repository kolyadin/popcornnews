<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class SystemEntity {

    static function getCommands() {

        return [
			new command\system\Deploy(),
			new command\system\gaCron()
        ];

    }

}