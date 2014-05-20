<?php

namespace popcorn\model\posts\photoArticle;

use popcorn\model\content\Image;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\persons\Person;
use popcorn\model\posts\photoArticle\PhotoArticleDataMap;

class PhotoArticleFactory {

	/**
	 * @var PhotoArticleDataMap
	 */
	private static $dataMap = null;

	public static function savePhotoArticle(PhotoArticlePost $post) {
		self::checkDataMap();
		self::$dataMap->save($post);
		$post->onSave();
	}

	/**
	 * @param int $id
	 */
	public static function removePhotoArticle($id) {
		self::checkDataMap();
		self::$dataMap->delete($id);
	}

	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new PhotoArticleDataMap());
		}
	}

	public static function resetDataMap() {
		self::$dataMap = new PhotoArticleDataMap();
	}

	/**
	 * @param $postId
	 * @param array $options
	 * @return PhotoArticlePost
	 */
	public static function getPhotoArticle($postId, array $options = []) {

		$options = array_merge([
			'itemCallback' => [
				'popcorn\\model\\posts\\photoArticle\\PhotoArticleDataMap' => PhotoArticleDataMap::WITH_ALL,
				'popcorn\\model\\dataMaps\\PersonDataMap'                  => PersonDataMap::WITH_NONE
			]
		], $options);

		$dataMap = new PhotoArticleDataMap(new DataMapHelper($options['itemCallback']));

		return $dataMap->findById($postId, $options);
	}


	/**
	 * Посты по дате, новые выше
	 *
	 * @param int $from
	 * @param int $count
	 * @param array $options
	 * @param $totalFound
	 * @return PhotoArticlePost[]
	 */
	public static function getPhotoArticles(array $options = [], $from = 0, $count = 10, &$totalFound = 0) {

		$options = array_merge([
			'itemCallback' => [
				'popcorn\\model\\dataMaps\\PhotoArticlePostDataMap' => PhotoArticleDataMap::WITH_NONE,
				'popcorn\\model\\dataMaps\\PersonDataMap'           => PersonDataMap::WITH_NONE
			]
		], $options);

		$dataMap = new PhotoArticleDataMap(new DataMapHelper($options['itemCallback']));

		return $dataMap->findByLimit($options, $from, $count, $totalFound);
	}


	/**
	 * Обновим количество показов у фото-статьи
	 *
	 * @param PhotoArticlePost $post
	 */
	public static function incrementViews(PhotoArticlePost $post) {
		self::checkDataMap();

		self::$dataMap->updateViews($post);
	}

	public static function attachPersonToImage(Image $image, Person $person) {
		self::checkDataMap();

		self::$dataMap->attachPersonToImage($image, $person);
	}

	public static function clearImageFromPersons(Image $image) {
		self::checkDataMap();

		self::$dataMap->clearImageFromPersons($image);
	}

}