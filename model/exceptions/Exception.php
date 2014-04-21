<?php
/**
 * User: anubis
 * Date: 05.09.13 13:51
 */

namespace popcorn\model\exceptions;


class Exception extends \Exception {

	protected $ajaxMessage = '';

	public function __construct($message = "Generic exception", $code = 0) {
		parent::__construct($message, $code, null);
	}

	public function exitWithJsonException() {
		die(json_encode([
			'status' => 'error',
			'exception' => [
				'message' => $this->getAjaxMessage(),
				'code' => $this->getCode(),
				'instance' => get_class($this)
			]
		]));
	}

	public function setAjaxMessage($message) {
		$this->ajaxMessage = $message;
	}

	public function getAjaxMessage() {
		return $this->ajaxMessage;
	}


}