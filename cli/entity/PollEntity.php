<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class PollEntity {

    static function getCommands() {

        return array(
            new command\poll\Import()
        );

    }

}