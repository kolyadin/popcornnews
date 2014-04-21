<?php

namespace popcorn\model\exceptions\ajax;

use \popcorn\model\exceptions\AjaxException;

class UserAlreadyInBlackListException extends AjaxException {

	public function getAjaxMessage(){
		return 'Пользователь уже находится в черном списке';
	}

}