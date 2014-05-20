<?php
/**
 * User: anubis
 * Date: 03.09.13 15:00
 */

namespace popcorn\model\dataMaps;

use popcorn\lib\mmc\MMC;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\Movie;
use popcorn\model\posts\MovieFactory;
use popcorn\model\posts\PhotoArticlePost;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;

class NewsTagDataMap extends CrossLinkedDataMap {

	const POPULAR_POSTS = 1;

	function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();

		$this->findLinkedStatement =
			$this->prepare("
                SELECT t.id,t.name,t.type FROM pn_tags AS t
				JOIN pn_news_tags AS l ON (l.entityId = t.id and (l.type = " . Tag::ARTICLE . " or l.type = " . Tag::EVENT . "))
				WHERE l.newsId = :id

				union all

				SELECT p.id,p.name," . Tag::PERSON . " type FROM pn_persons AS p
				JOIN pn_news_tags AS l ON (l.entityId = p.id and l.type = " . Tag::PERSON . ")
				WHERE l.newsId = :id

				union all

				SELECT m.id,m.name," . Tag::MOVIE . " type FROM ka_movies AS m
				JOIN pn_news_tags AS l ON (l.entityId = m.id and l.type = " . Tag::MOVIE . ")
				WHERE l.newsId = :id");

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
	 * @return Model[]
	 */
	public function findById($id) {

		$this->checkStatement($this->findLinkedStatement);
		$this->findLinkedStatement->bindValue(':id', $id);
		$this->findLinkedStatement->execute();
		$items = $this->findLinkedStatement->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($items as &$item) {
			if ($item['type'] == Tag::PERSON) {
				$item = PersonFactory::getPerson($item['id']);
			} elseif (in_array($item['type'], [Tag::EVENT, Tag::ARTICLE])) {
				$item = TagFactory::get($item['id']);
			} elseif ($item['type'] == Tag::MOVIE) {
				$item = MovieFactory::getMovie($item['id']);
			}
		}

		return $items;
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

		if (empty($tags)) {
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
			} elseif ($tag instanceof Movie) {
				$movie = $tag;

				$this->insertStatement->bindValue(':type', Tag::MOVIE);
				$this->insertStatement->bindValue(':entityId', $movie->getId());
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

		$sql = "select %s from pn_news where id in (
			select newsId from pn_news_tags where type = :type and entityId = :personId
		)";

		$binds = [
			':type'     => Tag::PERSON,
			':personId' => $person->getId()
		];

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute($binds);

		$totalFound = $stmt->fetchColumn();

		if ($modifier == self::POPULAR_POSTS) {
			$sql .= $this->getOrderString([
				'comments' => 'desc',
				'views'    => 'desc'
			]);
		} else {
			$sql .= $this->getOrderString(['id' => 'desc']);
		}

		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		$stmt = $this->prepare(sprintf($sql, '*'));
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