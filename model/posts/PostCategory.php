<?php

namespace popcorn\model\posts;

class PostCategory {

	private $category = [
		1 => [
			'urlName' => 'stars',
			'name'    => 'Звезды'
		],
		2 => [
			'urlName' => 'fashion',
			'name'    => 'Мода'
		],
		3 => [
			'urlName' => 'beauty',
			'name'    => 'Красота'
		],
		4 => [
			'urlName' => 'movie',
			'name'    => 'Кино'
		],
		5 => [
			'urlName' => 'tvshow',
			'name'    => 'ТВ и сериалы'
		],
		6 => [
			'urlName' => 'music',
			'name'    => 'Музыка'
		],
		7 => [
			'urlName' => 'gadget',
			'name'    => 'Гаджеты'
		],
	];

	public function getCategory($categoryId) {

		return $this->category[$categoryId];

	}

	public function getCategories() {
		return $this->category;
	}

}
































