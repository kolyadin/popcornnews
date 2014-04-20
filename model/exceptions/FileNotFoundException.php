<?php
/**
 * User: anubis
 * Date: 14.10.13
 * Time: 22:41
 */

namespace popcorn\model\exceptions;


class FileNotFoundException extends Exception {

    public function __construct($file = '') {
        parent::__construct("File not found: ".$file);
    }

}