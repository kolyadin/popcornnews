<?php
namespace popcorn\model\exceptions;


class BadAuthorizationException extends Exception {

	public function __construct($message = "Bad/wrong authorization data provided. Access denied") {
		parent::__construct($message);
	}

	public function display() {
		$this
			->getApp()
			->getSlim()
			->response
			->status(401);

		$this
			->getApp()
			->getTwig()
			->display('/errors/BadAuthorization.twig');
	}
}