<?php
/**
 * User: anubis
 * Date: 10.09.13 15:58
 */

namespace popcorn\lib;

use PDO;

class PDOHelper {

	private static $PDO = null;

	/**
	 * @return \PDO
	 */
	public static function getPDO() {
		if (is_null(self::$PDO)) {
			$options = array();
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . Config::getPDOEncoding();
			self::$PDO = new PDO(
				Config::getPDODsn(), Config::getPDOUser(), Config::getPDOPassword(),
				$options
			);

			self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		return self::$PDO;
	}

	public static function truncate($table) {
		if (is_array($table)) {
			foreach ($table as $q) {
				self::getPDO()->query(sprintf('truncate %s', $q));
			}
		} else {
			self::getPDO()->query(sprintf('truncate %s', $table));
		}
	}
}