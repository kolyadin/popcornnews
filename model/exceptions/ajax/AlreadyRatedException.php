<?php

namespace popcorn\model\exceptions\ajax;

use \popcorn\model\exceptions\AjaxException;

class AlreadyRatedException extends AjaxException {

	public function getAjaxMessage() {
		return 'Вы уже голосовали';
	}

}