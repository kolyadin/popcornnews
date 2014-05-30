<?php

namespace popcorn\lib;

use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\system\users\UserFactory;

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
		if (UserFactory::getCurrentUser()->getId() > 0) {
			return true;
		}

		self::getApp()->getSlim()->error(new NotAuthorizedException());
	}
}