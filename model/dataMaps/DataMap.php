<?php
/**
 * User: anubis
 * Date: 08.10.13 17:13
 */

namespace popcorn\model\dataMaps;

use PDO;
use PDOStatement;
use popcorn\lib\PDOHelper;
use popcorn\model\exceptions\NullException;
use popcorn\model\Model;

abstract class DataMap {

	const ASC = 'ASC';
	const DESC = 'DESC';

	/**
	 * @var PDOStatement
	 */
	protected $insertStatement = null;
	/**
	 * @var PDOStatement
	 */
	protected $updateStatement = null;
	/**
	 * @var PDOStatement
	 */
	protected $deleteStatement = null;
	/**
	 * @var PDOStatement
	 */
	protected $findOneStatement = null;

	static private $helper;

	/**
	 * @var string
	 */
	protected $class = '';

	/**
	 * @var PDO
	 */
	private $pdo = null;

	function __construct() {
		$this->pdo = PDOHelper::getPDO();
	}

	/**
	 * @param DataMapHelper $helper
	 */
	public final static function setHelper(DataMapHelper $helper) {
		self::$helper = $helper;
	}

	/**
	 * @return DataMapHelper
	 */
	public final static function getHelper() {
		return self::$helper;
	}

	protected function getModifier(DataMap $map, $default = null) {

		if (self::getHelper() instanceof DataMapHelper) {
			$rel = self::getHelper()->getRelationship();

			if (array_key_exists(get_class($map), $rel)) {
				return $rel[get_class($map)];
			}
		}


		return $default;

	}

	/**
	 * @return PDO
	 */
	protected final function getPDO() {
		return $this->pdo;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function delete($id) {
		$this->checkStatement($this->deleteStatement);
		$this->deleteStatement->bindValue(':id', $id);

		$removeStatus = $this->deleteStatement->execute();

		$this->onRemove($id);

		return $removeStatus;
	}

	/**
	 * @param $id
	 *
	 * @return Model|null
	 */
	public function findById($id) {

		$this->checkStatement($this->findOneStatement);
		$this->findOneStatement->bindValue(':id', $id);
		$this->findOneStatement->execute();

		$item = empty($this->class)
			? $this->findOneStatement->fetch(PDO::FETCH_ASSOC)
			: $this->findOneStatement->fetchObject($this->class);
		$this->findOneStatement->closeCursor();

		if ($item === false) {
			return null;
		} else {
			$this->itemCallback($item);

			return $item;
		}
	}

	/**
	 * @param Model $object
	 */
	protected function insert($object) {
		$this->checkStatement($this->insertStatement);
		$this->insertBindings($object);


		$this->insertStatement->execute();
		$object->setId($this->getPDO()->lastInsertId());
	}

	/**
	 * @param Model $object
	 */
	protected function update($object) {
		$this->checkStatement($this->updateStatement);
		$this->updateBindings($object);

//		$this->updateStatement->debugDumpParams();

		$this->updateStatement->bindValue(':id', $object->getId());
		$this->updateStatement->execute();
	}

	/**
	 * @param Model $object
	 */
	public function save($object) {
		$object = $this->prepareItem($object);

		if (is_null($object->getId())) {
			$this->insert($object);
			$this->onInsert($object);
		} else {
			if ($object->isChanged()) {
				$this->update($object);
				$this->onUpdate($object);
			}
		}
		$this->onSave($object);
		$object->onSave();
	}

	/**
	 * @param Model $item
	 */
	protected function itemCallback($item) {
		if (is_array($item)) return;
		$fields = get_object_vars($item);
		foreach ($fields as $field => $value) {
			if (isset($item->$field)) {
				if ($field == 'id') continue;
				$method = 'set' . ucfirst($field);
				if (method_exists($item, $method)) {
					call_user_func(array($item, $method), $value);
				}
				unset($item->$field);
			}
		}
		$this->setItemId($item);
		$item->onLoad();
	}

	/**
	 * @param string $sql
	 *
	 * @return \PDOStatement
	 */
	protected final function prepare($sql) {
		return $this->getPDO()->prepare($sql);
	}

	/**
	 * @param Model $item
	 */
	protected function insertBindings($item) {
	}

	/**
	 * @param Model $item
	 */
	protected function updateBindings($item) {
	}

	/**
	 * @throws \popcorn\model\exceptions\NullException
	 */
	protected final function checkStatement($statement) {
		if (is_null($statement)) {
			throw new NullException;
		}
	}

	/**
	 * @param Model $item
	 *
	 * @return Model
	 */
	protected function prepareItem($item) {
		return $item;
	}

	/**
	 * @param Model $item
	 */
	protected function onSave($item) {
	}

	/**
	 * @param Model $item
	 */
	protected function onInsert($item) {
	}

	/**
	 * @param Model $item
	 */
	protected function onUpdate($item) {
	}

	/**
	 * @param $id
	 */
	protected function onRemove($id) {

	}

	protected function fetchOne($sql, $bindings = array(), $asArray = false) {
		$items = $this->fetchAll($sql, $bindings, $asArray);

		if (isset($items[0])) {
			return $items[0];
		}

		return null;
	}

	protected function fetchAll($sql, $bindings = array(), $asArray = false) {

		$stmt = $this->prepare($sql);
		if (count($bindings) > 0) {
			foreach ($bindings as $key => $value) {
				$stmt->bindValue($key, $value);
			}
		}
		$stmt->execute();
		if ($asArray) {
			$items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		} else {
			$items = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);

			foreach ($items as &$item) {
				$this->itemCallback($item);
			}
		}

		return $items;
	}

	/**
	 * @param $from
	 * @param $count
	 *
	 * @return string
	 */
	protected final function getLimitString($from, $count) {
		$limits = '';
		if ($count > -1) {
			$limits = ' LIMIT ';
			if ($from > 0) {
				$limits = $limits . $from . ', ';
			}
			$limits = $limits . $count;

			return $limits;
		}

		return $limits;
	}

	/**
	 * @param Model $item
	 */
	private function setItemId($item) {
		if (is_null($item->getId())) {
			$item->setId($item->{'id'});
			unset($item->{'id'});
		}
	}

	/**
	 * @param $orders
	 *
	 * @return string
	 */
	protected function getOrderString($orders) {
		$orderString = '';
		if (count($orders) > 0) {
			$orderString = ' ORDER BY ';
			$tmpOrders = array();

			foreach ($orders as $key => $value) {
				$tmpOrders[] = $key . ' ' . $value;
			}

			$orderString .= implode(', ', $tmpOrders);

			return $orderString;
		}

		return $orderString;
	}

	public function getClass() {
		return $this->class;
	}

}