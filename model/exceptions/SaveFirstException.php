<?php
/**
 * User: anubis
 * Date: 18.09.13 12:10
 */

namespace popcorn\model\exceptions;


class SaveFirstException extends Exception {

    public function __construct() {
        parent::__construct("You need to save object first");
    }

}