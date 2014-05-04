<?php
/**
 * User: anubis
 * Date: 03.09.13 15:00
 */

namespace popcorn\model\dataMaps;

use popcorn\lib\mmc\MMC;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\NewsPost;
use popcorn\model\tags\Tag;

class NewsTagDataMap extends CrossLinkedDataMap {

	const POPULAR_POSTS = 1;

	function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();

		$this->findLinkedStatement =
			$this->prepare("
                SELECT t.* FROM pn_tags AS t
                LEFT JOIN pn_news_tags AS l ON (l.entityId = t.id)
                WHERE l.newsId = :id ORDER BY t.id ASC");
		$this->cleanStatement = $this->prepare("DELETE FROM pn_news_tags WHERE newsId = :id");
		$this->insertStatement = $this->prepare("INSERT INTO pn_news_tags (newsId, type, entityId) VALUES (:id, :type, :entityId)");
		$this->fidnByLinkStatement = $this->prepare("
            SELECT n.* FROM pn_news AS n
            INNER JOIN pn_news_tags AS t ON (n.id = t.newsId)
            WHERE t.tagId = :id
        ");
	}

	/**
	 * @param $id
	 *
	 * @return NewsPost[]
	 */
	public function findByLink($id) {
		$dm = new NewsPostDataMap();
		$this->fidnByLinkStatement->bindValue(':id', $id);
		$this->fidnByLinkStatement->execute();
		$items = $this->fidnByLinkStatement->fetchAll(\PDO::FETCH_CLASS, $dm->getClass());

		foreach ($items as &$item) {
			$dm->itemCallback($item);
		}

		return $items;
	}

	final public function save($tags, $postId) {

		$this->checkStatement($this->cleanStatement);
		$this->checkStatement($this->insertStatement);
		$this->cleanStatement->bindValue(':id', $postId);
		$this->cleanStatement->execute();

		if (empty($items)) {
			return;
		}

		$this->insertStatement->bindValue(':id', $postId);

		foreach ($tags as $tag) {
			if ($tag instanceof Person) {
				$person = $tag;

				$this->insertStatement->bindValue(':type', Tag::PERSON);
				$this->insertStatement->bindValue(':entityId', $person->getId());

			} elseif ($tag instanceof Tag) {
				$this->insertStatement->bindValue(':type', $tag->getType());
				$this->insertStatement->bindValue(':entityId', $tag->getId());
			}

			$this->insertStatement->execute();
		}
	}

	/**
	 * @param Person $person
	 * @param array $paginator
	 * @param $modifier
	 * @return NewsPost[]
	 */
	public function findByPerson(Person $person, array &$paginator, $modifier = null) {

		$dm = new NewsPostDataMap();

		$sql = <<<EOL
SELECT
	%s
FROM
         pn_news      news
	JOIN pn_news_tags newsTags ON (newsTags.newsId = news.id)
	JOIN pn_tags      tags     ON (tags.id = newsTags.tagId)
WHERE
	tags.type = :tagType AND tags.name = :person
EOL;

		$binds = [
			':tagType' => Tag::PERSON,
			':person'  => $person->getId()
		];

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute($binds);

		$totalFound = $stmt->fetchColumn();

		if ($modifier == self::POPULAR_POSTS) {
			$sql .= $this->getOrderString([
				'news.comments' => 'desc',
				'news.views'    => 'desc'
			]);
		} else {
			$sql .= $this->getOrderString(['news.id' => 'desc']);
		}

		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		$stmt = $this->prepare(sprintf($sql, 'news.*'));
		$stmt->execute($binds);

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $dm->getClass());

//		$cacheTagName = MMC::genTag(get_class($person), $person->getId(), 'news');

		foreach ($items as &$item) {
//			$item = MMC::getSet(MMC::genKey($dm->class, __METHOD__, func_get_args(), $item),
//				strtotime('+1 month'),
//				[$cacheTagName, 'news'],
//				function () use ($dm, $item) {
			$dm->itemCallback($item);
//					return $item;
//				}
//			);
		}

		return $items;
	}

	protected function mainDataMap() {
		return new TagDataMap();
	}

}