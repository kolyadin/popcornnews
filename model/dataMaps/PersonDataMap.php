<?php

namespace popcorn\model\dataMaps;

use PDO;
use popcorn\lib\mmc\MMC;
use popcorn\model\content\ImageFactory;
use popcorn\model\persons\Person;
use popcorn\model\voting\VotingFactory;

class PersonDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_IMAGES = 2;
	const WITH_LOOK = 4;
	const WITH_STYLE = 8;
	const WITH_TALENT = 16;
	const WITH_PHOTO = 32;
	const WITH_WIDGET_PHOTO = 64;
	const WITH_WIDGET_FULL_PHOTO = 128;

	const WITH_ALL = 255;


	/**
	 * @var NewsImageDataMap
	 */
	private $imagesDataMap;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();
		$this->class = "popcorn\\model\\persons\\Person";
		$this->initStatements();
		$this->imagesDataMap = new PersonImageDataMap();
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
		$this->insertStatement->bindValue(":isWidgetAvailable", $item->isWidgetAvailable(), \PDO::PARAM_BOOL);
		$this->insertStatement->bindValue(":widgetPhoto", $item->getWidgetPhoto()->getId());
		$this->insertStatement->bindValue(":widgetFullPhoto", $item->getWidgetFullPhoto()->getId());
		$this->insertStatement->bindValue(":vkPage", $item->getVkPage());
		$this->insertStatement->bindValue(":twitterLogin", $item->getTwitterLogin());
		$this->insertStatement->bindValue(":pageName", $item->getPageName());
		$this->insertStatement->bindValue(":nameForBio", $item->getNameForBio());
		$this->insertStatement->bindValue(":published", $item->isPublished(), \PDO::PARAM_BOOL);
		$this->insertStatement->bindValue(":urlName", $item->getUrlName());
		$this->insertStatement->bindValue(":look", $item->getLook()->getId());
		$this->insertStatement->bindValue(":style", $item->getStyle()->getId());
		$this->insertStatement->bindValue(":talent", $item->getTalent()->getId());
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
		$this->updateStatement->bindValue(":isWidgetAvailable", $item->isWidgetAvailable(), \PDO::PARAM_BOOL);
		$this->updateStatement->bindValue(":widgetPhoto", $item->getWidgetPhoto()->getId());
		$this->updateStatement->bindValue(":widgetFullPhoto", $item->getWidgetFullPhoto()->getId());
		$this->updateStatement->bindValue(":vkPage", $item->getVkPage());
		$this->updateStatement->bindValue(":twitterLogin", $item->getTwitterLogin());
		$this->updateStatement->bindValue(":pageName", $item->getPageName());
		$this->updateStatement->bindValue(":nameForBio", $item->getNameForBio());
		$this->updateStatement->bindValue(":published", $item->isPublished(), \PDO::PARAM_BOOL);
		$this->updateStatement->bindValue(":urlName", $item->getUrlName());
	}

	/**
	 * @param Person $item
	 * @param int $modifier
	 */
	protected function itemCallback($item, $modifier = self::WITH_ALL) {

		$modifier = $this->getModifier($this,$modifier);

		parent::itemCallback($item);

		if ($modifier & self::WITH_LOOK){
			$item->setLook(VotingFactory::getTenVoting($item->getLook()));
		}

		if ($modifier & self::WITH_STYLE){
			$item->setStyle(VotingFactory::getTenVoting($item->getStyle()));
		}

		if ($modifier & self::WITH_TALENT){
			$item->setTalent(VotingFactory::getTenVoting($item->getTalent()));
		}

		if ($modifier & self::WITH_PHOTO){
			$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
		}

		if ($modifier & self::WITH_WIDGET_PHOTO){
			$item->setWidgetPhoto(ImageFactory::getImage($item->getWidgetPhoto()));
		}

		if ($modifier & self::WITH_WIDGET_FULL_PHOTO){
			$item->setWidgetFullPhoto(ImageFactory::getImage($item->getWidgetFullPhoto()));
		}

		if (!is_null($item->getBirthDate())) {
			$item->setBirthDate(new \DateTime($item->getBirthDate()));
		} else {
			$item->setBirthDate(null);
		}

		if ($modifier & self::WITH_IMAGES){
			$item->setImages($this->getAttachedImages($item->getId()));
		}
	}

	/**
	 * @param Person $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
	}

	/**
	 * @param Person $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
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

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_persons
            (name, englishName, genitiveName, prepositionalName, info, source,
            photo, birthDate, showInCloud, sex, isSinger, allowFacts, isWidgetAvailable,
            widgetPhoto, widgetFullPhoto, vkPage, twitterLogin, pageName, nameForBio, published, urlName,
            look, style, talent)
            VALUES (:name, :englishName, :genitiveName, :prepositionalName, :info, :source,
            :photo, :birthDate, :showInCloud, :sex, :isSinger, :allowFacts, :isWidgetAvailable,
            :widgetPhoto, :widgetFullPhoto, :vkPage, :twitterLogin, :pageName, :nameForBio, :published, :urlName,
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
                isWidgetAvailable = :isWidgetAvailable,
                widgetPhoto = :widgetPhoto,
                widgetFullPhoto = :widgetFullPhoto,
                vkPage = :vkPage,
                twitterLogin = :twitterLogin,
                pageName = :pageName,
                nameForBio = :nameForBio,
                published = :published,
                urlName = :urlName
            WHERE id = :id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_persons WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_persons WHERE id=:id");
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

		if ($stmt->rowCount()){
			$personId = $stmt->fetchColumn();
			return $personId;
		}

		return false;
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
	 * @param Person $item
	 *
	 * @return Person
	 */
	protected function prepareItem($item) {
		if (!is_object($item->getPhoto())) {
			$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
		}
		if (!is_object($item->getWidgetPhoto())) {
			$item->setWidgetPhoto(ImageFactory::getImage($item->getWidgetPhoto()));
		}
		if (!is_object($item->getWidgetFullPhoto())) {
			$item->setWidgetFullPhoto(ImageFactory::getImage($item->getWidgetFullPhoto()));
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