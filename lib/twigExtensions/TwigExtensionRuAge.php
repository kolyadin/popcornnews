<?php

namespace popcorn\lib\twigExtensions;

use Twig_Extension;
use popcorn\lib\RuHelper;

class TwigExtensionRuAge extends Twig_Extension{

	public function getName(){
		return 'ruAge';
	}

	public function getFilters(){
		return array(
			new \Twig_SimpleFilter('ruAge', array($this, 'ruAge')),
		);

	}

	public function ruAge($timestamp){

		return RuHelper::ruAge($timestamp);

	}

}