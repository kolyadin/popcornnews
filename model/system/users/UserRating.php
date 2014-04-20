<?php
namespace popcorn\model\system\users;


class UserRating {

	private $points = 0;

	private $ranks = array(
		'pink'     => array(0,5000),
		'silver'   => array(5001,10000),
		'gold'     => array(10001,50000),
		'platinum' => array(50001,100000),
		'diamond'  => array(100001,200000),
		'emerald'  => array(200001,300000),
		'ruby'     => array(300001,500000),
		'sapphire' => array(500001,1000000)
	);

	public function __construct($points){
		$this->points = $points;
	}

	public function getPoints(){
		return $this->points;
	}

	public function getRank(){
		$points = $this->getPoints();

		foreach ($this->ranks as $rankName => $rank){
			if ($points >= $rank[0] && $points <= $rank[1]){
				return $rankName;
			}
		}
	}

	public function getCssClassRating(){

		$rank = $this->ranks[$this->getRank()];

		$percent = ceil(
			($this->getPoints() - $rank[0]) * 100
				/
			($rank[1] - $rank[0])
		) / 20;


		if ($percent < 0.5) $percent = 0.5;
		if ($percent > 5)   $percent = 5;

		if ($percent >= 0.5 && $percent <= 1){
			return 'rating-05';
		}elseif ($percent > 1 && $percent <= 1.5){
			return 'rating-1';
		}elseif ($percent > 1.5 && $percent <= 2){
			return 'rating-15';
		}elseif ($percent > 2 && $percent <= 2.5){
			return 'rating-2';
		}elseif ($percent > 2.5 && $percent <= 3){
			return 'rating-25';
		}elseif ($percent > 3 && $percent <= 3.5){
			return 'rating-3';
		}elseif ($percent > 3.5 && $percent <= 4){
			return 'rating-35';
		}elseif ($percent > 4 && $percent <= 4.5){
			return 'rating-4';
		}elseif ($percent > 4.5 && $percent <= 5){
			return 'rating-45';
		}elseif ($percent == 5){
			return 'rating-5';
		}
	}

	public function getPersents(){

		$rank = $this->ranks[$this->getRank()];

		$percent = ceil(($this->getPoints() - $rank[0]) * 100 / ($rank[1] - $rank[0]));

		return $percent;

	}

}


































