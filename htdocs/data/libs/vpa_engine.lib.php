<?php
/*
 * Engine for DB: version 4.1
 */
// types for release operation OVERLOAD in conditions WHERE
define ('WHERE_NULL', 32);
define ('WHERE_INT', 1);
define ('WHERE_FLOAT', 2);
define ('WHERE_STRING', 4);
define ('WHERE_ARRAY', 8);
define ('WHERE_INTERVAL', 16);
//define ('WHERE_FIND', 64);
require_once LIB_DIR . 'compat.lib.php';
$include_file = 'vpa_' . DB_TYPE . '.lib.php';
if (file_exists(LIB_DIR . $include_file)) {
	require_once(LIB_DIR . $include_file);
} else {
	die("Module for DB::DB_TYPE $include_file not found !");
}

$include_file = 'vpa_' . DB_TYPE . '_sphinx.lib.php';
if (file_exists(LIB_DIR . $include_file)) {
	require_once(LIB_DIR . $include_file);
} else {
	die("Module for DB::DB_TYPE $include_file not found !");
}

class VPA_sql {
	public $quarks;
	public $quarks_connected = array();

	static public function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new VPA_sql;
		}
		return $instance;
	}

	/**
	 * Lazy connect to DB
	 */
	protected function _connect($db_type) {
		// already connected
		if (!empty($this->quarks_connected[$db_type])) {
			return $this;
		}
		// connect
		if (!$this->connect($db_type)) {
			die('<p>Уважаемые посетители!</p><p>На сервере проводятся профилактические работы, пожалуйста, попробуйте зайти через пять минут.</p>');
		}
		$this->quarks_connected[$db_type] = true;

		return $this;
	}

	public function init($db_type, $host, $login = null, $pass = null, $db = null) {
		$this->quarks[$db_type] = new VPA_sql_quark;
		$this->quarks[$db_type]->init($db_type, $host, $login, $pass, $db);
	}

	public function begin($db_type = DB_TYPE) {
		return $this->_connect($db_type)->quarks[$db_type]->begin();
	}

	public function commit($db_type = DB_TYPE) {
		return $this->_connect($db_type)->quarks[$db_type]->commit();
	}

	public function rollback($db_type = DB_TYPE) {
		return $this->_connect($db_type)->quarks[$db_type]->rollback();
	}

	public function connect($db_type = DB_TYPE) {
		return $this->quarks[$db_type]->connect();
	}

	public function get(&$obj, &$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->get($obj, $ret, $params, $orders, $offset, $limit, $groupby);
	}

	public function get_num(&$obj, &$ret, $params, $groupby = null) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->get_num($obj, $ret, $params, $groupby);
	}

	public function add(&$obj, &$ret, $params) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->add($obj, $ret, $params);
	}

	public function add_from_query($query, $db_type = 'mysql') {
		return $this->_connect($db_type)->quarks[$db_type]->add_from_query($query);
	}

	public function set_from_query($query, $db_type = 'mysql') {
		return $this->_connect($db_type)->quarks[$db_type]->set_from_query($query);
	}

	public function set(&$obj, &$ret, $params, $id) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->set($obj, $ret, $params, $id);
	}

	public function set_where(&$obj, &$ret, $params, $where) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->set_where($obj, $ret, $params, $where);
	}

	public function del(&$obj, &$ret, $id) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->del($obj, $ret, $id);
	}

	public function del_where(&$obj, &$ret, $where) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->del_where($obj, $ret, $where);
	}

	public function create_table(&$obj, &$ret, $fields) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->create_table($obj, $ret, $fields);
	}

	public function affected_rows(&$obj) {
		$db_type = $obj->db_type;
		return $this->_connect($db_type)->quarks[$db_type]->affected_rows($obj);
	}
}

class VPA_sql_quark {
	public $db_type;
	public $host;
	public $login;
	public $pass;
	public $db_name;
	public $db;
	public $resourse;
	public $log;

	public function VPA_sql_quark() {
		$this->log = VPA_logger::getInstance();
	}

	public function getInstance() {
		static $instance;
		if (!isset($instance)) $instance = new VPA_sql;
		return $instance;
	}

	public function init($db_type, $host, $login, $pass, $db) {
		$start = $this->log->get_time();
		$this->db_type = $db_type;
		$this->host = $host;
		$this->login = $login;
		$this->pass = $pass;
		$this->db_name = $db;
		$factory = new VPA_DB_drivers_factory;
		$this->db = $factory->init($this->db_type);
		$this->log->add_message(get_class($this), "init('$db_type','$host','$login','$pass','$db')", $start, true);
	}

	public function begin() {
		$start = $this->log->get_time();
		$status = $this->db->begin();
		$this->log->add_message(get_class($this), 'begin()', $start, $status);
		return $status;
	}

	public function commit() {
		$start = $this->log->get_time();
		$status = $this->db->commit();
		$this->log->add_message(get_class($this), 'commit()', $start, $status);
		return $status;
	}

	public function rollback() {
		$start = $this->log->get_time();
		$status = $this->db->rollback();
		$this->log->add_message(get_class($this), 'rollback()', $start, $status);
		return $status;
	}

	public function connect() {
		$start = $this->log->get_time();
		$this->resourse = $this->db->connect($this->host, $this->login, $this->pass, $this->db_name);
		$status = is_resource($this->resourse) || is_object($this->resourse);
		$this->log->add_message(get_class($this), 'connect()', $start, $status);
		return $status;
	}

	public function get(&$obj, &$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
		$start = $this->log->get_time();
		$status = $this->db->get($obj, $ret, $params, $orders, $offset, $limit, $groupby);
		$this->log->add_message(get_class($this), 'get()', $start, $status);
		return $status;
	}

	public function get_num(&$obj, &$ret, $params, $groupby = null) {
		$start = $this->log->get_time();
		$status = $this->db->get_num($obj, $ret, $params, $groupby);
		$this->log->add_message(get_class($this), 'get()', $start, $status);
		return $status;
	}

	public function add(&$obj, &$ret, $params) {
		$start = $this->log->get_time();
		$status = $this->db->add($obj, $ret, $params);
		$this->log->add_message(get_class($this), 'add()', $start, $status);
		return $status;
	}

	public function add_from_query($query) {
		$start = $this->log->get_time();
		$status = $this->db->add_from_query($query);
		$this->log->add_message(get_class($this), 'add()', $start, $status);
		return $status;
	}

	public function set_from_query($query) {
		$start = $this->log->get_time();
		$status = $this->db->set_from_query($query);
		$this->log->add_message(get_class($this), 'set()', $start, $status);
		return $status;
	}

	public function set(&$obj, &$ret, $params, $id) {
		$start = $this->log->get_time();
		$status = $this->db->set($obj, $ret, $params, $id);
		$this->log->add_message(get_class($this), 'set()', $start, $status);
		return $status;
	}

	public function set_where(&$obj, &$ret, $params, $where) {
		$start = $this->log->get_time();
		$status = $this->db->set_where($obj, $ret, $params, $where);
		$this->log->add_message(get_class($this), 'set_where()', $start, $status);
		return $status;
	}

	public function del(&$obj, &$ret, $id) {
		$start = $this->log->get_time();
		$status = $this->db->del($obj, $ret, $id);
		$this->log->add_message(get_class($this), 'del()', $start, $status);
		return $status;
	}

	public function del_where(&$obj, &$ret, $where) {
		$start = $this->log->get_time();
		$status = $this->db->del_where($obj, $ret, $where);
		$this->log->add_message(get_class($this), 'del_where()', $start, $status);
		return $status;
	}

	public function create_table(&$obj, &$ret, $fields) {
		$start = $this->log->get_time();
		$status = $this->db->create_table($obj, $ret, $fields);
		$this->log->add_message(get_class($this), 'create_table()', $start, $status);
		return $status;
	}

	public function affected_rows(&$obj) {
		$start = $this->log->get_time();
		$status = $this->db->affected_rows();
		$this->log->add_message(get_class($this), 'affected_rows()', $start, $status);
		return $status;
	}
}

class VPA_DB_drivers_factory {
	public $log;
	public function __construct() {
		$this->log = VPA_logger::getInstance();
	}

	public function init($type) {
		$start = $this->log->get_time();
		$class_name = 'VPA_DB_driver_' . $type;
		$obj = new $class_name;
		$status = is_object($obj) ? true : false;
		$this->log->add_message(get_class($this), 'init()', $start, $status);
		return $obj;
	}
}

/**
 * Класс для работы с виртуальной таблицей
 */
class VPA_table {
	public $interface = null;
	public $sequence = null;
	public $fields = array();
	public $where = array();
	public $triggers = array();
	public $name;
	public $memcache;
	public $db_type = DB_TYPE;
	public $primary_key;
	public $sql;
	public $log;
	public $use_cache = false;
	public $reset_cache = false;
	public $from_cache_only = false;
	public $cache_group = null;
	public $cache_lifetime = 0;
	public $old_params = null;
	public $schema = 'public';
	public $query = false;
	public $sql_query = '';
	/**
	 * Sphinx class
	 *
	 * @var VPA_sphinx
	 */
	private $sphinx_object;
	/**
	 * Groups for visual view of edit forms
	 * Sturcture:
	 * group_name => {type_group(vertical or horizontal),{name_field1,name_field2,...}}
	 */
	public $vgroups = array();
	// group fields for search in admin interface
	public $sgroup = null;

	public function __construct($name = null) {
		$this->name = $name;

		$this->log = VPA_logger::getInstance();
		$this->memcache = VPA_memcache::getInstance();
	}

	/**
	 * Задает нужно ли использовать кеширование для данной таблицы
	 */
	public function set_use_cache($value) {
		$this->use_cache = $value ? true : false;
	}

	/**
	 * Задает нужно брать ли результат только из кеша
	 */
	public function set_from_cache_only($value) {
		$this->from_cache_only = $value ? true : false;
	}

	/**
	 * Сбросить кеш
	 */
	public function set_reset_cache($value) {
		$this->reset_cache = $value ? true : false;
	}

	/**
	 * Задает время жизни кеша для данной таблицы
	 */
	public function set_cache_lifetime($value) {
		$this->cache_lifetime = $value;
	}

	/*
	* задает группу кеширования: набор полей по которым нужно делать кеширование
	*/
	public function set_cache_group($group) {
		$this->cache_group = explode("|", $group);
	}

	/**
	 * Задает имя схемы для таблицы (работает только в PostgreSQL)
	 */
	public function set_schema($value) {
		$this->schema = $value;
	}

	public function set_interface(&$obj) {
		$this->interface = $obj;
	}

	public function set_as_query($query) {
		$this->sql_query = $query;
		$this->query = true;
	}

	/**
	 * Задает  название последовательности первичного ключа (работает только в PostgreSQL)
	 */
	public function set_sequence($value) {
		$this->sequence = $value;
	}

	/**
	 * Задает  название типа БД
	 */
	public function set_db_type($value) {
		$this->db_type = $value;
	}

	public function set_primary_key($key) {
		$this->primary_key = $key;
	}

	public function set_sphinx_object($class_name) {
		$class_name = 'VPA_sphinx_' . $class_name;
		$this->sphinx_object = new $class_name;
	}

	public function get_sphinx_object() {
		return $this->sphinx_object;
	}

	public function get_fields() {
		return $this->fields;
	}

	public function add_vgroup($group_name, $group_type, $names) {
		$start = $this->log->get_time();
		$this->vgroups[$group_name] = array($group_type, $names);
		$this->log->add_message(get_class($this), "add_vgroup('$group_name','$group_type',...)", $start, true);
		return true;
	}

	public function get_vgroups(&$groups) {
		$groups = $this->vgroups;
	}

	public function add_sgroup($names) {
		$start = $this->log->get_time();
		$this->sgroup = $names;
		$this->log->add_message(get_class($this), "add_sgroup(names,...)", $start, true);
		return true;
	}

	public function get_sgroup(&$group) {
		$group = $this->sgroup;
	}

	public function add_field($human_name, $name, $alias, $type, $user_type = array('type' => 'private')) {
		$start = $this->log->get_time();
		if (!isset($this->fields[$alias]) || !$this->fields[$alias]) {
			$this->fields[$alias] = new VPA_field($this, $human_name, $name, $alias, $type, $user_type);
			$this->log->add_message(get_class($this), "add_field('$human_name','$name','$alias'...)", $start, true);
			return true;
		}
		$this->log->add_message(get_class($this), "add_field('$name','$alias'...): такое поле уже существует", $start, false);
		return false;
	}

	public function add_where($name, $condition, $input_value = WHERE_STRING, $adding = true, $adding_method = 'AND', $replace_old = false) {
		$start = $this->log->get_time();
		if ($replace_old || (!isset($this->where[$name][$input_value]) || !$this->where[$name][$input_value])) {
			$this->where[$name][$input_value] = new VPA_where($name, $condition, $input_value, $adding, $adding_method);
			$this->log->add_message(get_class($this), "add_where('$name','$condition')", $start, true);
			return true;
		}
		$this->log->add_message(get_class($this), "add_where('$name','$condition'): такое условие уже существует", $start, true);
		return false;
	}

	public function add_trigger($name, $method, $actions) {
		$start = $this->log->get_time();
		if (!isset($this->triggers[$name]) || !$this->triggers[$name]) {
			$this->triggers[$name] = new VPA_trigger($name, $method, $actions);
			$this->log->add_message(get_class($this), "add_trigger('$name','$condition')", $start, true);
			return true;
		}
		$this->log->add_message(get_class($this), "add_trigger('$name','$condition'): такое условие уже существует", $start, true);
		return false;
	}

	/**
	 * убираем поля, не существующие в таблице БД
	 */
	public function alias2name(&$params) {
		$start = $this->log->get_time();
		$params_new = $params;

		foreach ($this->fields as $fi => $fv) {
			// if (isset($params[$fv->get_alias()]) && !empty($params[$fv->get_alias()])) {
			if (isset($params[$fv->get_alias()])) {
				if ($fv->get_alias() != $fv->get_name()) {
					unset($params_new[$fv->get_alias()]); // в списке параметров должны быть только реально существующие в таблице поля, поэтому алиасы удаляем
				}
				$params_new[$fv->get_name()] = $params[$fv->get_alias()];
			}
		}

		$params = $params_new;
		$this->log->add_message(get_class($this), "alias2name()", $start, true);
		return true;
	}

	/**
	 * проверяем, есть ли хоть один параметр для которого необходимо запускать трансформацию через PHP
	 * и если есть - то трансформируем
	 */
	public function transform($method, $action, &$params, $old_params = null) {
		$start = $this->log->get_time();
		$script_set_null = $user_set_null = array();
		if ($this->have_fields_type($method)) {
			$save_params = $params;
			foreach ($this->fields as $fi => $fv) {
				$key = $fv->get_name();
				if (!isset($params[$key])) {
					$params[$key] = null;
					$script_set_null[$key] = 1;
				}

				if (!$fv->$action($params[$key], $old_params)) {
					$this->log->add_message(get_class($this), "transform('$method','$action'): ошибка при трансформировании (обработчик php для поля " . $fv->get_name() . " не отработал)", $start, false);
					return false;
				}

				$keys = is_array($save_params) ? array_keys($save_params) : array();
				if (!in_array($key, $keys) && is_null($params[$key])) {
					unset($params[$key]);
				}
			}
		}
		$this->log->add_message(get_class($this), "transform('$method','$action')", $start, true);
		return true;
	}

	public function add_from_query($query) {
		$start = $this->log->get_time();
		$this->alias2name ($params);

		$this->sql = VPA_sql::getInstance();
		$status = $this->sql->add_from_query($query);

		if (!$status) {
			$this->log->add_message(get_class($this), "add(): ошибка при добавлении в БД", $start, false);
			return false;
		}

		return true;
	}

	public function set_from_query($query) {
		$start = $this->log->get_time();
		$this->alias2name ($params);

		$this->sql = VPA_sql::getInstance();
		$status = $this->sql->set_from_query($query);

		if (!$status) {
			$this->log->add_message(get_class($this), "set(): ошибка при добавлении в БД", $start, false);
			return false;
		}

		return true;
	}

	public function add(&$ret, $params) {
		$start = $this->log->get_time();
		$this->alias2name ($params);

		if (!$this->transform('php', 'add', $params)) {
			$this->log->add_message(get_class($this), "add(): ошибка при трансформации", $start, false);
			return false;
		}
		if (!$this->transform('trigger', 'add', $params)) {
			$this->log->add_message(get_class($this), "add(): Ошибка триггера", $start, false);
			return false;
		}

		$this->sql = &VPA_sql::getInstance();
		$this->sql->add($this, $ret, $params);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "add(): ошибка при добавлении в БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "add()", $start, true);
		return true;
	}


	/**
	 * смысла в операторе update задавать массу условий cписка нет, потому как практически нигде не используется
	 * поэтому в качестве последнего параметра передается номер изменяемой записи
	 */
	public function set(&$ret, $params, $id) {
		$start = $this->log->get_time();

		$this->alias2name ($params);
		if (!$this->transform('php', 'set', $params, $params)) {
			$this->log->add_message(get_class($this), "set($id): ошибка при трансформации", $start, false);
			return false;
		}
		if (!$this->transform('trigger', 'set', $params)) {
			$this->log->add_message(get_class($this), "set($id): Ошибка триггера", $start, false);
			return false;
		}

		$this->sql = VPA_sql::getInstance();
		$this->sql->set($this, $ret, $params, $id);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "set($id): ошибка при добавлении в БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "set($id)", $start, true);
		return true;
	}

	/**
	 * работает без триггеров !!!
	 */
	public function set_where(&$ret, $params, $where) {
		$start = $this->log->get_time();
		$this->alias2name ($params);

		$this->sql = VPA_sql::getInstance();
		$this->sql->set_where($this, $ret, $params, $where);
		{
			ob_start();
			var_dump($where);
			$str = ob_get_contents();
			ob_end_clean();
		}
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "set_where($str): ошибка при добавлении в БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "set_where($str)", $start, true);
		return true;
	}

	/**
	 */
	public function del(&$ret, $id) {
		$start = $this->log->get_time();
		$key = $this->primary_key;

		$this->alias2name ($params);
		if (!$this->transform('php', 'del', $params, $params)) {
			$this->log->add_message(get_class($this), "del($id): ошибка при трансформации", $start, false);
			return false;
		}
		if (!$this->transform('trigger', 'del', $params)) {
			$this->log->add_message(get_class($this), "del($id): Ошибка триггера", $start, false);
			return false;
		}

		$this->sql = VPA_sql::getInstance();
		$this->sql->del($this, $ret, $id);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "del($id): ошибка при удалении из БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "del($id)", $start, true);
		return true;
	}

	/**
	 * работает без триггеров !!!
	 */
	public function del_where(&$ret, $where) {
		$start = $this->log->get_time();
		$this->alias2name ($params);

		$this->sql = VPA_sql::getInstance();
		$this->sql->del_where($this, $ret, $where);
		{
			ob_start();
			var_dump($where);
			$str = ob_get_contents();
			ob_end_clean();
		}
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "del_where($str): ошибка при добавлении в БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "del_where($str)", $start, true);
		return true;
	}

	protected function get_key_hash($params, $orders, $offset, $limit, $groupby, $is_num = false) {
		$key = $this->get_cache_group_key($params, $orders, $offset, $limit, $is_num);
		return md5(($this->sql_query ? $this->sql_query : null) . ($key ? serialize($key) : null) . ($params ? serialize($params) : null) . ($groupby ? serialize($groupby) : null) . get_class($this) . ($is_num ? '_count': null));
	}

	protected function get_cache_group_key($params, $orders, $offset, $limit, $is_num = false) {
		$key = array();
		if ($this->cache_group) {
			foreach ($this->cache_group as $indx => $field) {
				if (is_array($params) && in_array($field, array_keys($params))) {
					$key[$field] = $params[$field];
				}
			}
		}
		if (!$is_num) {
			$key['limit'] = $limit;
			$key['offset'] = $offset;
			$key['orders'] = $orders;
		}
		sort($key);

		return $key;
	}

	public function get(&$ret, $params = null, $orders = null, $offset = null, $limit = null, $groupby = null) {
		if (!is_array($orders) && !empty($orders)) {
			$orders = array($orders);
		}
		if (!is_array($groupby) && !empty($groupby)) {
			$groupby = array($groupby);
		}

		$start = $this->log->get_time();
		$this->sql = &VPA_sql::getInstance();
		// -------- cache --------//
		$key_hash = $this->get_key_hash($params, $orders, $offset, $limit, $groupby);

		// before all tranforms - because data already transformed
		if ($this->from_cache_only) {
			$this->log->add_message(get_class($this), "get(): readed from cache", $start, true);
			$ret = $this->memcache->get($key_hash);
			return is_object($ret);
		}

		if ($this->use_cache) {
			if ($this->memcache->is($key_hash) && !$this->reset_cache) {
				$ret = $this->memcache->get($key_hash);
				$this->log->add_message(get_class($this), "get(): readed from cache", $start, true);
				return true;
			}
		}
		
		#if ($_COOKIE['DF'])
		#{
			#$this->memcache->delete($key_hash);
			#print '<pre>'.print_r($key_hash,1).'</pre>';die;
		#}
		
		// \\\\\\\\-------- cache --------//
		$this->sql->get($this, $ret, $params, $orders, $offset, $limit, $groupby);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "get(): ошибка при получении данных", $start, false);
			return false;
		}

		if ($this->have_fields_type('php')) { // проверяем, есть ли хоть один параметр для которого необходимо запускать трансформацию через PHP
			$results = array();
			$ret->get($results);
			reset($results);
			$fields_with_php = array();
			foreach ($this->fields as $fi => $fv) {
				$fv->have_type('php') && $fields_with_php[] = $fv;
			}
			while (list($indx, $row) = each($results)) {
				foreach ($fields_with_php as $fi => $fv) {
					!isset($row[$fv->get_alias()]) && $row[$fv->get_alias()] = null;

					if (!$fv->get($row[$fv->get_alias()])) {
						$this->log->add_message(get_class($this), "get(): ошибка при трансформации данных :" . $fv->get_alias(), $start, false);
						return false;
					}
				}
				$results[$indx] = $row;
			}
			if (!$ret->set($results)) {
				$this->log->add_message(get_class($this), "get(): ошибка при обновлении данных перед выдачей", $start, false);
				return false;
			}
		}
		if ($this->use_cache) {
			$this->memcache->set($key_hash, $ret, $this->cache_lifetime);
		}
		$this->log->add_message(get_class($this), "get()", $start, true);
		return true;
	}

	public function get_num(&$ret, $params = null, $groupby = null) {
		if (!is_array($groupby) && !empty($groupby)) {
			$groupby = array($groupby);
		}

		$start = $this->log->get_time();
		$this->sql = &VPA_sql::getInstance();
		// -------- cache --------//
		$key_hash = $this->get_key_hash($params, null, null, null, $groupby, true);

		// before all tranforms - because data already transformed
		if ($this->from_cache_only) {
			$this->log->add_message(get_class($this), "get_num(): readed from cache", $start, true);
			$ret = $this->memcache->get($key_hash);
			return is_object($ret);
		}

		if ($this->use_cache) {
			if ($this->memcache->is($key_hash) && !$this->reset_cache) {
				$ret = $this->memcache->get($key_hash);
				$this->log->add_message(get_class($this), "get_count(): readed from cache", $start, true);
				return true;
			}
		}
		// -------- cache --------//
		$this->sql->get_num($this, $ret, $params, $groupby);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "get_count(): ошибка при получении данных", $start, false);
			return false;
		}

		if ($this->use_cache) {
			$this->memcache->set($key_hash, $ret);
		}
		$this->log->add_message(get_class($this), "get_count()", $start, true);
		return true;
	}

	public function create_table() {
		$start = $this->log->get_time();
		$this->sql = VPA_sql::getInstance();

		if ($this->have_fields_type('sql')) {
			$fields = array();
			foreach ($this->fields as $fi => $fv) {
				$fv->have_type('sql') && $fields[$fv->name] = $fv->get_type('sql');
			}
		}

		$this->sql->create_table($this, $ret, $fields);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "create_table(): ошибка при удалении из БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "create_table()", $start, true);
		return true;
	}

	// проверяет в таблице наличие полей с заданным типом
	public function have_fields_type($type) {
		$start = $this->log->get_time();
		$flag = false;
		foreach ($this->fields as $indx => $key) {
			if ($key->have_type($type)) {
				$flag = true;
				break;
			}
		}
		$this->log->add_message(get_class($this), "have_fields_type('$type'):" . (($flag) ? "поля типа $type есть" : "полей типа $type нет"), $start, true);
		return $flag;
	}

	public function get_form_fields(&$ret) {
		$results = array();
		$fields = $this->get_fields();
		foreach($fields as $fkey => $field) {
			$name_of_field = $this->name . "::" . $field->name;
			$val = $field->user_type;
			$val['human_name'] = $field->human_name;
			$results[$name_of_field] = $val;
		}
		$ret = $results;
		return true;
	}

	// выполняет get, но результат будет только с нужными полями указаными в fields
	public function get_params(&$ret, $params, $orders, $offset = null, $limit = null, $groupby = null, $fields = null) {
		if ($fields) {
			$temp = $this->fields;
			foreach($this->fields as $k => &$v) {
				if (!in_array($v->alias, $fields)) unset($this->fields[$k]);
			}
		}
		$rett = $this->get($ret, $params, $orders, $offset, $limit, $groupby);
		if ($fields) {
			$this->fields = $temp;
		}
		return $rett;
	}

	public function affected_rows() {
		$start = $this->log->get_time();
		$this->sql = VPA_sql::getInstance();

		$rows = $this->sql->affected_rows($this);
		$this->log->add_message(get_class($this), "affected_rows(): " . $rows, $start, false);
		return $rows;
	}

	public function get_num_fetch($params = null, $groupby = null) {
		if (!$this->get_num($ret, $params, $groupby)) {
			return false;
		}
		$ret->get_first($ret);
		return (int)$ret['count'];
	}

	public function get_first_fetch($params = null, $orders = null, $offset = 0, $limit = 1, $groupby = null) {
		if (!$this->get($ret, $params, $orders, $offset, $limit, $groupby)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

    /**
     * @return array|bool
     */
    public function get_fetch($params = null, $orders = null, $offset = null, $limit = null, $groupby = null) {
		if (!$this->get($ret, $params, $orders, $offset, $limit, $groupby)) {
			return false;
		}
		$ret->get($ret);
		return $ret;
	}

	public function get_params_fetch($params = null, $orders = null, $offset = null, $limit = null, $groupby = null, $fields = null) {
		if (!$this->get_params($ret, $params, $orders, $offset, $limit, $groupby, $fields)) {
			return false;
		}
		$ret->get($ret);
		return $ret;
	}

	public function add_fetch($params) {
		if (!$this->add($ret, $params)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

	public function set_fetch($params, $id) {
		if (!$this->set($ret, $params, $id)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

	public function del_fetch($params, $id) {
		if (!$this->del($ret, $params, $id)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

	public function set_where_fetch($params, $where) {
		if (!$this->set_where($ret, $params, $where)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

	public function del_where_fetch($params, $where) {
		if (!$this->del_where($ret, $params, $where)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}
}

/**
 * Real-time indexes for sphinx support
 */
class VPA_sphinx_table {
	public $interface = null;
	public $sequence = null;
	public $fields = array();
	public $name;
	public $db_type = DB_TYPE;
	public $primary_key;
	public $sql;
	public $log;
	public $old_params = null;
	public $schema = 'public';
	public $query = false;
	public $where = array();

	private $mysql_table;
	private $sql_class;
	/**
	 * Groups for visual view of edit forms
	 * Sturcture:
	 * group_name => {type_group(vertical or horizontal),{name_field1,name_field2,...}}
	 */
	public $vgroups = array();
	// group fields for search in admin interface
	public $sgroup = null;

	public function __construct($name = null, $mysql_table = null, $sql_class = null) {
		$this->name = $name;

		$this->mysql_table = $mysql_table;
		if (!$this->mysql_table) {
			if (strpos($name, PROJECT_NAME) !== false) {
				$name = str_replace(PROJECT_NAME . '_', '', $name);
			}
			$this->mysql_table = substr($name, 0, 3) == 'rt_' ? substr($name, 3) : $name;
		}
		$this->sql_class = $sql_class ?: 'VPA_table_' . $this->mysql_table;

		$this->log = VPA_logger::getInstance();
		$this->memcache = VPA_memcache::getInstance();

		$this->set_db_type(DB_TYPE . '_sphinx');

		$this->set_primary_key('id');
	}

	/**
	 * Задает имя схемы для таблицы (работает только в PostgreSQL)
	 */
	public function set_schema($value) {
		$this->schema = $value;
	}

	public function set_interface(&$obj) {
		$this->interface = $obj;
	}

	/**
	 * Задает  название последовательности первичного ключа (работает только в PostgreSQL)
	 */
	public function set_sequence($value) {
		$this->sequence = $value;
	}

	/**
	 * Задает  название типа БД
	 */
	public function set_db_type($value) {
		$this->db_type = $value;
	}

	public function set_primary_key($key) {
		$this->primary_key = $key;
	}

	public function add_vgroup($group_name, $group_type, $names) {
		$start = $this->log->get_time();
		$this->vgroups[$group_name] = array($group_type, $names);
		$this->log->add_message(get_class($this), "add_vgroup('$group_name','$group_type',...)", $start, true);
		return true;
	}

	public function get_vgroups(&$groups) {
		$groups = $this->vgroups;
	}

	public function get_fields() {
		return $this->fields;
	}

	public function add_sgroup($names) {
		$start = $this->log->get_time();
		$this->sgroup = $names;
		$this->log->add_message(get_class($this), "add_sgroup(names,...)", $start, true);
		return true;
	}

	public function get_sgroup(&$group) {
		$group = $this->sgroup;
	}

	public function add_field($human_name, $name, $alias, $type, $user_type = array('type' => 'private')) {
		$start = $this->log->get_time();
		if (!isset($this->fields[$alias]) || !$this->fields[$alias]) {
			$this->fields[$alias] = new VPA_field($this, $human_name, $name, $alias, $type, $user_type);
			$this->log->add_message(get_class($this), "add_field('$human_name','$name','$alias'...)", $start, true);
			return true;
		}
		$this->log->add_message(get_class($this), "add_field('$name','$alias'...): такое поле уже существует", $start, false);
		return false;
	}

	public function add_where($name, $condition, $input_value = WHERE_STRING, $adding = true, $adding_method = 'AND', $replace_old = false) {
		$start = $this->log->get_time();
		if ($replace_old || (!isset($this->where[$name][$input_value]) || !$this->where[$name][$input_value])) {
			$this->where[$name][$input_value] = new VPA_where($name, $condition, $input_value, $adding, $adding_method);
			$this->log->add_message(get_class($this), "add_where('$name','$condition')", $start, true);
			return true;
		}
		$this->log->add_message(get_class($this), "add_where('$name','$condition'): такое условие уже существует", $start, true);
		return false;
	}

	/**
	 * убираем поля, не существующие в таблице БД
	 */
	public function alias2name(&$params) {
		$start = $this->log->get_time();
		$params_new = $params;

		foreach ($this->fields as $fi => $fv) {
			if (isset($params[$fv->get_alias()])) {
				if ($fv->get_alias() != $fv->get_name()) {
					unset($params_new[$fv->get_alias()]); // в списке параметров должны быть только реально существующие в таблице поля, поэтому алиасы удаляем
				}
				$params_new[$fv->get_name()] = $params[$fv->get_alias()];
			}
		}

		$params = $params_new;
		$this->log->add_message(get_class($this), "alias2name()", $start, true);
		return true;
	}

	/**
	 * проверяем, есть ли хоть один параметр для которого необходимо запускать трансформацию через PHP
	 * и если есть - то трансформируем
	 */
	public function transform($method, $action, &$params, $old_params = null) {
		$start = $this->log->get_time();
		$script_set_null = $user_set_null = array();
		if ($this->have_fields_type($method)) {
			$save_params = $params;
			foreach ($this->fields as $fi => $fv) {
				$key = $fv->get_name();
				if (!isset($params[$key])) {
					$params[$key] = null;
					$script_set_null[$key] = 1;
				}

				if (!$fv->$action($params[$key], $old_params)) {
					$this->log->add_message(get_class($this), "transform('$method','$action'): ошибка при трансформировании (обработчик php для поля " . $fv->get_name() . " не отработал)", $start, false);
					return false;
				}

				$keys = is_array($save_params) ? array_keys($save_params) : array();
				if (!in_array($key, $keys) && is_null($params[$key])) {
					unset($params[$key]);
				}
			}
		}
		$this->log->add_message(get_class($this), "transform('$method','$action')", $start, true);
		return true;
	}
	
	public function set(&$ret, $params, $id) {
		$start = $this->log->get_time();

		$this->alias2name ($params);

		$this->sql = VPA_sql::getInstance();
		$this->sql->set($this, $ret, $params, $id);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "set($id): ошибка при добавлении в БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "set($id)", $start, true);
		return true;
	}

	public function add(&$ret, $params) {
		$start = $this->log->get_time();
		$this->alias2name ($params);

		$this->sql = &VPA_sql::getInstance();
		$this->sql->add($this, $ret, $params);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "add(): ошибка при добавлении в БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "add()", $start, true);
		return true;
	}
	public function del(&$ret, $id) {
		$start = $this->log->get_time();
		$key = $this->primary_key;
		$this->alias2name ($params);

		$this->sql = VPA_sql::getInstance();
		$this->sql->del($this, $ret, $id);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "del($id): ошибка при удалении из БД", $start, false);
			return false;
		}

		$this->log->add_message(get_class($this), "del($id)", $start, true);
		return true;
	}

	public function get(&$ret, $params, $orders = null, $offset = null, $limit = null, $groupby = null) {
		if (!is_array($orders) && !empty($orders)) {
			$orders = array($orders);
		}
		if (!is_array($groupby) && !empty($groupby)) {
			$groupby = array($groupby);
		}

		$start = $this->log->get_time();
		$this->sql = &VPA_sql::getInstance();

		$this->sql->get($this, $ret, $params, $orders, $offset, $limit, $groupby);
		if (!is_object($ret)) {
			$this->log->add_message(get_class($this), "get(): ошибка при получении данных", $start, false);
			return false;
		}
		$this->log->add_message(get_class($this), "get()", $start, true);
		return true;
	}

	public function get_first_fetch($params = null, $orders = null, $offset = 0, $limit = 1, $groupby = null) {
		if (!$this->get($ret, $params, $orders, $offset, $limit, $groupby)) {
			return false;
		}
		$ret->get_first($ret);

		$o = new $this->sql_class;
		return $o->get_fetch(array('id' => $ret), $orders, 0, count($ids));
	}

	public function get_fetch($params = null, $orders = null, $offset = null, $limit = null, $groupby = null) {
		if (!$this->get($ret, $params, $orders, $offset, $limit, $groupby)) {
			return false;
		}
		$ret->get($ret);

		$ids = array();
		foreach ($ret as &$row) {
			$ids[] = $row['id'];
		}
		if (empty($ids)) {
			return null;
		}
		$o = new $this->sql_class;
		$orders = $orders ?: $orders = array(sprintf('FIELD(id, %s)', join(',', $ids)));

		return $o->get_fetch(array('id_in' => $ids), $orders, 0, count($ids));
	}

	public function add_fetch($params) {
		if (!$this->add($ret, $params)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

	public function del_fetch($params, $id) {
		if (!$this->del($ret, $params, $id)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}

	public function set_fetch($params, $id) {
		if (!$this->set($ret, $params, $id)) {
			return false;
		}
		$ret->get_first($ret);
		return $ret;
	}
}

class VPA_field {
	public $name;
	public $human_name; //name of field on human language for output in admin
	public $value;
	public $type;
	public $alias;
	public $user_type;
	public $parent;
	public $log;

	public function VPA_field($parent, $human_name, $name, $alias, $type, $user_type) {
		$this->name = $name;
		$this->human_name = $human_name;
		$this->alias = $alias;
		$this->user_type = $user_type;
		$this->parent = $parent;
		$this->type = new VPA_field_types($this, $type);
		$this->log = VPA_logger::getInstance();
	}

	public function get_alias() {
		return $this->alias;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_type($type) {
		return $this->type->get_type($type);
	}

	public function get(&$value) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "get('" . substr($value, 0, 15) . "')", $start, true);
		return $this->type->get($value);
	}

	public function set(&$value, $old_values) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "set('" . (!is_array($value) ? substr($value, 0, 15) : 'Array') . "')", $start, true);
		return $this->type->set($value, $old_values);
	}

	public function del(&$value, $old_values) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "del()", $start, true);
		return $this->type->del($value, $old_values);
	}

	public function add(&$value) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "add('$value')", $start, true);
		return $this->type->add($value);
	}

	public function remove() {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "remove()", $start, true);
		return true;
	}

	public function have_type($type) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "have_type('$type')", $start, true);
		return $this->type->have_type($type);
	}

	public function get_table() {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "get_table()", $start, true);
		return $this->parent;
	}
}

class VPA_trigger {
	public $name;
	public $method; // start trigger after or before make of action
	public $actions = array(); // array of actions from list: add,set,sel,del
	public $info = array(); // additional infrmation, what need for trigger
	public function VPA_trigger($name, $method, $actions, $info) {
		$this->name = $name;
		$this->method = $method;
		$this->actions = $actions;
		$this->info = $info;
	}
}

class VPA_where {
	public $name;
	public $condition;
	public $type;
	public $adding; // нужно ли объединять условия (если TRUE - то да, FALSE - массив условий будет обнулен)
	public $adding_method; // метод объединения: AND или OR
	public function VPA_where($name, $condition, $type = WHERE_STRING, $adding = true, $adding_method = 'AND') {
		$this->name = $name;
		$this->condition = $condition;
		$this->type = $type;
		$this->adding = $adding;
		$this->adding_method = $adding_method;
	}
}

class VPA_field_types {
	public $type;
	public $handlers;
	public $parent;
	public $log;

	public function VPA_field_types($parent, $types) {
		$this->type = $types;
		$this->parent = $parent;
		$this->log = VPA_logger::getInstance();
	}

	public function get_field() {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "get_field()", $start, true);
		return $this->parent;
	}

	public function get_type($type) {
		return $this->type[$type];
	}

	public function add(&$value) {
		$start = $this->log->get_time();
		if (isset($this->type['php']) && !empty($this->type['php']) && isset($this->type['class'])) {
			$name = $this->type['php'];
			$func = &VPA_field_function_handler::getInstance($this->type['class']);
			if (!$func->exec($name, $this, 'add', $value, null)) {
				$this->log->add_message(get_class($this), "add('$value'): function error", $start, false);
				return false;
			}
		}
		$this->log->add_message(get_class($this), "add('" . ((is_string($value)) ? substr($value, 0, 15) : 'Array') . "')", $start, true);
		return true;
	}

	public function set(&$value, $old_values) {
		$start = $this->log->get_time();
		if (isset($this->type['php']) && !empty($this->type['php']) && isset($this->type['class'])) {
			$name = $this->type['php'];
			$func = &VPA_field_function_handler::getInstance($this->type['class']);
			if (!$func->exec($name, $this, 'set', $value, $old_values)) {
				$this->log->add_message(get_class($this), "set('$value'): function error", $start, false);
				return false;
			}
		}
		$v = !is_array($value) ? substr($value, 0, 15) : $value;
		$this->log->add_message(get_class($this), "add('" . $v . "')", $start, true);
		return true;
	}

	public function get(&$value) {
		$start = $this->log->get_time();
		if (isset($this->type['php']) && !empty($this->type['php']) && isset($this->type['class'])) {
			$name = $this->type['php'];
			$func = &VPA_field_function_handler::getInstance($this->type['class']);
			if (!$func->exec($name, $this, 'get', $value, null)) {
				$this->log->add_message(get_class($this), "get('" . substr($value, 0, 15) . "'): function error", $start, false);
				return false;
			}
		}
		$this->log->add_message(get_class($this), "get('" . substr($value, 0, 15) . "')", $start, true);
		return true;
	}

	public function del(&$value, $old_values) {
		$start = $this->log->get_time();
		if (isset($this->type['php']) && !empty($this->type['php']) && isset($this->type['class'])) {
			$name = $this->type['php'];
			$func = &VPA_field_function_handler::getInstance($this->type['class']);
			if (!$func->exec($name, $this, 'del', $value, $old_values)) {
				$this->log->add_message(get_class($this), "del(): function error", $start, false);
				return false;
			}
		}
		$this->log->add_message(get_class($this), "del()", $start, true);
		return true;
	}

	public function have_type($type) {
		$start = $this->log->get_time();
		$status = isset($this->type[$type]) ? true : false;
		$this->log->add_message(get_class($this), "have_type('$type'): " . ($status ? 'true':'false'), $start, true);
		return $status;
	}
}

/**
 * содержит все функции-обработчики для полей таблицы, если у них указан тип php
 */
class VPA_field_function_handler {
	public $log;
	public $plugin_dir;
	public $plugins;
	public $class;

	public function VPA_field_function_handler($class) {
		$this->log = VPA_logger::getInstance();
		$this->plugin_dir = VPA_PLUGIN_DIR;
		$this->class = $class;
		// $this->load_plugins();
	}

	public function &getInstance($class) {
		static $instance;
		if (!isset($instance)) $instance = new VPA_field_function_handler($class);
		return $instance;
	}

	public function exec($func_name, $object, $action, &$value, $old_values) {
		$start = $this->log->get_time();
		$class_name = 'VPA_function_plugin_' . $func_name;
		if (!class_exists($class_name)) {
			$plugin_file = $this->plugin_dir . $func_name . '.plugin.php';
			if (!is_file($plugin_file)) {
				$this->log->add_message(get_class($this), "exec(plugin file '$plugin_file' not found)", $start, false);
				return false;
			}
			include_once($plugin_file);
		}
		if (!class_exists($class_name)) {
			$this->log->add_message(get_class($this), "exec(class '$class_name' not defined)", $start, false);
			return false;
		}

		if (!isset($plugins[$func_name])) {
			$plugins[$func_name] = new $class_name($this->class);
		}
		$plugins[$func_name]->init($object, $old_values);
		$plugins[$func_name]->$action($value);
		return true;
	}
}

class VPA_function_plugin {
	public $log;
	public $object;
	public $old_values;
	public $class;

	public function VPA_function_plugin($class) {
		$this->log = VPA_logger::getInstance();
		$this->class = $class;
	}

	public function init($object, $old_values) {
		$this->log = VPA_logger::getInstance();
		$this->object = $object;
		$this->old_values = $old_values;
	}

	public function add(&$value) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "add() succeful", $start, true);
		return true;
	}

	public function set(&$value) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "set() succeful", $start, true);
		return true;
	}

	public function del(&$value) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "del succeful", $start, true);
		return true;
	}

	public function get(&$value) {
		$start = $this->log->get_time();
		$this->log->add_message(get_class($this), "get() succeful", $start, true);
		return true;
	}
}

class VPA_iterator {
	public $results;
	public $length;

	public function VPA_iterator(&$results) {
		$this->results = $results;
		$this->length = count($results);
	}

	public function get(&$ret) {
		$ret = $this->results;
		return true;
	}

	public function set(&$ret) {
		$this->results = $ret;
		return true;
	}

	public function get_first(&$ret) {
		if (!empty($this->results)) {
			reset($this->results);
			$ret = current($this->results);
		} else {
			$ret = null;
		}
		return true;
	}

	public function len() {
		return $this->length;
	}
}
