<?php
namespace popcorn\model\exceptions;


class CommonErrorException extends Exception {

	public function display() {
		$this
			->getApp()
			->getSlim()
			->response
			->status(500);

		$this
			->getApp()
			->getTwig()
			->display('/errors/CommonError.twig');
	}
}