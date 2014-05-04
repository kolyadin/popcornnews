<?php

namespace popcorn\model\posts;

class PostCategory {

	private $category = [
		1 => [
			'urlName' => 'stars',
			'name'    => 'звезды'
		],
		2 => [
			'urlName' => 'fashion',
			'name'    => 'мода'
		],
		3 => [
			'urlName' => 'beauty',
			'name'    => 'красота'
		],
		4 => [
			'urlName' => 'movie',
			'name'    => 'кино'
		],
		5 => [
			'urlName' => 'tvshow',
			'name'    => 'тв и сериалы'
		],
		6 => [
			'urlName' => 'music',
			'name'    => 'музыка'
		],
		7 => [
			'urlName' => 'gadget',
			'name'    => 'гаджеты'
		],
	];

	public function getCategory($categoryId) {

		return $this->category[$categoryId];

	}

	public function getCategories() {
		return $this->category;
	}

}
































