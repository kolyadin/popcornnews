<?php

namespace popcorn\lib\twigExtensions;

use Twig_Extension;
use popcorn\lib\RuHelper;

class TwigExtensionRuDate extends Twig_Extension{

	public function getName(){
		return 'ruDate';
	}

	public function getFilters(){
		return array(
			new \Twig_SimpleFilter('ruDate', array($this, 'ruDate')),
		);

	}

	public function ruDate($timestamp, $format){

		return RuHelper::ruDate($format, $timestamp);

	}

}