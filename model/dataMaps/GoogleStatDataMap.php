<?php

namespace popcorn\model\dataMaps;

class GoogleStatDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\system\\GoogleStat";
		$this->insertStatement = $this->prepare("INSERT INTO `pn_ga` (`date`, `pageviews`, `visits`, `country_json`, `city_json`, `sex_json`, `age_json`)
		    VALUES (:date, :pageviews, :visits, :country_json, :city_json, :sex_json, :age_json)");
		$this->updateStatement = $this->prepare("UPDATE `pn_ga`
			SET `date`=:date, `pageviews`=:pageviews, `visits`=:visits, `country_json`=:country_json, `city_json`=:city_json, `sex_json`=:sex_json, `age_json`=:age_json WHERE `id`=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM `pn_ga` WHERE `id`=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM `pn_ga` WHERE `id`=:id");
	}

	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":date", $item->getDate());
		$this->insertStatement->bindValue(":pageviews", $item->getPageViews());
		$this->insertStatement->bindValue(":visits", $item->getVisits());
		$this->insertStatement->bindValue(":country_json", $item->getCountryJson());
		$this->insertStatement->bindValue(":city_json", $item->getCityJson());
		$this->insertStatement->bindValue(":sex_json", $item->getSexJson());
		$this->insertStatement->bindValue(":age_json", $item->getAgeJson());
	}

	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":date", $item->getDate());
		$this->updateStatement->bindValue(":pageviews", $item->getPageViews());
		$this->updateStatement->bindValue(":visits", $item->getVisits());
		$this->updateStatement->bindValue(":country_json", $item->getCountryJson());
		$this->updateStatement->bindValue(":city_json", $item->getCityJson());
		$this->updateStatement->bindValue(":sex_json", $item->getSexJson());
		$this->updateStatement->bindValue(":age_json", $item->getAgeJson());
	}

	public function findByDate($date) {

		$sql = <<<SQL
			SELECT `id`
			FROM `pn_ga`
			WHERE `date` = ?
SQL;

		$stmt = $this->prepare($sql);
		$stmt->bindValue(1, $date, \PDO::PARAM_STR);
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

	public function getDataByDate($date1, $date2 = false) {

		if ($date2) {
			$sql = <<<SQL
				SELECT `city_json`, `sex_json`, `age_json`
				FROM `pn_ga`
				WHERE `date` BETWEEN ? AND ?
SQL;

			$stmt = $this->prepare($sql);
			$stmt->bindValue(1, $date1, \PDO::PARAM_STR);
			$stmt->bindValue(2, $date2, \PDO::PARAM_STR);
		} else {
			$sql = <<<SQL
				SELECT `city_json`, `sex_json`, `age_json`
				FROM `pn_ga`
				WHERE `date` LIKE ?
SQL;

			$stmt = $this->prepare($sql);
			$stmt->bindValue(1, $date1, \PDO::PARAM_STR);
		}
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

	public function getVisitsByDate($date1, $date2 = false) {

		if ($date2) {
			$sql = <<<SQL
				SELECT `date`, `pageviews`, `visits`
				FROM `pn_ga`
				WHERE `date` BETWEEN ? AND ?
				GROUP BY `date`
SQL;

			$stmt = $this->prepare($sql);
			$stmt->bindValue(1, $date1, \PDO::PARAM_STR);
			$stmt->bindValue(2, $date2, \PDO::PARAM_STR);
		} else {
			$sql = <<<SQL
				SELECT `date`, `pageviews`, `visits`
				FROM `pn_ga`
				WHERE `date` LIKE ?
				GROUP BY `date`
SQL;

			$stmt = $this->prepare($sql);
			$stmt->bindValue(1, $date1, \PDO::PARAM_STR);
		}
		$stmt->execute();

		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

		if ($items === false) return null;

		return $items;

	}

}