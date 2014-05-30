<?php

namespace popcorn\lib\twigExtensions;

use Twig_Extension;
use popcorn\lib\RuHelper;

class TwigExtensionRuNumber extends \Twig_Extension {

	public function getName() {
		return 'ruNumber';
	}

	public function getFilters() {
		return array(
			new \Twig_SimpleFilter('ruNumber', array($this, 'ruNumber')),
		);

	}

	public function ruNumber($number, $ru) {
		return RuHelper::ruNumber($number, $ru);
	}
}