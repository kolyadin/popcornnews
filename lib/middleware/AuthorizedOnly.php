<?php

namespace popcorn\lib;

use popcorn\model\system\users\UserFactory;
use Slim\Middleware;

class AuthorizedOnly extends Middleware {

	public function call() {
		if (!UserFactory::getCurrentUser()->getId()) {
			$this->app->notFound();
		}
	}
}