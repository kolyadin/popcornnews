<?php

namespace popcorn\model\exceptions\ajax;

use \popcorn\model\exceptions\AjaxException;

class VotingNotAllowException extends AjaxException {

	public function getAjaxMessage(){
		return 'Вы уже голосовали сегодня';
	}

}