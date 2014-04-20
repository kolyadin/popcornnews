<?php
/**
 * User: anubis
 * Date: 15.08.13 12:18
 */

namespace popcorn\app\commands\editor;


use popcorn\app\Command;

class LoginCommand extends Command {

    public function __construct($method) {
        parent::__construct('Login', $method);
    }

}