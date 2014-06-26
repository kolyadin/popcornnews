<?php
/**
 * User: anubis
 * Date: 14.08.13
 * Time: 0:42
 */

namespace popcorn\app\controllers;

use popcorn\app\Application;
use popcorn\app\Popcorn;

abstract class GenericController {

    /**
     * @var \popcorn\app\Application
     */
    private static $app;

	public static function setApp(Application $app){
		self::$app = $app;
	}


	/**
	 * @return Popcorn
	 */
	protected function getApp(){
		return self::$app;
	}

    /**
     * @return \Slim\Slim
     */
    protected final function getSlim() {
        return self::getApp()->getSlim();
    }

	protected final function getTwig(){
		return self::getApp()->getTwig();
	}

    /**
     * @return \Slim\Http\Request
     */
    protected final function getRequest() {
        return self::getApp()->getSlim()->request;
    }

}