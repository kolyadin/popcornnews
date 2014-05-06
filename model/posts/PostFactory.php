<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 12:30
 */

namespace popcorn\model\posts;

use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\NewsTagDataMap;

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
	 * @param $postId
	 *
	 * @return NewsPost
	 */
	public static function getPost($postId) {
		self::checkDataMap();

		return self::$dataMap->findById($postId);
	}

	/**
	 * Посты по дате, новые выше
	 * @param int $from
	 * @param int $count
	 *
	 * @return NewsPost[]
	 */
	public static function getPosts($from = 0, $count = 10) {
		self::checkDataMap();

		return self::$dataMap->findByDate($from, $count);
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

	public static function incrementViews(NewsPost $post) {
		self::checkDataMap();

		$stmt = PDOHelper::getPDO()->prepare('UPDATE pn_news SET views = views+1 WHERE id = :newsId');

		return $stmt->execute([
			':newsId' => $post->getId()
		]);

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

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::setDataMap(new NewsPostDataMap());
		}
	}

	public static function getStopShot($from = 0, $count = 2) {
		self::checkDataMap();

		return self::$dataMap->findRaw('name like "Стоп-кадр%" and status = ' . NewsPost::STATUS_PUBLISHED,
			['createDate' => 'desc'], $from, $count
		);
	}

	/**
	 * @param $count
	 *
	 * @return NewsPost[]
	 */
	public static function getTopPosts($count) {
		self::checkDataMap();

		return self::$dataMap->findRaw("status = " . NewsPost::STATUS_PUBLISHED . " AND createDate > " . strtotime("-2 week"),
			array('comments' => DataMap::DESC, 'createDate' => DataMap::DESC),
			0, $count);
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

	public static function getByTag($tagId) {
		$dataMap = new NewsTagDataMap();

		$posts = $dataMap->findByLink($tagId);

		return $posts;
	}
}