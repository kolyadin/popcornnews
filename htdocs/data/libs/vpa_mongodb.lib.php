<?php

/**
 * @author Azar Khuzhin
 *
 * Model for mongoDB
 */

class VPA_MongoDB {
	/**
	 * Main MongoDB resource
	 *
	 * @var object
	 */
	protected $mongo = null;
	/**
	 * DB
	 *
	 * @var object
	 */
	protected $db;


	/**
	 * Get an instance
	 *
	 * @staticvar resource $mongo
	 * @return MongoDb
	 */
	static public function getInstance() {
		static $mongo;

		if (!isset($mongo)) {
			$mongo = new VPA_MongoDB();
		}

		return $mongo->db();
	}

	/**
	 * Connect to MongoDB
	 *
	 * @return resource
	 */
	public function __construct() {
		$this->mongo = new Mongo();
		$this->db = $this->mongo->popcornnews;
	}

	/**
	 * DB
	 *
	 * @return MongoDb
	 */
	public function db() {
		return $this->db;
	}
}
