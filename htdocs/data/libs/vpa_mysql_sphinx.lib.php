<?php

require_once __DIR__ . '/vpa_mysql.lib.php';

define('MYSQL_SPHINX_ATTR', 1);
define('MYSQL_SPHINX_FIELD', 2);

class VPA_DB_driver_mysql_sphinx extends VPA_DB_driver_mysql {
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
		// sphinx only
		$field_weights = array();
		foreach ($obj->fields as $indx => $key) {
			if ($key->have_type('sphinx_field_weights')) {
				$field_weights[] = $key->get_name() . '=' . $key->get_type('sphinx_field_weights');
			}
			// this is a fields, not attrs
			if ($key->have_type('sphinx') && $key->get_type('sphinx') == MYSQL_SPHINX_FIELD) {
				continue;
			}
			
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
		
		// sphinx only
		$options = array();
		if (!empty($field_weights)) {
			$options[] = 'field_weights=(' . join(',', $field_weights) . ')';
		}
		if (!empty($options)) {
			$options = ' OPTION ' . join(' ', $options);
		}

		if (!$obj->query) {
			$query_string = "SELECT " . join(",", $fields) . " FROM " . $obj->name . $str . $group_string . $orders_string . $limit_string . $options;
		} else {
			$sql_str = $obj->sql_query;
			if (!empty($params)) {
				foreach ($params as $indx => $key) {
					$sql_str = str_replace('|' . $indx . '|', $key, $sql_str);
				}
			}
			$query_string = $sql_str . $str . $group_string . $orders_string . $limit_string . $options;
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

	public function set(&$obj, &$ret, $params, $id) {
		$start = $this->log->get_time();
		$fields_tb = $obj->get_fields();

		$id = (int)$id;

		if (!isset($id) || !is_int($id)) {
			$this->log->add_message(get_class($this), "set('$id'): не задан ID для записи", $start, false);
			return false;
		}

		$fields = array('id');
		$values = array($id);

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
				$fields[] = $key;
				$values[] = $value;
			} elseif (is_null($value)) {
				$fields[] = $key;
				$values[] = 'NULL';
			}
		}

		$pk = $obj->primary_key;
		$query_string = sprintf('REPLACE INTO %s (%s) VALUES (%s)', $obj->name, join(", ", $fields), join(", ", $values));

		if (empty($fields)) {
			$results = array($id);
			$ret = new VPA_iterator($results);
			$this->log->add_message(get_class($this), "set('$query_string'): нечего менять", $start, true);
			return true;
		}
		if (SHOW_QUERY && VPA_template::isDeveloper()) {
			var_dump($query_string);
		}
		if (!($query = mysql_query($query_string, $this->dbconn))) {
			$this->log->add_message(get_class($this), "set('$query_string'): запрос не прошел", $start, false);
			return false;
		}

		$results = array($id);
		$ret = new VPA_iterator($results);
		$this->log->add_message(get_class($this), "set('$query_string')", $start, true);
		return true;
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
						list($trigrams, $minLength, $maxLength) = akSphinx::getSuggestOptimalParams(strlen($key));
						$condition = strtr($where->condition, array('$' => $key, '%' => join(' ', akSphinx::getTrigrams($key)), '#' => $trigrams));
						
						if ($where->adding) {
							$str .= ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
						} else {
							$str = ($i < $len-1) ? $condition . ' ' . $where->adding_method . ' ' : $condition;
						}

						if ($condition) break;
					}
					$i++;
				}
			}
		}
		return $str;
	}
}
