<?php

namespace popcorn\lib;

use popcorn\app\Application;

abstract class GenericHelper {

	/**
	 * @var \popcorn\app\Application
	 */
	private static $app;

	public static function setApp(Application $app){
		self::$app = $app;
	}

	public static function getApp(){
		return self::$app;
	}

}