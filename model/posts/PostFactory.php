<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 12:30
 */

namespace popcorn\model\posts;

use popcorn\lib\mmc\MMC;
use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\NewsTagDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\tags\Tag;

class PostFactory {

	/**
	 * @var NewsPostDataMap
	 */
	private static $dataMap = null;

	public static function savePost(NewsPost $post) {
		self::checkDataMap();
		self::$dataMap->save($post);
		$post->onSave();
	}

	/**
	 * @param int $id
	 */
	public static function removePost($id) {
		self::checkDataMap();
		self::$dataMap->delete($id);
	}

	public static function setDataMap($dataMap) {
		self::$dataMap = $dataMap;
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new NewsPostDataMap());
		}
	}

	public static function resetDataMap() {
		self::$dataMap = new NewsPostDataMap();
	}

	/**
	 * @param NewsPostBuilder $builder
	 * @return \popcorn\model\posts\NewsPost
	 */
	public static function createFromBuilder($builder) {
		$post = $builder->build();
		self::savePost($post);

		return $post;
	}

	/**
	 * @param $postId
	 * @param array $options
	 * @return NewsPost
	 */
	public static function getPost($postId, array $options = []) {

		$options = array_merge([
			'itemCallback' => [
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_ALL,
				'popcorn\\model\\dataMaps\\PersonDataMap'   => PersonDataMap::WITH_NONE
			]
		], $options);

		$newsPostDataMap = new NewsPostDataMap(new DataMapHelper($options['itemCallback']));

		return $newsPostDataMap->findById($postId, $options);
	}


	/**
	 * Посты по дате, новые выше
	 *
	 * @param int $from
	 * @param int $count
	 * @param array $options
	 * @param $totalFound
	 * @return NewsPost[]
	 */
	public static function getPosts(array $options = [], $from = 0, $count = 10, &$totalFound = 0) {

		$options = array_merge([
			'itemCallback' => [
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE ^ NewsPostDataMap::WITH_MAIN_IMAGE ^ NewsPostDataMap::WITH_TAGS,
				'popcorn\\model\\dataMaps\\PersonDataMap'   => PersonDataMap::WITH_NONE
			]
		], $options);

		$newsPostDataMap = new NewsPostDataMap(new DataMapHelper($options['itemCallback']));

		return $newsPostDataMap->findByLimit($options, $from, $count, $totalFound);
	}

	/**
	 * @param $categoryAlias
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param int $totalFound
	 * @return NewsPost[]
	 */
	public static function findByCategory($categoryAlias, array $options = [], $from = 0, $count = -1, &$totalFound = 0) {

		$categoryId = PostCategory::getCategoryIdByAlias($categoryAlias);

		$options = array_merge([
			'itemCallback' => [
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE ^ NewsPostDataMap::WITH_MAIN_IMAGE ^ NewsPostDataMap::WITH_TAGS,
				'popcorn\\model\\dataMaps\\PersonDataMap'   => PersonDataMap::WITH_NONE
			]
		], $options);

		$newsPostDataMap = new NewsPostDataMap(new DataMapHelper($options['itemCallback']));

		return $newsPostDataMap->findByCategory($categoryId, $options, $from, $count, $totalFound);
	}

	/**
	 * @param $tagId
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param $totalFound
	 * @return NewsPost[]
	 */
	public static function findByTag($tagId, array $options = [], $from = 0, $count = -1, &$totalFound = 0) {

		$options = array_merge([
			'itemCallback' => [
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE ^ NewsPostDataMap::WITH_MAIN_IMAGE ^ NewsPostDataMap::WITH_TAGS,
				'popcorn\\model\\dataMaps\\PersonDataMap'   => PersonDataMap::WITH_NONE
			]
		], $options);


		$newsPostDataMap = new NewsPostDataMap(new DataMapHelper($options['itemCallback']));

		return $newsPostDataMap->findByTag($tagId, $options, $from, $count, $totalFound);

	}


	public static function findEarlier(NewsPost $post, array $relationships = []) {

		$dataMapHelper = new DataMapHelper();

		if (count($relationships)) {
			$dataMapHelper->setRelationship($relationships);
		} else {
			$dataMapHelper->setRelationship([
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE
			]);
		}

		$newsPostDataMap = new NewsPostDataMap($dataMapHelper);

		return $newsPostDataMap->findEarlier($post);

	}

	/**
	 * Обновим количество показов у новости
	 *
	 * @param NewsPost $post
	 */
	public static function incrementViews(NewsPost $post) {
		self::checkDataMap();

		self::$dataMap->updateViews($post);
	}

	/**
	 * @param $search
	 * @param int $from
	 * @param int $count
	 *
	 * @return NewsPost[]
	 */
	public static function searchPosts($search, $from = 0, $count = -1) {
		self::checkDataMap();
		$query = " name LIKE '%$search%' OR announce LIKE '%$search%' OR content LIKE '%$search%'";

		return self::$dataMap->findRaw($query, array('createDate' => DataMap::DESC), $from, $count);
	}

	/**
	 * @param $query
	 * @param array $orders
	 * @param int $from
	 * @param int $count
	 *
	 * @return NewsPost[]
	 */
	public static function getPostsRaw($query, $orders = array(), $from = 0, $count = -1) {
		self::checkDataMap();

		return self::$dataMap->findRaw($query, $orders, $from, $count);
	}


	public static function getStopShot($from = 0, $count = 2) {

		$newsPostDataMap = new NewsPostDataMap(new DataMapHelper(['popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE ^ NewsPostDataMap::WITH_IMAGES]));

//		$cacheKey = MMC::genKey($newsPostDataMap->getClass(), __METHOD__, func_get_args());

//		return MMC::getSet($cacheKey, strtotime('+1 day'), ['post'], function () use ($newsPostDataMap, $from, $count) {
			return $newsPostDataMap->findRaw('name like "Стоп-кадр%" and status = ' . NewsPost::STATUS_PUBLISHED, ['createDate' => 'desc'], $from, $count);
//		});


	}

	/**
	 * @param $count
	 *
	 * @return NewsPost[]
	 */
	public static function getTopPosts($count) {
		self::checkDataMap();

		$cacheKey = MMC::genKey(self::$dataMap->getClass(), __METHOD__, func_get_args());

		return MMC::getSet($cacheKey, strtotime('+1 day'), ['post'], function () use ($count) {
			return self::$dataMap->findRaw("status = " . NewsPost::STATUS_PUBLISHED . " AND createDate > " . strtotime("-2 week"),
				array('comments' => DataMap::DESC, 'createDate' => DataMap::DESC),
				0, $count);
		});


	}


	public static function getByTag($tagId) {
		$dataMap = new NewsTagDataMap();

		$posts = $dataMap->findByLink($tagId);

		return $posts;
	}
}