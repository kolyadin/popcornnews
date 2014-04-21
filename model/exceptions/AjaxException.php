<?php

namespace popcorn\model\exceptions;

abstract class AjaxException extends Exception {

	public function exitWithJsonException(){
		die(json_encode([
			'status'    => 'error',
			'exception' => [
				'message'  => $this->getAjaxMessage(),
				'code'     => $this->getCode(),
				'instance' => get_class($this)
			]
		]));
	}

}