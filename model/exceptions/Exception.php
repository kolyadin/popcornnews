<?php
/**
 * User: anubis
 * Date: 05.09.13 13:51
 */

namespace popcorn\model\exceptions;


use popcorn\app\Application;

class Exception extends \Exception {

	protected $ajaxMessage = '';

	static private $app;

	public function __construct($message = "Generic exception", $code = 0) {
		parent::__construct($message, $code, null);
	}

	/**
	 * @param \popcorn\app\Application $app
	 */
	static public function setApp(Application $app) {
		self::$app = $app;
	}

	/**
	 * @return \popcorn\app\Application
	 */
	static public function getApp() {
		return self::$app;
	}

	public function exitWithJsonException() {
		die(json_encode([
			'status'    => 'error',
			'exception' => [
				'message'  => $this->getAjaxMessage(),
				'code'     => $this->getCode(),
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