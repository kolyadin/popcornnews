<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class CommunityEntity {

    static public function getCommands() {

        return [
            new command\community\UpdateCounters()
        ];

    }

}