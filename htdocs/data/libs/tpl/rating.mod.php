<?php

/**
 * Get users rating class
 */

class vpa_tpl_rating {
	public $scores;
	public $stars;

	public function vpa_tpl_rating() {
		$this->scores = array(
			5000 => array('name' => 'pink', 'class' => 'pinkUserRating'),
			10000 => array('name' => 'silver', 'class' => 'silverUserRating'),
			50000 => array('name' => 'gold', 'class' => 'goldUserRating'),
			100000 => array('name' => 'platinum', 'class' => 'platinumUserRating'),
			200000 => array('name' => 'diamond', 'class' => 'diamondUserRating'),
		);

		$this->stars = array(
			0 => '',
			1 => 'ratingOne',
			2 => 'ratingTwo',
			3 => 'ratingThree',
			4 => 'ratingFour',
			5 => 'ratingFive',
			6 => 'ratingSix',
			7 => 'ratingSeven',
			8 => 'ratingEight',
			9 => 'ratingNine',
			10 => 'ratingTen',
		);
	}

	public function _class($scores) {
		foreach ($this->scores as $rating => $info) {
			$stars_step = $rating / 10;
			if ($scores < $rating) {
				break;
			}
		}
		$indx = ceil($scores / $stars_step);
		$info['stars'] = $this->stars[$indx];
		return $info;
	}

}
