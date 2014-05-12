<?php

namespace popcorn\model\posts;

class PostCategory {

	private static $category = [
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

	public static function getCategory($categoryId) {
		return self::$category[$categoryId];
	}

	public static function getCategories() {
		return self::$category;
	}

	public static function getCategoryIdByAlias($alias) {
		foreach (self::getCategories() as $categoryId => $category) {
			if ($category['urlName'] == $alias) {
				return $categoryId;
			}
		}
	}

}
































