<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class PersonEntity {

    static function getCommands() {

        return array(
            new command\person\FillTestData(),
            new command\person\RemoveAll(),
            new command\person\Import(),
			new command\person\ImportFans(),
			new command\person\UpdateCounters()
        );

    }

}