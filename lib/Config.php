<?php
/**
 * User: anubis
 * Date: 13.10.13
 * Time: 14:06
 */

namespace popcorn\lib;

use popcorn\model\exceptions\Exception;

class Config {

	private static $cfg = null;
	private static $mode = 'development';

	private static function checkCfg() {
		if (is_null(self::$cfg)) {
			$cfgFile = __DIR__ . '/../config/config.ini';
			$defaultCfgFile = $cfgFile . '.default';
			if (!file_exists($cfgFile)) {
				if (!file_exists($defaultCfgFile)) {
					throw new Exception("Default config file is not found");
				}
				if (!copy($defaultCfgFile, $cfgFile)) {
					throw new \RuntimeException("on copy config");
				}
			}
			self::$cfg = parse_ini_file($cfgFile, true);
		}
	}


	public static function getMode() {

		if (isset($_SERVER['POPCORN_MODE'])) {
			return strtolower($_SERVER['POPCORN_MODE']);
		}

		return 'development';
	}

	public static function getPDOUser() {
		self::checkCfg();

		return self::$cfg['PDO']['user'];
	}


	public static function getPDODsn() {
		self::checkCfg();

		return self::$cfg['PDO']['dsn'];
	}

	public static function getPDOPassword() {
		self::checkCfg();

		return self::$cfg['PDO']['password'];
	}

	public static function getPDOEncoding() {
		self::checkCfg();

		return self::$cfg['PDO']['encoding'];
	}

	public static function getServers($name = null) {
		self::checkCfg();
		if (!array_key_exists('Servers', self::$cfg)) {
			throw new Exception;
		}

		if (!is_null($name) && !array_key_exists($name, self::$cfg['Servers'])) {
			throw new Exception;
		}

		if (is_null($name)) {
			return self::$cfg['Servers'];
		} else {
			return self::$cfg['Servers'][$name];
		}

	}

	public static function getRandomServer(){
		self::checkCfg();

		if (!array_key_exists('Servers', self::$cfg)) {
			throw new Exception;
		}

		$servers = self::$cfg['Servers'];
		shuffle($servers);

		return array_shift($servers);
	}

	public static function getMMCHost() {
		self::checkCfg();
		if (!array_key_exists('Memcache', self::$cfg)) {
			throw new Exception;
		}
		return self::$cfg['Memcache']['host'];
	}

	public static function getMMCPort() {
		self::checkCfg();
		if (!array_key_exists('Memcache', self::$cfg)) {
			throw new Exception;
		}
		$port = null;
		if (array_key_exists('port', self::$cfg['Memcache'])) {
			$port = self::$cfg['Memcache']['port'];
			if (empty($port)) $port = null;
		}

		return $port;
	}

	public static function getInfo() {

		self::checkCfg();

		if (!array_key_exists('Memcache', self::$cfg)) {
			throw new Exception;
		}

		print '<pre>'.print_r(self::$cfg,true).'</pre>';

		return self::$cfg['Info'];
	}

}