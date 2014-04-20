<?php

namespace popcorn\model\exceptions\ajax;

use \popcorn\model\exceptions\AjaxException;

class AlreadyFriendsException extends AjaxException {

	public function getAjaxMessage(){
		return 'Вы уже друзья с этим пользователем';
	}

}