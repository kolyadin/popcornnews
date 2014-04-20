<?php
/*
 * Через этот класс делать только простые запросы!!
 */

class vpa_tpl_query {
	public function vpa_tpl_query() {}

	public function get($table, $where = null, $sort = null, $offset = null, $limit = null, $groupby = null, $use_cache = null, $reloadcache = null, $from_cache_only = null, $lifetime = null) {
		$class = 'VPA_table_' . $table;
		$tb = new $class;
		/*
		 * для перегенерации кеша
		 * сложных запросов
		 */
		if (!is_null($use_cache)) $tb->set_use_cache($use_cache);
		if (!is_null($reloadcache)) $tb->set_reset_cache($reloadcache);
		if (!is_null($from_cache_only)) $tb->set_from_cache_only($from_cache_only);
		if (!is_null($lifetime)) $tb->set_cache_lifetime($lifetime);

		$tb->get($ret, $where, $sort, $offset, $limit, $groupby);
		if (is_object($ret)) {
			$ret->get($records);
			unset($tb, $ret);
			return $records;
		}
		return false;
	}

	public function get_params($table, $where = null, $sort = null, $offset = null, $limit = null, $groupby = null, $fields, $use_cache = null, $reloadcache = null) {
		$class = 'VPA_table_' . $table;
		$tb = new $class;
		/*
		 * для перегенерации кеша
		 * сложных запросов
		 */
		if ($use_cache) $tb->set_use_cache($use_cache);
		if ($reloadcache) $tb->reset_cache = $reloadcache;

		$tb->get_params($ret, $where, $sort, $offset, $limit, $groupby, $fields);
		if (is_object($ret)) {
			$ret->get($records);
			unset($tb, $ret);
			return $records;
		}
		return false;
	}

	public function get_num($table, $where = null, $use_cache = null, $reloadcache = null, $from_cache_only = null, $lifetime = null) {
		$class = 'VPA_table_' . $table;
		$tb = new $class;
		/*
		 * для перегенерации кеша
		 * сложных запросов
		 */
		if (!is_null($use_cache)) $tb->set_use_cache($use_cache);
		if (!is_null($reloadcache)) $tb->set_reset_cache($reloadcache);
		if (!is_null($from_cache_only)) $tb->set_from_cache_only($from_cache_only);
		if (!is_null($lifetime)) $tb->set_cache_lifetime($lifetime);

		$tb->get_num($ret, $where);
		if (is_object($ret)) {
			$ret->get_first($records);
			unset($tb);
			return (int)$records['count'];
		}
		return false;
	}

	public function set($table, $params, $id) {
		$class = 'VPA_table_' . $table;
		$tb = new $class;
		$status = $tb->set($ret, $params, $id);
		unset($tb);
		return $status;
	}

	// lifetime - default 1 day
	public function get_query($query, $use_cache = null, $lifetime = 86400, $reloadcache = null, $from_cache_only = null) {
		$q = new VPA_table_query;

		if (!is_null($use_cache)) {
			$q->set_use_cache($use_cache);
			$q->set_cache_lifetime($lifetime);
		}
		if (!is_null($reloadcache)) $q->set_reset_cache($reloadcache);
		if (!is_null($from_cache_only)) $q->set_from_cache_only($from_cache_only);
		$q->set_as_query($query);

		$q->get($ret, null, null, null, null, null);
		if (is_object($ret)) {
			$ret->get($records);
			unset($q);
			return $records;
		}
		return false;
	}

	public function add_from_query($query) {
		$q = new VPA_table_query;
		$status = $q->add_from_query($query);
		unset($q);
		return $status;
	}

	public function set_from_query($query) {
		$q = new VPA_table_query;
		$status = $q->set_from_query($query);
		unset($q);
		return $status;
	}
}
