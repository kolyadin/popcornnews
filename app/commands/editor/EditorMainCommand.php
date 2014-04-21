<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 15:05
 */

namespace popcorn\app\commands\editor;


use popcorn\app\Command;

class EditorMainCommand extends Command {

    public function __construct($method) {
        parent::__construct('EditorMain', $method);
    }

}