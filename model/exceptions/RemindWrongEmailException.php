<?php
namespace popcorn\model\exceptions;


class RemindWrongEmailException extends Exception {

	private $tpl = array();

	public function __construct($message = "Wrong email provided", $tpl = array()) {
		$this->tpl = $tpl;
		parent::__construct($message);
	}

	public function getTpl(){
		return $this->tpl;
	}
}