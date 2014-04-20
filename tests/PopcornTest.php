<?php
/**
 * User: anubis
 * Date: 19.10.13
 * Time: 1:48
 */

namespace popcorn\tests\model;

abstract class PopcornTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        cleanUp();
    }

}
