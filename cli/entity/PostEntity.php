<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class PostEntity {

    static function getCommands() {

        return array(
            new command\post\RemoveAll(),
            new command\post\Import(),
			new command\post\ImportComments()
        );

    }

}