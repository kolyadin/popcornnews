<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\mmc\MMC;
use popcorn\model\content\ImageFactory;
use popcorn\model\persons\Person;
use popcorn\model\posts\fashionBattle\FashionBattle;
use popcorn\model\posts\fashionBattle\FashionBattleDataMap;
use popcorn\model\posts\NewsPost;
use popcorn\model\posts\PostCategory;
use popcorn\model\tags\Tag;

class NewsPostDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_MAIN_IMAGE = 2;
	const WITH_TAGS = 4;
	const WITH_IMAGES = 8;
	const WITH_FASHION_BATTLE = 16;
	const WITH_ALL = 31;

	/**
	 * @var NewsImageDataMap
	 */
	private $imagesDataMap;
	/**
	 * @var NewsTagDataMap
	 */
	private $tagsDataMap;
	/**
	 * @var FashionBattleDataMap
	 */
	private $fashionBattleDataMap;

	private $modifier;

	public function __construct($modifier = self::WITH_NONE) {

		parent::__construct();

		$this->modifier = $modifier;

		$this->class = "popcorn\\model\\posts\\NewsPost";
		$this->initStatements();

		$this->imagesDataMap = new NewsImageDataMap();
		$this->tagsDataMap = new NewsTagDataMap();
		$this->fashionBattleDataMap = new FashionBattleDataMap();
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_news
			(announce, source, sent, uploadRSS, mainImageId, name, createDate, editDate, content, allowComment, status, views, comments)
				VALUES
			(:announce, :source, :sent, :uploadRSS, :mainImageId, :name, :createDate, :editDate, :content, :allowComment, :status, :views, :comments)");
		$this->updateStatement =
			$this->prepare("
			UPDATE pn_news SET announce=:announce, source=:source, sent=:sent, uploadRSS=:uploadRSS, mainImageId=:mainImageId, name=:name, createDate=:createDate, editDate=:editDate, content=:content, allowComment=:allowComment, status=:status, views=:views, comments=:comments WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_news WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_news WHERE id=:id");
	}

	/**
	 * @param NewsPost $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":announce", $item->getAnnounce());
		$this->insertStatement->bindValue(":source", $item->getSource());
		$this->insertStatement->bindValue(":sent", $item->isSent());
		$this->insertStatement->bindValue(":uploadRSS", $item->getUploadRSS());
		$this->insertStatement->bindValue(":mainImageId", $item->getMainImageId()->getId());
		$this->insertStatement->bindValue(":name", $item->getName());
		$this->insertStatement->bindValue(":editDate", $item->getEditDate()->getTimestamp());
		$this->insertStatement->bindValue(":createDate", $item->getCreateDate()->getTimestamp());
		$this->insertStatement->bindValue(":content", $item->getContent());
		$this->insertStatement->bindValue(":allowComment", $item->getAllowComment());
		$this->insertStatement->bindValue(":status", $item->getStatus());
		$this->insertStatement->bindValue(":views", $item->getViews());
		$this->insertStatement->bindValue(":comments", $item->getComments());
	}

	/**
	 * @param NewsPost $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":announce", $item->getAnnounce());
		$this->updateStatement->bindValue(":source", $item->getSource());
		$this->updateStatement->bindValue(":sent", $item->isSent());
		$this->updateStatement->bindValue(":uploadRSS", $item->getUploadRSS());
		$this->updateStatement->bindValue(":mainImageId", $item->getMainImageId()->getId());
		$this->updateStatement->bindValue(":name", $item->getName());
		$this->updateStatement->bindValue(":editDate", $item->getEditDate()->getTimestamp());
		$this->updateStatement->bindValue(":createDate", $item->getCreateDate()->getTimestamp());
		$this->updateStatement->bindValue(":content", $item->getContent());
		$this->updateStatement->bindValue(":allowComment", $item->getAllowComment());
		$this->updateStatement->bindValue(":status", $item->getStatus());
		$this->updateStatement->bindValue(":views", $item->getViews());
		$this->updateStatement->bindValue(":comments", $item->getComments());
		$this->updateStatement->bindValue(":id", $item->getId());
	}

	/**
	 * @param NewsPost $item
	 *
	 * @return NewsPost
	 */
	protected function prepareItem($item) {
		if (!is_object($item->getMainImageId())) {
			$item->setMainImageId(ImageFactory::getImage($item->getMainImageId()));
		}

		if ($item->getFashionBattle() instanceof FashionBattle) {
			if ($item->getFashionBattle()->getId() > 0) {
				$item->addFashionBattle($this->getFashionBattle($item->getId()));
			}
		}

		return parent::prepareItem($item);
	}

	/**
	 * @param NewsPost $item
	 */
	public function itemCallback($item) {

		parent::itemCallback($item);

		if ($this->modifier & self::WITH_MAIN_IMAGE) {
			if (!is_object($item->getMainImageId())) {
				$item->setMainImageId(ImageFactory::getImage($item->getMainImageId()));
			}
		}

		if ($this->modifier & self::WITH_TAGS) {
			$item->setTags($this->getAttachedTags($item->getId()));
		}

		if ($this->modifier & self::WITH_IMAGES) {
			$item->setImages($this->getAttachedImages($item->getId()));
		}

		if ($this->modifier & self::WITH_FASHION_BATTLE) {
			$item->addFashionBattle($this->getFashionBattle($item->getId()));
		}

	}

	/**
	 * @param NewsPost $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
		$this->attachTags($item);
		$this->attachFashionBattle($item);

		MMC::delByTag('post');
	}

	/**
	 * @param NewsPost $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
		$this->attachTags($item);
		$this->attachFashionBattle($item);

		MMC::delByTag('post');
	}

	/**
	 * При удалении новости удаляем:
	 * - все вложенные фотки
	 * - все связи тегов
	 * - связанный опрос (если есть)
	 * - связанный fashion battle (если есть)
	 *
	 * @param $postId
	 */
	protected function onRemove($postId) {
		$this->getPDO()->prepare('
			DELETE FROM pn_images WHERE id IN(SELECT imageId FROM pn_news_images WHERE newsId = :postId);
			DELETE FROM pn_news_images WHERE newsId = :postId;
			DELETE FROM pn_news_tags WHERE newsId = :postId;
			DELETE FROM pn_news_poll WHERE newsId = :postId;
			DELETE FROM pn_news_fashion_battle WHERE newsId = :postId
		')->execute([
			':postId' => $postId
		]);
	}

	/**
	 * @param NewsPost $item
	 */
	private function attachTags($item) {
		$this->tagsDataMap->save($item->getTags(), $item->getId());
	}

	/**
	 * @param int $id
	 *
	 * @return Tag[]
	 */
	private function getAttachedTags($id) {
		return $this->tagsDataMap->findById($id);
	}

	/**
	 * @param NewsPost $item
	 */
	private function attachImages($item) {
		$this->imagesDataMap->save($item->getImages(), $item->getId());

	}

	private function getAttachedImages($id) {
		return $this->imagesDataMap->findById($id);
	}

	/**
	 * @param NewsPost $item
	 */
	private function attachFashionBattle($item) {
		if ($item->getFashionBattle() instanceof FashionBattle) {
			$this->fashionBattleDataMap->saveWithPost($item);
		}
	}

	private function getFashionBattle($id) {
		$item = $this->fashionBattleDataMap->getByPostId($id);

		return $item;
	}

	/**
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param int $totalFound
	 * @return NewsPost[]
	 */
	public function findByLimit(array $options = [], $from = 0, $count = -1, &$totalFound = 0) {

		$options = array_merge([
			'status'  => NewsPost::STATUS_PUBLISHED,
			'orderBy' => [
				'createDate' => 'desc'
			]
		], $options);

		$sql = 'SELECT %s FROM pn_news WHERE status = :status';

		$binds = [
			':status' => $options['status']
		];

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute($binds);

		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'), $binds);
	}

	public function findByDate($from, $to, $limit = 0) {
		$sql = "SELECT *
				FROM pn_news
				WHERE status = " . NewsPost::STATUS_PUBLISHED . " and createDate BETWEEN $from AND $to
				ORDER BY createDate DESC";

		if ($limit) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $this->fetchAll($sql);
	}

	/**
	 * Находим ранние новости, основываясь на текущем посте
	 *
	 * @param NewsPost $post
	 * @return array
	 */
	public function findEarlier(NewsPost $post) {

		$currentDate = date('Y-m-d H:i:s', $post->getCreateDate());

		$sql = "SELECT * FROM pn_news WHERE status = " . NewsPost::STATUS_PUBLISHED . " and
				YEAR(from_unixtime(createDate)) = YEAR('$currentDate' - INTERVAL :month MONTH) AND
				MONTH(from_unixtime(createDate)) = MONTH('$currentDate' - INTERVAL :month MONTH)
			 ORDER BY comments DESC LIMIT 5";

		return [
			'month1' => $this->fetchAll($sql, [':month' => 1]),
			'month2' => $this->fetchAll($sql, [':month' => 2]),
			'month3' => $this->fetchAll($sql, [':month' => 3])
		];
	}

	/**
	 * @param NewsPost $post
	 */
	public function updateViews(NewsPost $post) {

		$stmt = $this->prepare('UPDATE pn_news SET views = views+1 WHERE id = :postId LIMIT 1');
		$stmt->execute([
			':postId' => $post->getId()
		]);

	}

	/**
	 * @param $query
	 * @param array $orders
	 * @param int $from
	 * @param $count
	 * @return array
	 */
	public function findRaw($query, array $orders = [], $from = 0, $count = -1) {

		$sql = "SELECT * FROM pn_news";
		$where = empty($query) ? '' : ' WHERE ' . $query;
		$orderString = $this->getOrderString($orders);
		$limits = $this->getLimitString($from, $count);
		$sql .= $where . $orderString . $limits;

		return $this->fetchAll($sql);
	}

	public function findPopular(array $options, array $offset) {

		$options = array_merge([
			'category' => null,
			'tag'      => null
		], $options);

		if ($options['category']) {
			$sql = <<<EOL
SELECT
	news.*
FROM
         pn_news      news
	JOIN pn_news_tags newsTags ON (newsTags.newsId = news.id)
	JOIN pn_tags      tags     ON (tags.id = newsTags.tagId)
WHERE
	tags.type = :tagType AND tags.name = :category
EOL;
			$binds = [
				':tagType'  => Tag::ARTICLE,
				':category' => $options['category']
			];

			$stmt = $this->prepare($sql);
			$stmt->execute($binds);

		}

	}

	/**
	 * @param $postId
	 * @param array $options
	 *
	 * @return null|\popcorn\model\Model
	 */
	public function findById($postId, array $options = []) {
		$options = array_merge([
			'status' => NewsPost::STATUS_PUBLISHED
		], $options);

		return $this->fetchOne('SELECT * FROM pn_news WHERE id = :postId AND status = :status LIMIT 1', [
			':postId' => $postId,
			':status' => $options['status']
		]);
	}

	/**
	 * @param $categoryId
	 * @param int $from
	 * @param $count
	 * @param $totalFound
	 * @param array $options
	 * @return NewsPost[]
	 */
	public function findByCategory($categoryId, array $options = [], $from = 0, $count = -1, &$totalFound) {

		$options = array_merge([
			'status' => NewsPost::STATUS_PUBLISHED
		], $options);

		$sql = 'SELECT %s FROM pn_news t_n
			JOIN pn_news_tags t_nt ON (t_nt.newsId = t_n.id)
			WHERE t_nt.type = :type AND t_nt.entityId = :categoryId AND t_n.status = :status';

		$binds = [
			':type'       => Tag::ARTICLE,
			':categoryId' => $categoryId,
			':status'     => $options['status']
		];

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute($binds);

		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString(['createDate' => 'desc']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, 't_n.*'), $binds);
	}

	/**
	 * @param $tagId
	 * @param int $from
	 * @param $count
	 * @param array $options
	 * @param $totalFound
	 * @return NewsPost[]
	 */
	public function findByTag($tagId, array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'status' => NewsPost::STATUS_PUBLISHED
		], $options);

		$sql = 'SELECT %s FROM pn_news t_n
			JOIN pn_news_tags t_nt ON (t_nt.newsId = t_n.id)
			WHERE t_nt.type = :type AND t_nt.entityId = :tagId AND t_n.status = :status';

		$binds = [
			':type'   => Tag::EVENT,
			':tagId'  => $tagId,
			':status' => $options['status']
		];

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString(['createDate' => 'desc']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, 't_n.*'), $binds);
	}

	/**
	 * @param \popcorn\model\persons\Person $person
	 * @param int $from
	 * @param $count
	 * @param array $options
	 * @param $totalFound
	 * @return NewsPost[]
	 */
	public function findByPerson(Person $person, array $options = [], $from = 0, $count = -1, &$totalFound = -1) {

		$options = array_merge([
			'status' => NewsPost::STATUS_PUBLISHED
		], $options);

		$sql = 'SELECT %s FROM pn_news t_n
			JOIN pn_news_tags t_nt ON (t_nt.newsId = t_n.id)
			WHERE t_nt.type = :type AND t_nt.entityId = :personId AND t_n.status = :status';

		$binds = [
			':type'     => Tag::PERSON,
			':personId' => $person->getId(),
			':status'   => $options['status']
		];

		if ($totalFound != -1) {
			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$totalFound = $stmt->fetchColumn();
		}

		$sql .= $this->getOrderString(['t_n.createDate' => 'desc']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, 't_n.*'), $binds);
	}

	public function getTopPosts($from = 0, $count = -1) {

		$sql = 'select t_n.* from pn_news t_n
			where t_n.status = :status and
				t_n.createDate <= :dateTill and t_n.createDate >= :dateFrom
			order by t_n.comments desc, t_n.createDate desc';

		$sql .= $this->getLimitString($from, $count);

		$cacheKey = MMC::genKey($this->class, __METHOD__, func_get_args());

//		return MMC::getSet($cacheKey, strtotime('+3 hour'), function () use ($sql) {

		$stmt = $this->prepare('select createDate from pn_news where status = :status order by createDate desc limit 1');
		$stmt->execute([
			':status' => NewsPost::STATUS_PUBLISHED
		]);

		$lastTime = $stmt->fetchColumn();

		return $this->fetchAll($sql, [
			':status'   => NewsPost::STATUS_PUBLISHED,
			':dateFrom' => strtotime("-2 weeks", $lastTime),
			':dateTill' => $lastTime
		]);
//		});
	}
}