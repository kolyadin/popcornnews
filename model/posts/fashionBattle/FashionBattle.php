<?php

namespace popcorn\model\posts\fashionBattle;

use popcorn\model\Model;
use popcorn\model\persons\Person;

/**
 * Class FashionBattle
 * @package popcorn\model\posts\fashionBattle
 * @table pn_news_fashion_battle
 */
class FashionBattle extends Model {


	//region Fields

	/**
	 * @var int
	 * @export
	 */
	private $newsId;

	/**
	 * @var Person
	 */
	private $firstPerson;

	/**
	 * @var Person
	 */
	private $secondPerson;


	//endregion

	function __construct() {

	}

	//region Getters

	/**
	 * @return int
	 */
	public function getNewsId(){
		return $this->newsId;
	}

	/**
	 * @return Person
	 */
	public function getFirstPerson(){
		return $this->firstPerson;
	}

	/**
	 * @return Person
	 */
	public function getSecondPerson(){
		return $this->secondPerson;
	}

	//endregion

	//region Setters

	public function setNewsId($id){
		$this->newsId = $id;
	}

	public function setFirstPerson($personId){
		$this->firstPerson = $personId;
	}

	public function setSecondPerson($personId){
		$this->secondPerson = $personId;
	}

	//endregion

}
































