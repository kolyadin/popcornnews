<?php

/**
 * @author Azar Khuzhin
 *
 * Класс для работы с memcache
 */

class VPA_memcache {
	/**
	 * Main memcahe resource
	 *
	 * @var resource
	 */
	protected $memcache = null;
	/**
	 * Default lifetime
	 * For 1 hour
	 *
	 * @var int
	 */
	protected $cache_lifetime = 3600;
	/**
	 * Default flags
	 *
	 * @var int
	 */
	protected $flags = false;


	/**
	 * Get an instance
	 *
	 * @staticvar VPA_memcache $memcache
	 * @return VPA_memcache
	 */
	static public function getInstance() {
		static $memcache;

		if (!isset($memcache)) {
			$memcache = new vpa_memcache();
		}

		return $memcache;
	}

	/**
	 * Connect to memcache
	 *
	 * @return resource
	 */
	public function __construct() {
		$this->memcache = new Memcache();
	}

	/**
	 * Restore connection to Memcache Server
	 */
	protected function restore() {
		if (!@$this->memcache->getversion()) {
			$this->memcache->connect('unix:///var/run/memcached/memcached.sock', null);
		}
	}

	/**
	 * Get cache by key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		$this->restore();
		return $this->memcache->get(crc32(WWW_DIR . __CLASS__ . $key));
	}

	/**
	 * Return true if cache is set
	 *
	 * @param string $key
	 * @return bool
	 */
	public function is($key) {
		$this->restore();
		if ($this->get($key)) return true;
		return false;
	}

	/**
	 * Set key data
	 * If key already exists than reset key data
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $lifetime
	 * @return mixed
	 */
	public function set($key, $data, $lifetime = null) {
		$this->restore();
		if (is_null($lifetime)) $lifetime = $this->cache_lifetime;

		$result = $this->memcache->replace(crc32(WWW_DIR . __CLASS__ . $key), $data, $this->flags, $lifetime);
		if ($result === false) {
			$result = $this->memcache->set(crc32(WWW_DIR . __CLASS__ . $key), $data, $this->flags, $lifetime);
		}
		return $result;
	}

	public function delete($key) {
		$this->restore();
		/* zero - for second param: http://ru2.php.net/manual/en/memcache.delete.php#95344 */
		return $this->memcache->delete(crc32(WWW_DIR . __CLASS__ . $key), 0);
	}

	public function add($key, $data, $lifetime) {
		$this->restore();
		if (!$lifetime) $lifetime = $this->cache_lifetime;

		return $this->memcache->add(crc32(WWW_DIR . __CLASS__ . $key), $data, $this->flags, $lifetime);
	}
}
