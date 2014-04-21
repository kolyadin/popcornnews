<?php
/**
 * User: anubis
 * Date: 17.10.13 11:42
 */

namespace popcorn\model;


interface IBuilder {

    public static function create();
    public function build();

}