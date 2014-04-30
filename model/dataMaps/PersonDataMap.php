<?php

namespace popcorn\model\dataMaps;

use PDO;
use popcorn\lib\mmc\MMC;
use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\ajax\VotingNotAllowException;
use popcorn\model\persons\Person;
use popcorn\model\voting\VotingFactory;

class PersonDataMap extends DataMap {

	//Время действия ограничения
	const RESTRICT_INTERVAL = 86400;

	const WITH_NONE = 1;
	const WITH_IMAGES = 2;
	const WITH_PHOTO = 4;
	const WITH_ALL = 7;


	/**
	 * @var NewsImageDataMap
	 */
	private $imagesDataMap;
	private $checksum;
	private $pdo;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();

		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];

		$this->checksum = md5(implode('', [$ip, $browser]));
		$this->pdo = PDOHelper::getPDO();

		$this->class = "popcorn\\model\\persons\\Person";
		$this->initStatements();
		$this->imagesDataMap = new PersonImageDataMap();
	}

	public function getChecksum() {
		return $this->checksum;
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_persons
            (name, englishName, genitiveName, prepositionalName, info, source,
            photo, birthDate, showInCloud, sex, isSinger, allowFacts, vkPage, twitterLogin,
            instagramLogin, pageName, nameForBio, published, urlName,
            look, style, talent)
            VALUES (:name, :englishName, :genitiveName, :prepositionalName, :info, :source,
            :photo, :birthDate, :showInCloud, :sex, :isSinger, :allowFacts, :vkPage, :twitterLogin,
             :instagramLogin, :pageName, :nameForBio, :published, :urlName,
            :look, :style, :talent)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_persons
            SET
                name = :name,
                englishName = :englishName,
                genitiveName = :genitiveName,
                prepositionalName = :prepositionalName,
                info = :info,
                source = :source,
                photo = :photo,
                birthDate = :birthDate,
                showInCloud = :showInCloud,
                sex = :sex,
                isSinger = :isSinger,
                allowFacts = :allowFacts,
                vkPage = :vkPage,
                twitterLogin = :twitterLogin,
                instagramLogin = :instagramLogin,
                pageName = :pageName,
                nameForBio = :nameForBio,
                published = :published,
                urlName = :urlName
            WHERE id = :id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_persons WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_persons WHERE id=:id");
	}

	/**
	 * @param Person $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":name", $item->getName());
		$this->insertStatement->bindValue(":englishName", $item->getEnglishName());
		$this->insertStatement->bindValue(":genitiveName", $item->getGenitiveName());
		$this->insertStatement->bindValue(":prepositionalName", $item->getPrepositionalName());
		$this->insertStatement->bindValue(":info", $item->getInfo());
		$this->insertStatement->bindValue(":source", $item->getSource());
		$this->insertStatement->bindValue(":photo", $item->getPhoto()->getId());
		if (!is_null($item->getBirthDate())) {
			$this->insertStatement->bindValue(":birthDate", $item->getBirthDate()->format('Y-m-d'));
		} else {
			$this->insertStatement->bindValue(':birthDate', null);
		}
		$this->insertStatement->bindValue(":showInCloud", $item->isShowInCloud(), \PDO::PARAM_BOOL);
		$this->insertStatement->bindValue(":sex", $item->getSex());
		$this->insertStatement->bindValue(":isSinger", $item->isSinger(), \PDO::PARAM_BOOL);
		$this->insertStatement->bindValue(":allowFacts", $item->isAllowFacts(), \PDO::PARAM_BOOL);
		$this->insertStatement->bindValue(":vkPage", $item->getVkPage());
		$this->insertStatement->bindValue(":twitterLogin", $item->getTwitterLogin());
		$this->insertStatement->bindValue(":instagramLogin", $item->getInstagramLogin());
		$this->insertStatement->bindValue(":pageName", $item->getPageName());
		$this->insertStatement->bindValue(":nameForBio", $item->getNameForBio());
		$this->insertStatement->bindValue(":published", $item->isPublished(), \PDO::PARAM_BOOL);
		$this->insertStatement->bindValue(":urlName", $item->getUrlName());
		$this->insertStatement->bindValue(":look", $item->getLook());
		$this->insertStatement->bindValue(":style", $item->getStyle());
		$this->insertStatement->bindValue(":talent", $item->getTalent());
	}

	/**
	 * @param Person $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":name", $item->getName());
		$this->updateStatement->bindValue(":englishName", $item->getEnglishName());
		$this->updateStatement->bindValue(":genitiveName", $item->getGenitiveName());
		$this->updateStatement->bindValue(":prepositionalName", $item->getPrepositionalName());
		$this->updateStatement->bindValue(":info", $item->getInfo());
		$this->updateStatement->bindValue(":source", $item->getSource());
		$this->updateStatement->bindValue(":photo", $item->getPhoto()->getId());
		if (!is_null($item->getBirthDate())) {
			$this->updateStatement->bindValue(":birthDate", $item->getBirthDate()->format('Y-m-d'));
		} else {
			$this->updateStatement->bindValue(':birthDate', null);
		}
		$this->updateStatement->bindValue(":showInCloud", $item->isShowInCloud(), \PDO::PARAM_BOOL);
		$this->updateStatement->bindValue(":sex", $item->getSex());
		$this->updateStatement->bindValue(":isSinger", $item->isSinger(), \PDO::PARAM_BOOL);
		$this->updateStatement->bindValue(":allowFacts", $item->isAllowFacts(), \PDO::PARAM_BOOL);
		$this->updateStatement->bindValue(":vkPage", $item->getVkPage());
		$this->updateStatement->bindValue(":twitterLogin", $item->getTwitterLogin());
		$this->updateStatement->bindValue(":instagramLogin", $item->getInstagramLogin());
		$this->updateStatement->bindValue(":pageName", $item->getPageName());
		$this->updateStatement->bindValue(":nameForBio", $item->getNameForBio());
		$this->updateStatement->bindValue(":published", $item->isPublished(), \PDO::PARAM_BOOL);
		$this->updateStatement->bindValue(":urlName", $item->getUrlName());
	}


	/**
	 * @param Person $item
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_PHOTO) {

		$modifier = $this->getModifier($this, $modifier);

		parent::itemCallback($item);

		if ($modifier & self::WITH_PHOTO) {
			$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
		}

		if (!is_null($item->getBirthDate())) {
			$item->setBirthDate(new \DateTime($item->getBirthDate()));
		} else {
			$item->setBirthDate(null);
		}

		if ($modifier & self::WITH_IMAGES) {
			$item->setImages($this->getAttachedImages($item->getId()));
		}
	}

	/**
	 * @param Person $item
	 */
	protected function onInsert($item) {
//		$this->attachImages($item);
	}

	/**
	 * @param Person $item
	 */
	protected function onUpdate($item) {
//		$this->attachImages($item);
	}

	/**
	 * @param Person $item
	 */
	private function attachImages($item) {
		$this->imagesDataMap->save($item->getImages(), $item->getId());
	}

	private function getAttachedImages($id) {
		return $this->imagesDataMap->findById($id);
	}


	/**
	 * @param \popcorn\model\persons\Person $person
	 * @param $category
	 * @throws \popcorn\model\exceptions\ajax\VotingNotAllowException
	 * @internal param object $entity
	 * @return bool
	 */
	public function isVotingAllow(Person $person, $category) {

		$stmt = $this->pdo->prepare('SELECT count(*) FROM pn_persons_voting WHERE checksum = :checksum AND personId = :personId AND category = :category AND (:nowTime - votedAt) <= :restrictTime');
		$stmt->bindValue(':checksum', $this->checksum, \PDO::PARAM_STR);
		$stmt->bindValue(':personId', $person->getId(), \PDO::PARAM_INT);
		$stmt->bindValue(':category', $category, \PDO::PARAM_STR);
		$stmt->bindValue(':nowTime', time(), \PDO::PARAM_INT);
		$stmt->bindValue(':restrictTime', self::RESTRICT_INTERVAL, \PDO::PARAM_INT);

		$stmt->execute();

		if ($stmt->fetchColumn() > 0) {
			throw new VotingNotAllowException();
		} else {
			return true;
		}

	}

	/**
	 * @param array $query
	 * @param int $from
	 * @param $count
	 * @param $orders
	 *
	 * @return Person[]
	 */
	public function find($query = array(), $from = 0, $count = -1, $orders = array()) {
		$sql = "SELECT * FROM pn_persons";
		$where = array();
		$whereBindings = array();
		if (count($query) > 0) {
			foreach ($query as $key => $item) {
				$where[] = $key . ' = :' . $key;
				$whereBindings[':' . $key] = $item;
			}
		}
		$where = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

		$sql .= $where . $this->getLimitString($from, $count);
		$sql .= $this->getOrderString($orders);


//		$cacheKey = MMC::genKey($this->class, __METHOD__, func_get_args());

//		return MMC::getSet($cacheKey, strtotime('+1 week'), ['person'], function () use ($sql, $whereBindings) {
		return $this->fetchAll($sql, $whereBindings);
//		});


	}

	public function findWithPaginator(array $orders = [], array &$paginator = []) {

		$orders = array_merge([
			'name' => 'asc'
		], $orders);

		$sql = 'SELECT %s FROM pn_persons';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute();
		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString($orders);
		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		return $this->fetchAll(sprintf($sql, '*'));

	}

	/**
	 * @param $query
	 * @param array $orders
	 *
	 * @return Person[]
	 * @throws \InvalidArgumentException
	 */
	public function findByName($query, $orders = array()) {
		if (empty($query)) {
			throw new \InvalidArgumentException("Empty query not allowed");
		}
		$sql = "SELECT * FROM pn_persons WHERE name LIKE :query";
		$sql .= $this->getOrderString($orders);

		return $this->fetchAll($sql, array(':query' => '%' . $query . '%'));
	}

	/**
	 * Метод просто проверяет существование персоны
	 *
	 * @param $urlName
	 * @return bool|int
	 */
	public function checkByUrl($urlName) {

		$stmt = $this->prepare('SELECT id FROM pn_persons WHERE BINARY urlName = :urlName LIMIT 1');
		$stmt->bindValue(':urlName', $urlName, \PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount()) {
			$personId = $stmt->fetchColumn();
			return $personId;
		}

		return false;
	}

	/**
	 * @param $personId
	 * @param $from
	 * @param $count
	 * @return array
	 */
	public function getFilmography($personId, $from, $count){

		$sql = 'select movie.* from ka_movies movie join pn_persons_movies p_movie on (p_movie.movieId = movie.id) where p_movie.personId = :personId order by movie.year desc';

		$sql .= $this->getLimitString($from,$count);

		$stmt = $this->prepare($sql);
		$stmt->execute([
			':personId' => $personId
		]);

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

	/**
	 * @param $personId
	 * @return int
	 */
	public function getFilmographyCount($personId){

		$stmt = $this->prepare('select count(*) from ka_movies movie join pn_persons_movies p_movie on (p_movie.movieId = movie.id) where p_movie.personId = :personId');
		$stmt->execute([
			':personId' => $personId
		]);

		return $stmt->fetchColumn();

	}

	/**
	 * Метод возвращает персону
	 *
	 * @param $urlName
	 * @return Person
	 */
	public function findByUrl($urlName) {
		$sql = "SELECT * FROM pn_persons WHERE BINARY urlName = :urlName";
		$persons = $this->fetchAll($sql, array(':urlName' => $urlName));
		if (count($persons) == 0) {
			return null;
		}
		return $persons[0];
	}

	/**
	 * @todo Сброс кэша по ключу, при изменении тегов
	 * @return mixed
	 */
	public function getTop() {

		$sql = 'SELECT name,urlName,newsCount FROM pn_persons ORDER BY newsCount DESC LIMIT 50';

		$stmt = PDOHelper::getPDO()->query($sql);
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

	public function getAllPersonsCount() {
		$stmt = PDOHelper::getPDO()->query('SELECT count(*) FROM pn_persons');
		return $stmt->fetchColumn();
	}

	/**
	 * @param Person $item
	 *
	 * @return Person
	 */
	protected function prepareItem($item) {
		if (!is_object($item->getPhoto())) {
			$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
		}
		if (!($item->getBirthDate() instanceof \DateTime) && !is_null($item->getBirthDate())) {
			$item->setBirthDate(new \DateTime($item->getBirthDate()));
		}
		if (is_null($item->getUrlName())) {
			$name = str_replace('-', '_', $item->getEnglishName());
			$name = str_replace('&dash;', '_', $name);
			$name = str_replace(' ', '-', $name);
			$item->setUrlName($name);
		}

		return parent::prepareItem($item);
	}

}