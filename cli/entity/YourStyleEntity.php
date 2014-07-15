<?php

/*
 * User: kirill.mazurik
 * Date: 01.07.2014 10:00
 */

namespace popcorn\cli\entity;

use popcorn\cli\command;

class YourStyleEntity {

    static function getCommands() {

        return [
			new command\yourstyle\Import(),
        ];

    }

}
?>