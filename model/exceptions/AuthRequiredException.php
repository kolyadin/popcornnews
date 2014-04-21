<?php
/**
 * User: anubis
 * Date: 01.10.13 13:42
 */

namespace popcorn\model\exceptions;


class AuthRequiredException extends Exception {

    public function __construct() {
        parent::__construct("Guest not allowed");
    }

}