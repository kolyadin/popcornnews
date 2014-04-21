<?php
namespace popcorn\model\exceptions;


class NotAuthorizedException extends AjaxException {

	public function getAjaxMessage(){
		return 'Необходимо выполнить вход на сайт под своими данными';
	}

}