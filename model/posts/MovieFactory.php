<?php

namespace popcorn\model\posts;

use popcorn\model\posts\MovieDataMap;

class MovieFactory {

	/**
	 * @var MovieDataMap
	 */
	private static $dataMap = null;

	/**
	 * @param $movieId
	 *
	 * @return Movie
	 */
	public static function getMovie($movieId) {
		self::checkDataMap();

		return self::$dataMap->findById($movieId);
	}


	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new MovieDataMap());
		}
	}

}