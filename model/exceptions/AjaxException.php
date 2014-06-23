<?php

namespace popcorn\model\exceptions;

abstract class AjaxException extends Exception {

	public function exitWithJsonException($status = 'error') {
		die(json_encode([
			'status'    => $status,
			'exception' => [
				'message'  => $this->getAjaxMessage(),
				'code'     => $this->getCode(),
				'instance' => get_class($this)
			]
		]));
	}

}