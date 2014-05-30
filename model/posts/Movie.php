<?php

namespace popcorn\model\posts;

use popcorn\model\Model;

/**
 * Class Movie
 * @package popcorn\model\posts
 * @table ka_movies
 */
class Movie extends Model {

	private $name;

	private $originalName;

	private $year;

	//region Getters


	public function getName() {
		return $this->name;
	}

	public function getOriginalName() {
		return $this->originalName;
	}

	public function getYear() {
		return $this->year;
	}

	public function isMovie() {
		return true;
	}


	//endregion

	//region Setters


	//endregion

}
































