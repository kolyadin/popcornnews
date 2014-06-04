<?php
namespace popcorn\model\exceptions;


class NotAuthorizedException extends AjaxException {

	public function getAjaxMessage() {
		return 'Необходимо выполнить вход на сайт под своими данными';
	}

	public function display() {
		$this
			->getApp()
			->getSlim()
			->response
			->status(401);

		$this
			->getApp()
			->getTwig()
			->display('/errors/NotAuthorized.twig');
	}
}