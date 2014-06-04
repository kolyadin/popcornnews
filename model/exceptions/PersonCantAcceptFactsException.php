<?php
namespace popcorn\model\exceptions;


use popcorn\model\persons\Person;

class PersonCantAcceptFactsException extends Exception {

	/**
	 * @var \popcorn\model\persons\Person
	 */
	private $person;

	public function __construct(Person $person) {
		$this->person = $person;
	}

	public function display() {

		$this
			->getApp()
			->getTwig()
			->display('/person/facts/PersonCantAcceptFacts.twig',[
				'person' => $this->person
			]);
	}

}