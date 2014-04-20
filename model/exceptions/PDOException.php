<?php
/**
 * User: anubis
 * Date: 19.09.13 14:24
 */

namespace popcorn\model\exceptions;


class PDOException extends Exception {

    public function __construct(\PDOStatement $st) {
        parent::__construct(print_r($st->errorInfo(), true));
    }

}