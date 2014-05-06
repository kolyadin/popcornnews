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
	 * @var string
	 */
	private $firstOption;

	/**
	 * @var string
	 */
	private $secondOption;


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
	public function getFirstOption(){
		return $this->firstOption;
	}

	/**
	 * @return Person
	 */
	public function getSecondOption(){
		return $this->secondOption;
	}

	//endregion

	//region Setters

	public function setNewsId($id){
		$this->newsId = $id;
	}

	public function setFirstOption($option){
		$this->firstOption = $option;
	}

	public function setSecondOption($option){
		$this->secondOption = $option;
	}

	//endregion

}
































