<?php

namespace popcorn\model\exceptions\ajax;

use \popcorn\model\exceptions\AjaxException;

class SameUserAddFriendException extends AjaxException {

	public function getAjaxMessage(){
		return 'Нельзя добавить себя же в друзья';
	}

}