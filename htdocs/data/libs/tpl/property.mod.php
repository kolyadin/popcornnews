<?php

class vpa_tpl_property {
	public $scores;
	public $stars;

	public function vpa_tpl_property() {
		$this->stars = array(1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',
			7 => 'seven',
			8 => 'eight',
			9 => 'nine',
			10 => 'ten',
			);
	}

	public function _class($rating) {
		$indx = floor($rating);
		$info = $this->stars;
		$info[$indx] .= ' current';
		return $info;
	}
}
