<?php
/**
 * User: anubis
 * Date: 16.09.13 15:10
 */

namespace popcorn\app\commands;


use popcorn\app\Command;

class SiteCommand extends Command {

    public function __construct($method) {
        parent::__construct('SiteMain', $method);
    }

}