<?php
/**
 * User: anubis
 * Date: 05.09.13 13:56
 */

namespace popcorn\model\exceptions;


class ClassNotFoundException extends Exception {

    public function __construct($class) {
        parent::__construct(sprintf("Class '%s' not found", $class));
    }

}