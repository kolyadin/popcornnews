<?php

namespace popcorn\lib\twigExtensions;

use Twig_Extension;
use popcorn\lib\RuHelper;

class TwigExtensionRuDateFriendly extends Twig_Extension{

	public function getName(){
		return 'ruDateFriendly';
	}

	public function getFilters(){
		return array(
			new \Twig_SimpleFilter('ruDateFriendly', array($this, 'ruDateFriendly')),
		);

	}

	public function ruDateFriendly($timestamp){

		return RuHelper::ruDateFriendly($timestamp);

	}
	
}