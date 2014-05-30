<?php

namespace popcorn\lib;

class Middleware {

	/** @var \popcorn\app\Popcorn $app */
	static private $app;

	static public function setApp($app) {
		self::$app = $app;
	}

	static public function getApp() {
		return self::$app;
	}

	static public function authorizationNeeded() {
		return true;
	}
}