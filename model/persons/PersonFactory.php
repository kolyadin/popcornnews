<?php
/**
 * User: anubis
 * Date: 05.09.13 13:46
 */

namespace popcorn\model\persons;


use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\PersonImageDataMap;
use popcorn\model\dataMaps\PersonsLinkDataMap;

class PersonFactory {

	/**
	 * @var PersonsLinkDataMap
	 */
	private static $links = null;

	/**
	 * @var PersonDataMap
	 */
	private static $dataMap = null;

	/**
	 * @param int $id
	 *
	 * @return Person
	 */
	public static function getPerson($id) {
		self::checkDataMap();

		return self::$dataMap->findById($id);
	}

	public static function getPersonPhotos(Person $person) {
		$dataMap = new PersonImageDataMap();
		return $dataMap->findById($person->getId());
	}

	public static function getPersonPhotosByPersonId($personId) {
		$dataMap = new PersonImageDataMap();
		return $dataMap->findById($personId);
	}


	public static function getFilmography(Person $person, $from = 0, $count = -1) {
		self::checkDataMap();

		return self::$dataMap->getFilmography($person->getId(), $from, $count);
	}

	/**
	 * @param Person $person
	 * @return int
	 */
	public static function getFilmographyCount(Person $person) {
		self::checkDataMap();

		return self::$dataMap->getFilmographyCount($person->getId());
	}

	/**
	 * @param array $query
	 * @param int $from
	 * @param int $count
	 * @param array $orders
	 *
	 * @return Person[]
	 */
	public static function getPersons(array $query = [], $from = 0, $count = -1, array $orders = []) {
		self::checkDataMap();

		return self::$dataMap->find($query, $from, $count, $orders);
	}

	/**
	 * @param Person $person
	 */
	public static function savePerson($person) {
		self::checkDataMap();
		self::$dataMap->save($person);
	}

	/**
	 * @param int|array $id
	 */
	public static function removePerson($id) {
		self::checkDataMap();
		self::$dataMap->delete($id);
	}

	/**
	 * @param string $query
	 *
	 * @return Person[]
	 */
	public static function searchPersons($query) {
		self::checkDataMap();

		return self::$dataMap->findByName($query, array('name' => DataMap::ASC));
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::$dataMap = new PersonDataMap();
		}
		if (is_null(self::$links)) {
			self::$links = new PersonsLinkDataMap();
		}
	}

	public static function getLinkedPersons($id) {
		self::checkDataMap();

		return self::$links->find($id);
	}

	/**
	 * @param PersonBuilder $builder
	 *
	 * @return \popcorn\model\persons\Person
	 */
	public static function createFromBuilder($builder) {
		$person = $builder->build();
		self::savePerson($person);

		return $person;
	}

	/**
	 * @param int $person1
	 * @param int $person2
	 *
	 * @return bool
	 */
	public static function link($person1, $person2) {
		self::checkDataMap();

		return self::$links->link($person1, $person2);
	}

	public static function clearLinks($id) {
		self::checkDataMap();
		self::$links->delete($id);
	}

	public static function unlink($person1, $person2) {
		self::checkDataMap();
		self::$links->unlink($person1, $person2);
	}

	public static function getByUrl($url) {
		self::checkDataMap();
		return self::$dataMap->findByUrl($url);
	}

	public static function checkByUrl($url) {
		self::checkDataMap();
		return self::$dataMap->checkByUrl($url);
	}


}