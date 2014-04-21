<?php
/**
 * User: anubis
 * Date: 09.10.13 18:07
 */

namespace popcorn\model\exceptions;


class NullException extends Exception {

    public function __construct($message = "Null not allowed") {
        parent::__construct($message);
    }
}