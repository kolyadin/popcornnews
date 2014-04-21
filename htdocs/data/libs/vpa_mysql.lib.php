<?php

define('INT', 'INT');
define('OID', 'INT');
define('BOOL', 'BOOL');
define('FLOAT', 'FLOAT');
define('CHAR', 'VARCHAR');
define('TEXT', 'TEXT');
define('LO', 'MEDIUMBLOB');
define('TIMESTAMP', 'TIMESTAMP');

class VPA_DB_driver_mysql {
	public $sql;
	public $last_insert_id = 0;
	public $log;
	public $db;
	protected $dbconn;


	public function __construct() {
		$this->log = VPA_logger::getInstance();
	}

	public function connect($host, $login, $pass, $db) {
		$start = $this->log->get_time();
		if (!($ret = mysql_connect($host, $login, $pass)) || !mysql_select_db($db)) {
			$this->log->add_message(get_class($this), 'connect()', $start, false);
			return false;
		}
		$this->db = $db;
		$this->dbconn = $ret;
		mysql_query('SET NAMES ' . DB_CHARSET, $this->dbconn);
		$this->log->add_message(get_class($this), 'connect()', $start, true);
		return $ret;
	}

	public function add(&$obj, &$ret, $params) {
		$start = $this->log->get_time();
		$fields = $obj->get_fields();
		foreach ($params as $key => $value) {
			if ($value != '?n?') {
				$type = isset($fields[$key]) ? $fields[$key]->type->type['sql'] : '';

				/*if (get_magic_quotes_gpc())
				{
					$value = stripslashes($value);
				}*/

				if (!is_int($value) && $type != 'INT' && $type != 'MEDIUMBLOB') {
					$value = "'" . mysql_escape_string($value) . "'";
				} elseif ($type == 'MEDIUMBLOB') {
					$value = "'" . mysql_escape_string($value) . "'";
				}

				$params[$key] = $value;
			} else {
				unset($params[$key]);
			}
		}

		$keys = array_keys($params);
		$query_string = sprintf('INSERT INTO %s (%s) VALUES (%s)', $obj->name, join(',', $keys), join(',', $params));
		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}

		$params_log = array();
		foreach ($params as $indx => $log) {
            if(!is_array($log)) {
			    $params_log[$indx] = substr($log, 0, 15);
            }
		}
		$query_string_log = sprintf('INSERT INTO %s (%s) VALUES (%s)', $obj->name, join(",", $keys), join(',', $params_log));

		if (!($query = mysql_query($query_string, $this->dbconn)) || !($id = mysql_insert_id($this->dbconn)) || mysql_error($this->dbconn)) {
			$this->log->add_message(get_class($this), 'add(\'' . (isset($query_string_log) ? $query_string_log : null) . '\'):' . mysql_error(), $start, false);
			return false;
		}

		$results = array($id);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "add('$query_string_log')", $start, true);
		return true;
	}

	/*
	 * добавить в базу из запроса на прямую
	 * без всяких дополнительных параметров
	 */
	public function add_from_query($query_string) {
		$start = $this->log->get_time();

		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), 'add(\'' . (isset($query_string) ? $query_string : null) . '\'):' . mysql_error(), $start, false);
			return false;
		}
		$this->log->add_message(get_class($this), "add('$query_string')", $start, true);
		return true;
	}

	/*
	 * изменение данные в базе из запроса на прямую
	 * без всяких дополнительных параметров
	 */
	public function set_from_query($query_string) {
		$start = $this->log->get_time();

		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), 'set(\'' . (isset($query_string) ? $query_string : null) . '\'):' . mysql_error(), $start, false);
			return false;
		}
		$this->log->add_message(get_class($this), "set('$query_string')", $start, true);
		return true;
	}

	public function set(&$obj, &$ret, $params, $id) {
		$start = $this->log->get_time();
		$fields_tb = $obj->get_fields();
		$fields = array();
		$id = (int)$id;
		if (!isset($id) || !is_int($id)) {
			$this->log->add_message(get_class($this), "set('$id'): не задан ID для записи", $start, false);
			return false;
		}
		foreach ($fields_tb as $s => $k) {
			$fields_tb[$k->name] = $fields_tb[$s];
		}
		foreach ($params as $key => $value) {
			if (isset($fields_tb[$key])) {
				$type = $fields_tb[$key]->type->type['sql'];
			} else {
				$type = 'TEXT';
			}
			if (!is_null($value) && ($value != '?n?' || is_int($value) || is_float($value))) {
				if ($type == 'BOOL') {
					$value = ($value) ? '1' : '0';
				}

				if (get_magic_quotes_gpc()) {
					$value = stripslashes($value);
				}

				if (!is_int($value) && $type != 'INT' && $type != 'MEDIUMBLOB') {
					$value = "'" . mysql_escape_string($value) . "'";
				} elseif ($type == 'MEDIUMBLOB') {
					$value = "'" . mysql_escape_string($value) . "'";
				} elseif (empty($value) && strlen($value) == 0) {
					$value = 'NULL';
				}
				$fields[] = "$key = $value";
			} elseif (is_null($value)) {
				$fields[] = "$key = NULL";
			}
		}

		$pk = $obj->primary_key;
		$query_string = sprintf('UPDATE %s SET %s WHERE %s = %d', $obj->name, join(", ", $fields), $pk, $id);
		if (empty($fields)) {
			$results = array($id);
			$ret = new VPA_iterator($results);
			$this->log->add_message(get_class($this), "set('$query_string'): нечего менять", $start, true);
			return true;
		}
		if (IS_NEED_LOCK) {
			$query_lock_string = 'LOCK TABLES ' . $obj->name;
			mysql_query($query_lock_string, $this->dbconn);
		}
		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}
		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "set('$query_string'): запрос не прошел", $start, false);
			return false;
		}
		if (IS_NEED_LOCK) {
			$query_unlock_string = 'UNLOCK TABLES';
			mysql_query($query_unlock_string, $this->dbconn);
		}

		$results = array($id);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "set('$query_string')", $start, true);
		return true;
	}

	public function set_where(&$obj, &$ret, $params, $where_params) {
		$start = $this->log->get_time();
		$fields_tb = $obj->get_fields();
		$fields = array();
		foreach ($params as $key => $value) {
			if (isset($fields_tb[$key])) {
				$type = $fields_tb[$key]->type->type['sql'];
			} else {
				$type = 'TEXT';
			}
			if (!is_null($value) && ($value != '?n?' || is_int($value) || is_float($value))) {
				if ($type == 'BOOL') {
					$value = ($value) ? '1' : '0';
				}

				if (get_magic_quotes_gpc()) {
					$value = stripslashes($value);
				}

				if (!is_int($value) && $type != 'INT' && $type != 'MEDIUMBLOB') {
					$value = "'" . mysql_escape_string($value) . "'";
				} elseif ($type == 'MEDIUMBLOB') {
					$value = "'" . mysql_escape_string($value) . "'";
				} elseif (empty($value) && strlen($value) == 0) {
					$value = 'NULL';
				}
				$fields[] = $key . ' = ' . $value;
			} elseif (is_null($value)) {
				$fields[] = $key . ' = NULL';
			}
		}

		$str = $this->params($where_params, $obj);
		if (!empty($str)) $str = ' WHERE ' . $str;
		$str = rtrim($str);
		$str = trim($str, 'AND');
		$str = trim($str, 'OR');

		if (IS_NEED_LOCK) {
			$query_lock_string = "LOCK TABLES " . $obj->name;
			mysql_query($query_lock_string, $this->dbconn);
		}
		$query_string = sprintf('UPDATE %s SET %s', $obj->name, join(", ", $fields) . $str);

		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}
		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "set('$query_string'): запрос не прошел", $start, false);
			return false;
		}
		if (IS_NEED_LOCK) {
			$query_unlock_string = 'UNLOCK TABLES';
			mysql_query($query_unlock_string, $this->dbconn);
		}

		$results = array(true);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "set('$query_string')", $start, true);
		return true;
	}

	public function del(&$obj, &$ret, $id) {
		$start = $this->log->get_time();
		$fields = array();
		$id = (int)$id;
		if (!$id) {
			$this->log->add_message(get_class($this), "del('$id'): не задан ID для удаления", $start, false);
			return false;
		}

		$pk = $obj->primary_key;
		$query_string = sprintf('DELETE FROM %s WHERE %s = %u', $obj->name, $pk, $id);
		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}

		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "del('$query_string'): запрос не прошел", $start, false);
			return false;
		}

		$results = array($id);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "del('$query_string')", $start, true);
		return true;
	}

	public function del_where(&$obj, &$ret, $where_params) {
		$start = $this->log->get_time();
		$fields = array();

		$str = $this->params($where_params, $obj);

		// if ($str) $str = ' WHERE ' . $str;
		// debug

		if (!trim($str)) {
			mail('azat@traf.spb.ru', 'trying to delete all', serialize($where_params));
			$data = array(
			    'ip' => $_SERVER['REMOTE_ADDR'],
			    'location' => $_SERVER['SCRIPT_URI'],
			    'post' => $_POST,
			    'get' => $_GET,
			    'table' => $obj->name,
			    'params' => $where_params,
			);
			file_put_contents('/tmp/delete_query_test', serialize($data), FILE_APPEND);
		}
		$str = ' WHERE ' . $str;

		$str = rtrim($str);
		$str = trim($str, 'AND');
		$str = trim($str, 'OR');

		$query_string = 'DELETE FROM ' . $obj->name . $str;
		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}

		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "del_where('$query_string'): запрос не прошел", $start, false);
			return false;
		}

		$results = array($where_params);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "del_where('$query_string')", $start, true);
		return true;
	}

	public function get(&$obj, &$ret, $params, $orders, $offset = null, $limit = null, $groupby = null) {
		$start = $this->log->get_time();
		$str = '';

		$str = $this->params($params, $obj);
		if (!empty($str)) $str = ' WHERE ' . $str;

		$str = rtrim($str);
		$str = trim($str, 'AND');
		$str = trim($str, 'OR');
		// ================= получение списка полей в таблице =================
		$fields = array();
		foreach ($obj->fields as $indx => $key) {
			if ($key->have_type('sql')) {
				$fields[] = $key->get_name() == $key->get_alias() ? $key->get_name() : $key->get_name() . ' AS ' . $key->get_alias();
			}
		}
		$fields = array_unique($fields);
		if (!count($fields)) $fields = array('*'); // даже не знаю, может случай, когда нет ни одного поля для выборки задать как ошибку
		// ====================================================================
		$orders_string = '';
		if (is_array($orders) && !empty($orders)) {
			$orders_string = ' ORDER BY ' . join(',', $orders);
		}

		$group_string = '';
		if (is_array($groupby) && !empty($groupby)) {
			$group_string = ' GROUP BY ' . mysql_real_escape_string(join(',', $groupby));
		}

		$limit_string = '';
		if (is_integer($limit)) {
			$offset = !empty($offset) ? (int)$offset : 0;
			$limit = (int)$limit;
			$limit_string .= " LIMIT $offset,$limit";
		}

		if (!$obj->query) {
			$query_string = "SELECT " . join(",", $fields) . " FROM " . $obj->name . $str . $group_string . $orders_string . $limit_string;
		} else {
			$sql_str = $obj->sql_query;
			if (!empty($params)) {
				foreach ($params as $indx => $key) {
					$sql_str = @str_replace('|' . $indx . '|', $key, $sql_str);
				}
			}
			$query_string = $sql_str . $str . $group_string . $orders_string . $limit_string;
		}
		$results = array();

		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}

		$query = mysql_query($query_string, $this->dbconn);
		if (!$query) {
			$this->log->add_message(get_class($this), "get('" . get_class($obj) . "','$query_string') недопустимый запрос ?", $start, false);
			$ret = new VPA_iterator($results);
			return false;
		}

		if (mysql_num_rows($query)) {
			$results = mysql_fetch_all($query);
			mysql_free_result($query);
		}
		$r_x = false;

		$ret = new VPA_iterator($results);
		$fields = $obj->get_fields();
		$start_parser = array();
		foreach ($fields as $key => $field) {
			if ($field->type->type['sql'] == 'OID') {
				$start_parser[] = $key;
			}
		}
		if (!empty($start_parser)) {
			$rets = array();
			$ret->get($rets);

			foreach ($rets as $indx => $record) {
				foreach ($start_parser as $findx => $fname) {
					$loid = $record[$fname];
					$rets[$indx][$fname] = $loid;
				}
			}
			$ret->set($rets);
		}
		$this->log->add_message(get_class($this), "get('" . get_class($obj) . "','$query_string')", $start, $r_x);
		return true;
	}

	public function get_num(&$obj, &$ret, $params, $groupby = null) {
		$start = $this->log->get_time();
		$str = '';
		$params = empty($params) ? array() : $params;

		$str = $this->params($params, $obj);
		if (!empty($str)) $str = ' WHERE ' . $str;
		$str = rtrim($str);
		$str = trim($str, 'AND');
		$str = trim($str, 'OR');
		// ================= получение списка полей в таблице =================
		$fields = array('count(*)');
		// ====================================================================
		$group_string = '';
		if (is_array($groupby) && !empty($groupby)) {
			$group_string = ' GROUP BY ' . mysql_real_escape_string(join(',', $groupby));
		}

		if (!$obj->query) {
			$query_string = 'SELECT count(*) AS count FROM ' . $obj->name . $str;
		} else {
			$sql_str = $obj->sql_query;
			$sql_str = preg_replace("/(SELECT )(.*)?( FROM.*)/is", "\\1count(*) AS count\\3", $sql_str);
			foreach ($params as $indx => $key) {
				$sql_str = preg_replace("/\|" . $indx . "\|/is", $key, $sql_str);
			}
			$query_string = $sql_str . $str;
		}
		$query_string .= $group_string;

		$results = array();

		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}

		$query = mysql_query($query_string, $this->dbconn);
		if (!$query) {
			$this->log->add_message(get_class($this), "get('$query_string') недопустимый запрос ?", $start, false);
			$ret = new VPA_iterator($results);
			return false;
		}

		if (mysql_num_rows($query)) {
			$results = mysql_fetch_all($query);
			mysql_free_result($query);
		}
		$r_x = false;

		$ret = new VPA_iterator($results);

		$fields = $obj->get_fields();
		$this->log->add_message(get_class($this), "get('$query_string')", $start, $r_x);
		return true;
	}

	public function table_exists($schema, $name) {
		$query_string = 'SHOW TABLES  LIKE \'' . $schema . '_' . $name . '\'';
		$query = mysql_query($this->dbconn, $query_string);
		$info = mysql_fetch_array($query, MYSQL_ASSOC);
		return (!empty($info)) ? true : false;
	}

	public function table_info($schema, $name) {
		$query = mysql_list_fields($this->db, $schema . '_' . $name);
		$info = pg_fetch_all($query);
		return $info;
	}

	public function create_table(&$obj, &$ret, $fields) {
		$start = $this->log->get_time();

		$pk = $obj->primary_key;
		$fs = array();
		foreach($fields as $name => $type) {
			$fs[] = $name . " " . $type . ($name == $pk ? ' PRIMARY KEY' . ($type == 'int' ? ' AUTO_INCREMENT' : '') : '');
		}
		// $query_string="CREATE TABLE IF NOT EXISTS ".$obj->name." (".join(", ",$fs).")";
		$query_string = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)',
			$obj->name, join(", ", $fs)
			);

		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "create_table('$query_string'): запрос не прошел", $start, false);
			return false;
		}

		$results = array(null);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "create_table('$query_string')", $start, true);
		return true;
	}

	public function drop_table(&$obj, &$ret) {
		$start = $this->log->get_time();
		if (!$this->table_exists($obj->schema, $obj->name)) {
			$this->log->add_message(get_class($this), "drop_table(" . $obj->name . "): таблица уже отсутствует", $start, false);
			return false;
		}

		$query_string = 'DROP TABLE ' . $obj->name;

		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "drop_table('$query_string'): Невозможно удалить таблицу на основании запроса", $start, false);
			return false;
		}

		$results = array(null);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "drop_table('$query_string')", $start, true);
		return true;
	}

	/**
	 * Клонирует заданную запись
	 */
	public function clone_record(&$obj, &$ret, $id, $params = null) {
		$start = $this->log->get_time();

		$obj->get($r, array('id' => $id), null, 0, 1);
		$r->get_first($info);
		unset($info[$obj->primary_key]);
		if (!empty($params) && is_array($params)) {
			foreach ($params as $key => $item) {
				if (isset($info[$key])) {
					$info[$key] = $item;
				}
			}
		}

		$this->begin();
		if (!$obj->add($ret, $info)) {
			$this->log->add_message(get_class($this), "cannot clone record N " . $id . ":" . pg_last_error(), $start, false);
			$this->rollback();
			return false;
		}
		$this->commit();
		$this->log->add_message(get_class($this), "clone_record('$id')", $start, true);
		return true;
	}

	public function begin() {
		$start = $this->log->get_time();
		if (empty($GLOBALS['base_class']->transaction)) {
			$query = mysql_query('BEGIN', $this->dbconn);
			if (!$query) {
				$this->log->add_message(get_class($this), "begin(): transaction not started", $start, false);
				return false;
			}
			$GLOBALS['base_class']->transaction = 1;
			$this->log->add_message(get_class($this), "begin(): transaction started succeful", $start, true);
			return true;
		} else {
			$GLOBALS['base_class']->transaction += 1;
			$this->log->add_message(get_class($this), "begin(): transaction alredy started", $start, true);
			return true;
		}
	}

	public function commit() {
		$start = $this->log->get_time();
		if ($GLOBALS['base_class']->transaction == 1) {
			$query = mysql_query('COMMIT', $this->dbconn);
			if (!$query) {
				$this->log->add_message(get_class($this), "commit(): transaction not ended", $start, false);
				return false;
			}
			$this->log->add_message(get_class($this), "commit(): transaction ended succeful", $start, true);
			$GLOBALS['base_class']->transaction = 0;
			return true;
		} elseif ($GLOBALS['base_class']->transaction > 1) {
			$GLOBALS['base_class']->transaction -= 1;
			$this->log->add_message(get_class($this), "commit(): transaction is nested", $start, true);
			return true;
		}
	}

	public function rollback() {
		$start = $this->log->get_time();
		if ($GLOBALS['base_class']->transaction == 1) {
			$query = mysql_query('ROLLBACK', $this->dbconn);
			if (!$query) {
				$this->log->add_message(get_class($this), "rollback(): rollback unaviable", $start, false);
				return false;
			}
			$this->log->add_message(get_class($this), "rollback(): rollback succeful", $start, true);
			$GLOBALS['base_class']->transaction = 0;
			return true;
		} elseif ($GLOBALS['base_class']->transaction > 1) {
			$GLOBALS['base_class']->transaction -= 1;
			$this->log->add_message(get_class($this), "rollback(): transaction is nested", $start, true);
			return true;
		}
	}

	public function affected_rows() {
		return mysql_affected_rows($this->dbconn);
	}

	public function get_db_conn() {
		return $this->dbconn;
	}

	/**
	 * Params
	 *
	 * @param array $where_params - all params
	 * @param object $obj - object of params
	 */
	protected function params($where_params, $obj) {
		$str = '';

		if (isset($where_params) && !empty($where_params)) {
			// смотрим количество чего меньше - передаваемых параметров или возможных условий
			$len = count($where_params);
			$len > count($obj->where) && $len = count($obj->where); // замена $len=$len>count ? count : $len
			$i = 0;

			foreach ($where_params as $indx => $key) {
				if (isset($obj->where[$indx]) && ($wheres = $obj->where[$indx])) {
					$condition = null;
					foreach ($wheres as $widx => $where) {
						// todo: rewrite this part on methods of class
						if (is_numeric($key) && !is_null($key) && $where->type &WHERE_INT) {
							$condition = str_replace('$', $key, $where->condition);
							if ($where->adding) {
								$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							} else {
								$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							}
						}
						if (is_string($key) && !is_null($key) && $key != '' && $where->type &WHERE_STRING) {
							$condition = str_replace('$', $key, $where->condition);
							if ($where->adding) {
								$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							} else {
								$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							}
						}
						if (is_array($key) && $where->type &WHERE_ARRAY) {
							$condition = str_replace('$', join(',', $key), $where->condition);
							if ($where->adding) {
								$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							} else {
								$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							}
						}
						if (is_array($key) && $where->type &WHERE_INTERVAL) {
							$condition = $where->condition;

							foreach ($key as $one_key) {
								$condition = str_replace_first('$', $one_key, $condition);
							}
							if ($where->adding) {
								$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							} else {
								$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							}
						}
						if ((is_null($key) || $key == '') && $where->type &WHERE_NULL) {
							$condition = str_replace('$', $key, $where->condition);
							if ($where->adding) {
								$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							} else {
								$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
							}
						}
//						if (is_string($key) && $where->type &WHERE_FIND) {
//							/*
//							Это особый тип данных, предназначен для работы поиска по нескольким словам в ряде полей(перечисляются через запятую в условии поиска) таблицы.
//							Для улучшения результатов поиска поддерживается доп. синтаксис:
//							+ перед словом гарантирует, что это слово будет присутствовать в результатах поиска
//							- перед словом гарантирует, что это слово будет отсутствовать в результатах поиска
//							TODO: перенести в MySQL
//							*/
//							$fields = explode(',', $where->condition);
//							$key = str_replace("-", " -", $key);
//							$key = str_replace("+", " +", $key);
//							$keys = explode(' ', trim($key));
//							$conditions = $conditions_w = array();
//							foreach ($fields as $f => $field) {
//								foreach ($keys as $k => $key) {
//									$l = substr($key, 0, 1);
//									$subkey = trim($key, '+-');
//									$conditions[] = ($l == '-') ? $field . " NOT LIKE '%" . $subkey . "%'" : $field . " LIKE '%" . $subkey . "%'";
//								}
//								$conditions_w[] = "(" . implode(' AND ', $conditions) . ")";
//								$conditions = array();
//							}
//
//							$condition = implode(' OR ', $conditions_w);
//
//							if ($where->adding) {
//								$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
//							} else {
//								$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
//							}
//						}

						if ($condition) break;
					}
					$i++;
				}
			}
		}
		return $str;
	}
}
