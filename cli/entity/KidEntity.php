<?php

namespace popcorn\cli\entity;

use popcorn\cli\command;

class KidEntity {

    static public function getCommands() {

        return [
            new command\kid\Import(),
			new command\kid\ImportComments(),
			new command\kid\RemoveAllComments()
        ];

    }

}