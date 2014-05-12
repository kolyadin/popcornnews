<?php
/**
 * User: anubis
 * Date: 08.10.13 17:34
 */

namespace popcorn\model\dataMaps;


use PDO;
use PDOStatement;
use popcorn\lib\PDOHelper;
use popcorn\lib\SphinxHelper;
use popcorn\model\persons\PersonFactory;
use popcorn\model\tags\Tag;

class TagDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_ENTITY = 2;
	const WITH_ALL = 3;

	/**
	 * @var PDOStatement
	 */
	protected $findByIdsStatement = null;

	function __construct() {
		parent::__construct();
		$this->class = 'popcorn\\model\\tags\\Tag';
		$this->insertStatement = $this->prepare("INSERT INTO pn_tags (name, type) VALUES (:name, :type)");
		$this->updateStatement = $this->prepare("UPDATE pn_tags SET name = :name, type = :type WHERE id = :id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_tags WHERE id = :id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_tags WHERE id = :id");
	}


	/**
	 * @param $ids
	 *
	 * @return Tag[]
	 */
	public function findByIds($ids) {
		$idsList = array();
		for ($i = 0; $i < count($ids); $i++) {
			$idsList[] = ':id' . $i;
		}
		$idsList = implode(',', $idsList);
		$this->findByIdsStatement = $this->prepare("SELECT * FROM pn_tags WHERE id IN (" . $idsList . ")");
		foreach ($ids as $k => $id) {
			$this->findByIdsStatement->bindValue(':id' . $k, $id);
		}
		$this->findByIdsStatement->execute();
		$items = $this->findByIdsStatement->fetchAll(PDO::FETCH_CLASS, $this->class);
		foreach ($items as &$item) {
			$this->itemCallback($item);
		}

		return $items;
	}

	public function find($query = array()) {

		$sql = "SELECT * FROM pn_tags";
		if (isset($query['id'])) {
			$sql .= " WHERE id=:id";

			return $this->fetchAll($sql, array(':id' => $query['id']));
		}

		return $this->fetchAll($sql);
	}

	/**
	 * @param $name
	 * @return array
	 */
	public function findByName($name) {

		return $this->fetchAll('SELECT * FROM pn_tags WHERE name = :name', [':name' => $name]);

	}

	public function findPublicTagsByName($name) {

		$foundTags = $this->fetchAll('SELECT * FROM pn_tags WHERE name LIKE :name AND type = 0 order by name asc', [':name' => "%$name%"]);

		$sphinx = SphinxHelper::getSphinx();

		//region ищем персон
		$query = [
			'(@name               ^%1$s | *%1$s*)',
			'(@englishName        ^%1$s | *%1$s*)',
			'(@genitiveName       ^%1$s | *%1$s*)',
			'(@prepositionalName  ^%1$s | *%1$s*)',
			'(@vkPage             ^%1$s | *%1$s*)',
			'(@twitterLogin       ^%1$s | *%1$s*)',
			'(@urlName            ^%1$s | *%1$s*)'
		];

		$resultPersons = $sphinx
			->query(implode(' | ', $query), $name)
			->in('personsIndex')
			->weights([
				'name' => 70,
				'genitiveName' => 30,
				'prepositionalName' => 30
			])
			->fetch(['popcorn\model\persons\PersonFactory', 'getPerson'])
			->run();

		if ($resultPersons->matchesFound > 0) {
			foreach ($resultPersons->matches as $person) {
				$foundTags[] = $person;
			}
		}

		return $foundTags;


	}

	/**
	 * @todo Сброс кэша по ключу, при изменении тегов
	 * @return mixed
	 */
	public function getTop() {

		$sql = <<<SQL
SELECT
	count(*) overall,
	t_nt.entityId tagId,
	t_t.name
FROM
	     pn_news_tags t_nt
	JOIN pn_tags      t_t ON (t_t.id = t_nt.entityId AND t_t.type = :type)
GROUP BY
	t_nt.entityId
ORDER BY
	overall DESC
LIMIT
	30
SQL;

//		$cacheKey = MMC::genKey($this->class, __METHOD__);

//		return MMC::getSet($cacheKey, strtotime('+1 week'), ['tag'], function () use ($sql) {
		$stmt = $this->prepare($sql);
		$stmt->execute([
			':type' => Tag::EVENT
		]);


		$tags = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return $tags;
//		});

	}

	/**
	 * @param Tag $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":name", $item->getName());
		$this->insertStatement->bindValue(":type", $item->getType());
	}

	/**
	 * @param Tag $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":name", $item->getName());
		$this->updateStatement->bindValue(":type", $item->getType());
		$this->updateStatement->bindValue(":id", $item->getId());
	}

}