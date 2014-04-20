<?php

namespace popcorn\model\dataMaps;

use popcorn\model\content\ImageFactory;
use popcorn\model\posts\NewsPost;
use popcorn\model\posts\PostCategory;
use popcorn\model\tags\Tag;

class NewsPostDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_MAIN_IMAGE = 2;
	const WITH_TAGS = 4;
	const WITH_IMAGES = 8;
	const WITH_ALL = 15;

	/**
	 * @var NewsImageDataMap
	 */
	private $imagesDataMap;
	/**
	 * @var NewsTagDataMap
	 */
	private $tagsDataMap;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();
		$this->class = "popcorn\\model\\posts\\NewsPost";
		$this->initStatements();
		$this->imagesDataMap = new NewsImageDataMap();
		$this->tagsDataMap = new NewsTagDataMap();
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_news (announce, source, sent, uploadRSS, mainImageId, name, updateDate, createDate, editDate, content, allowComment, published, views, comments, type) VALUES (:announce, :source, :sent, :uploadRSS, :mainImageId, :name, :updateDate, :createDate, :editDate, :content, :allowComment, :published, :views, :comments, :type)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_news SET announce=:announce, source=:source, sent=:sent, uploadRSS=:uploadRSS, mainImageId=:mainImageId, name=:name, updateDate=:updateDate, createDate=:createDate, content=:content, allowComment=:allowComment, published=:published, views=:views, comments=:comments, type=:type WHERE id=:id");
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
		$this->insertStatement->bindValue(":updateDate", $item->getUpdateDate());
		$this->insertStatement->bindValue(":createDate", $item->getCreateDate());
		$this->insertStatement->bindValue(":editDate", $item->getEditDate());
		$this->insertStatement->bindValue(":content", $item->getContent());
		$this->insertStatement->bindValue(":allowComment", $item->getAllowComment());
		$this->insertStatement->bindValue(":published", $item->isPublished());
		$this->insertStatement->bindValue(":views", $item->getViews());
		$this->insertStatement->bindValue(":comments", $item->getComments());
		$this->insertStatement->bindValue(":type", $item->getType());
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
		$this->updateStatement->bindValue(":updateDate", $item->getUpdateDate());
		$this->updateStatement->bindValue(":createDate", $item->getCreateDate());
		$this->updateStatement->bindValue(":content", $item->getContent());
		$this->updateStatement->bindValue(":allowComment", $item->getAllowComment());
		$this->updateStatement->bindValue(":published", $item->isPublished());
		$this->updateStatement->bindValue(":views", $item->getViews());
		$this->updateStatement->bindValue(":comments", $item->getComments());
		$this->updateStatement->bindValue(":type", $item->getType());
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

		return parent::prepareItem($item);
	}

	/**
	 * @param NewsPost $item
	 * @param int $modifier
	 */
	public function itemCallback($item, $modifier = self::WITH_ALL) {

		parent::itemCallback($item);

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_MAIN_IMAGE) {
			if (!is_object($item->getMainImageId())) {
				$item->setMainImageId(ImageFactory::getImage($item->getMainImageId()));
			}
		}

		if ($modifier & self::WITH_TAGS) {
			$item->setTags($this->getAttachedTags($item->getId()));
		}

		if ($modifier & self::WITH_IMAGES) {
			$item->setImages($this->getAttachedImages($item->getId()));
		}


	}

	/**
	 * @param NewsPost $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
		$this->attachTags($item);
	}

	/**
	 * @param NewsPost $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
		$this->attachTags($item);
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

	public function findByDate($from = 0, $count = -1) {
		$sql = "SELECT * FROM pn_news ORDER BY createDate DESC";
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll($sql);
	}

	public function findEarlier(NewsPost $post) {

		$currentDate = date('Y-m-d H:i:s',$post->getCreateDate());

		$sql = "SELECT * FROM pn_news WHERE
				YEAR(from_unixtime(createDate)) = YEAR('$currentDate' - INTERVAL :month MONTH) AND
				MONTH(from_unixtime(createDate)) = MONTH('$currentDate' - INTERVAL :month MONTH)
			 ORDER BY comments DESC LIMIT 5";

		$news = [
			'month1' => $this->fetchAll($sql, [':month' => 1]),
			'month2' => $this->fetchAll($sql, [':month' => 2]),
			'month3' => $this->fetchAll($sql, [':month' => 3])
		];

		return $news;
	}

	public function updateViews(NewsPost $news) {

		$stmt = $this->prepare('UPDATE pn_news SET views = views+1 WHERE id = :newsId');
		$stmt->execute([
			':newsId' => $news->getId()
		]);

	}

	public function findRaw($query, $orders = array(), $from = 0, $count = -1) {
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
			'tag' => null
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
				':tagType' => Tag::ARTICLE,
				':category' => $options['category']
			];

			$stmt = $this->prepare($sql);
			$stmt->execute($binds);

		}

	}

	/**
	 * @param array $options
	 * @param array $offset
	 * @param array $paginator
	 * @return NewsPost[]
	 */
	public function find(array $options = [], array $offset = [], array &$paginator = []) {

		if (isset($options['category'])) {
			$categoryId = PostCategory::$category[$options['category']];

			$sql = <<<EOL
select
	%s
from
         pn_news      news
	join pn_news_tags newsTags on (newsTags.newsId = news.id)
	join pn_tags      tags     on (tags.id = newsTags.tagId)
where
	tags.type = :tagType and tags.name = :category
EOL;

			$binds = [
				':tagType' => Tag::ARTICLE,
				':category' => $categoryId
			];

			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$paginator['overall'] = $stmt->fetchColumn();
			$paginator['pages'] = ceil($paginator['overall'] / $offset[1]);

			$sql .= $this->getOrderString(['news.id' => 'desc']);
			$sql .= $this->getLimitString($offset[0], $offset[1]);

			return $this->fetchAll(sprintf($sql, 'news.*'), $binds);

		} elseif (isset($options['tag'])) {

			$sql = <<<EOL
select
	%s
from
         pn_news      news
	join pn_news_tags newsTags on (newsTags.newsId = news.id)
	join pn_tags      tags     on (tags.id = newsTags.tagId)
where
	tags.type = :tagType and tags.id = :tag
EOL;

			$binds = [
				':tagType' => Tag::EVENT,
				':tag' => $options['tag']
			];

			$stmt = $this->prepare(sprintf($sql, 'count(*)'));
			$stmt->execute($binds);

			$paginator['overall'] = $stmt->fetchColumn();
			$paginator['pages'] = ceil($paginator['overall'] / $offset[1]);

			$sql .= $this->getOrderString(['id' => 'desc']);
			$sql .= $this->getLimitString($offset[0], $offset[1]);

			return $this->fetchAll(sprintf($sql, 'news.*'), $binds);

		} else {

			$sql = 'SELECT %s FROM pn_news';

			$stmt = $this->getPDO()->query(sprintf($sql, 'count(*)'));

			$paginator['overall'] = $stmt->fetchColumn();
			$paginator['pages'] = ceil($paginator['overall'] / $offset[1]);

			$sql .= $this->getOrderString(['id' => 'desc']);
			$sql .= $this->getLimitString($offset[0], $offset[1]);

			return $this->fetchAll(sprintf($sql, '*'));
		}
	}

}