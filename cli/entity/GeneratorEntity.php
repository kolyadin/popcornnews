<?php
/**
 * User: anubis
 * Date: 21.10.13
 * Time: 15:48
 */

namespace popcorn\cli\entity;


use popcorn\cli\command\generator\Generate;

class GeneratorEntity {

    public static function getCommands() {
        return array(
            new Generate()
        );
    }

}