<?php
namespace popcorn\model\exceptions;


class RemindWrongEmailException extends Exception {

	private $tpl = [];

	public function __construct(array $tpl = []) {
		$this->tpl = $tpl;
	}

	public function display() {
		$this
			->getApp()
			->getTwig()
			->display('/errors/RemindWrongEmail.twig', $this->tpl);
	}
}