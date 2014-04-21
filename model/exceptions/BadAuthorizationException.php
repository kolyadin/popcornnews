<?php
namespace popcorn\model\exceptions;


class BadAuthorizationException extends Exception {

	public function __construct($message = "Bad/wrong authorization data provided. Access denied") {
		parent::__construct($message);
	}
}