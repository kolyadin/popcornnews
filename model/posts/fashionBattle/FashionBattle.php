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


	/**
	 * @var int
	 */
	private $firstOptionVotes = 0;

	/**
	 * @var int
	 */
	private $secondOptionVotes = 0;

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
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getFirstOptionVotes() {
		return $this->firstOptionVotes;
	}

	/**
	 * @param int $firstOptionVotes
	 */
	public function setFirstOptionVotes($firstOptionVotes) {
		$this->firstOptionVotes = $firstOptionVotes;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getSecondOptionVotes() {
		return $this->secondOptionVotes;
	}

	/**
	 * @param int $secondOptionVotes
	 */
	public function setSecondOptionVotes($secondOptionVotes) {
		$this->secondOptionVotes = $secondOptionVotes;
		$this->changed();
	}

	/**
	 * @param $option
	 */
	public function setSecondOption($option){
		$this->secondOption = $option;
		$this->changed();
	}

	/**
	 * @return int
	 */
	public function getTotalVotes(){
		return $this->firstOptionVotes + $this->secondOptionVotes;
	}

	//endregion

}
































