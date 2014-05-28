<?php
/**
 * User: anubis
 * Date: 14.10.13
 * Time: 16:26
 */

namespace popcorn\model\persons;

use popcorn\lib\PDOHelper;
use popcorn\model\content\Image;
use popcorn\model\dataMaps\KidDataMap;

class KidFactory {

	/**
	 * @var KidDataMap
	 */
	private static $dataMap;

	/**
	 * @param Person $firstParent
	 * @param Person $secondParent
	 * @param string $name
	 * @param \DateTime $birthDate
	 * @param string $description
	 * @param Image $photo
	 *
	 * @return \popcorn\model\persons\Kid
	 */
	public static function create(
		$firstParent, $secondParent,
		$name, $birthDate, $description, $photo) {

		$kid = new Kid();
		$kid->setFirstParent($firstParent);
		$kid->setSecondParent($secondParent);
		$kid->setName($name);
		$kid->setBirthDate($birthDate);
		$kid->setDescription($description);
		$kid->setPhoto($photo);

		self::save($kid);

		return $kid;
	}

	/**
	 * @param $id
	 *
	 * @return Kid
	 */
	public static function get($id) {
		self::checkDataMap();

		return self::$dataMap->findById($id);
	}

	private static function checkDataMap() {
		if (is_null(self::$dataMap)) {
			self::$dataMap = new KidDataMap();
		}
	}

	/**
	 * @param KidBuilder $builder
	 *
	 * @return \popcorn\model\persons\Kid
	 */
	public static function createFromBuilder($builder) {
		$kid = $builder->build();
		self::save($kid);

		return $kid;
	}

	/**
	 * @param Kid $kid
	 */
	public static function save($kid) {
		self::checkDataMap();
		self::$dataMap->save($kid);
	}

	public static function getRandomKid() {
		self::checkDataMap();

		return self::$dataMap->getRandomKid();
	}

	/**
	 * @param int $from
	 * @param $count
	 *
	 * @return Kid[]
	 */
	public static function getKids($from = 0, $count = -1) {
		self::checkDataMap();

		return self::$dataMap->getKids($from, $count);
	}


	/**
	 * @param $kidId
	 * @return bool
	 */
	public static function removeKid($kidId) {
		self::checkDataMap();
		return self::$dataMap->delete($kidId);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public static function delete($id) {
		self::checkDataMap();

		return self::$dataMap->delete($id);
	}

}