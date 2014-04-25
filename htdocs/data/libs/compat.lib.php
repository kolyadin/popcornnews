<?php

if (!function_exists('mysql_fetch_all')) {
	function mysql_fetch_all ($result) {
		$ret = array();
		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = $row;
		}
		return $ret;
	}
}

if (!function_exists('mysqli_fetch_all')) {
	function mysqli_fetch_all ($result) {
		$ret = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$ret[] = $row;
		}
		return $ret;
	}
}

function utf8_compliant($str) {
	if (strlen($str) == 0) return true;
	return (preg_match('/^.{1}/us', $str) == 1);
}

if (!function_exists('json_encode')) {
	function json_encode($a = false) {
		return _json_encode($a);
	}
}

function _json_encode($a = false) {
	if (is_null($a)) return 'null';
	if ($a === false) return 'false';
	if ($a === true) return 'true';
	if (is_scalar($a)) {
		if (is_float($a)) {
			// Always use "." for floats.
			return floatval(str_replace(",", ".", strval($a)));
		}

		if (is_string($a)) {
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		} else
			return $a;
	}
	$isList = true;
	for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
		if (key($a) !== $i) {
			$isList = false;
			break;
		}
	}
	$result = array();
	if ($isList) {
		foreach ($a as $v) $result[] = _json_encode($v);
		return '[' . join(',', $result) . ']';
	} else {
		foreach ($a as $k => $v) $result[] = _json_encode($k) . ':' . _json_encode($v);
		return '{' . join(',', $result) . '}';
	}
}

/**
 * sprintf to mysql_query
 *
 * @param string $formatedString - vstring for sprintf
 * @param mixed $arg1 - arg for vsprintf
 * @param mixed $arg2 - arg for vsprintf
 * @param mixed $argN - arg for vsprintf
 * @return resourse
 */
function mysql_sprintf() {
	$args = func_get_args();
	$formatedString = array_shift($args);

	$q = vsprintf($formatedString, $args);
	if (!$q) return false;

	return mysql_query($q);
}

/**
 * Return first column in row
 *
 * @param resource $resource - mysql resource
 * @return mixed
 */
function mysql_fetch_first_column($resource) {
	if (!is_resource($resource)) return false;

	list($row) = mysql_fetch_row($resource);
	return $row;
}

/**
 * Mysql AND
 *
 * @param array $array - data to join
 * @return string
 */
function mysql_and_join($array) {
	if (!is_array($array) || empty($array)) return false;

	$q = '';
	foreach ($array as $k => $v) {
		if (preg_match('@=|<|>|LIKE|REGEXP@Uis', $k)) {
			$q .= sprintf('%s "%s" AND ', $k, mysql_real_escape_string($v));
		} else {
			$q .= sprintf('%s = "%s" AND ', $k, mysql_real_escape_string($v));
		}
	}
	return substr($q, 0, -5);
}

/**
 * Iconv for all
 *
 * @param mixed $data - data to iconv
 * @return mixed
 */
function mixed_iconv($data, $in_charset, $out_charset) {
	if (!is_scalar($data)) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = mixed_iconv($value, $in_charset, $out_charset);
			}
		} elseif (is_object($data)) {
			foreach ($data as $key => $value) {
				$data->$key = mixed_iconv($value, $in_charset, $out_charset);
			}
		}

		return $data;
	}

	return iconv($in_charset, $out_charset, $data);
}

/**
 * Search value in array
 *
 * @param array $q - query to search
 * @param array $array - array
 * @param string $case_sensitive - case sensitive
 * @return mixed - key
 */
function array_full_search($q, $array, $case_sensitive = false) {
	if (is_array($array)) {
		foreach ($array as $key => &$value) {
			if (($case_sensitive && strpos($value, $q) !== false) || (!$case_sensitive && stripos($value, $q) !== false)) {
				return $key;
			}
		}
	}
	return false;
}

/**
 * Clever array values
 *
 * @param array $array - array
 * @param bool $customKey - custom key
 * @param bool $notSetsElements - not sets elements too
 * @return array
 *
 * array
  0 =>
    array
      'nid' => string '99182' (length=5)
  1 =>
    array
      'nid' => string '97363' (length=5)
  2 =>
    array
      'nid' => string '97350' (length=5)
  3 =>
    array
      'nid' => string '97010' (length=5)
  4 =>
    array
      'nid' => string '100947' (length=6)
 *
 * Returns:
 *	array('99182', '97363', '97350', '97010', '100947');
 */
function clever_array_values($array, $customKey = null, $notSetsElements = false) {
	if (!is_array($array)) return false;

	$out = array();
	foreach ($array as &$val) {
		if (is_null($customKey)) {
			$t = array_values($val);
			if ($notSetsElements || isset($t[0])) {
				$out[] = $t[0];
			}
		} elseif ($notSetsElements || isset($val[$customKey])) {
			$out[] = $val[$customKey];
		}
	}
	return $out;
}

/**
 * Clever array values
 *
 * @param array $array - array
 * @param bool $keys - keys
 * @param bool $notSetsElements - not sets elements too
 * @return array
 */
function array_custom_values(&$array, array $keys = null, $notSetsElements = false) {
	if (!is_array($array)) return false;

	$out = array();
	foreach ($array as $i => &$val) {
		foreach ($keys as $key) {
			if ($notSetsElements || isset($val[$key])) {
				$out[$i][$key] = $val[$key];
			}
		}
	}
	return $out;
}

/**
 * Generate random string
 *
 * @param int $length - length of destination string
 * @return string
 */
function rand_str($length = 11) {
	$dst = '';
	for ($i = 0; $i < $length; $i++) {
		// from a to b
		if (rand(1, 2) % 2) $dst .= chr(rand(97, 122));
		// from 0 to 9
		else $dst .= rand(1, 9);
	}
	return $dst;
}

/**
 * Generate random file name
 *
 * @param string $dir - dir to check for file exists
 * @param string $ext - extension of file
 * @param int $length - file name length
 * @return string
 */
function random_file_name($dir, $ext = 'tmp', $length = 10) {
	if ($ext) $ext = '.' . $ext;

	$filename = sprintf('%s%s', rand_str($length), $ext);
	while (file_exists(sprintf('%s/%s', $dir, $filename))) {
		$filename = sprintf('%s%s', rand_str($length), $ext);
	}
	return $filename;
}

/**
 * Split image meta data
 *
 * @param string $path
 * @return bool
 */
function split_image($path) {
	if (!$path || !file_exists($path)) return false;

	exec(sprintf('%s --strip-all "%s"', JPEGOPTIM_BIN, str_replace('"', '\"', $path)), $output, $returnVar);
	return $returnVar === 0 ? true : false;
}

/**
 * Возращает кол-во запросов в очереди, или false при ошибке
 *
 * @return integer, false in error
 */
function how_many_query() {
	$db = new VPA_DB_driver_mysql();
	$db->connect(DB_HOST, DB_LOGIN, DB_PASS, DB_NAME);

	if (!$result= mysql_query('SHOW PROCESSLIST', $db->get_db_conn())) return false;
	$count = 0;
	while (mysql_fetch_row($result)) {
		$count++;
		if ($count > 500) return $count;
	}
	unset($db);
	return $count;
}

/**
 * показывает кол-во занятой памяти этим скриптом в данное время
 * @param human - в удобном виде (mb, gb, и т.д.)
 * @param as_float - возвращать в неокргуленном(более подробном) виде
 * @return mixed
 */
function how_many_memory_allocated($as_float = true, $human = true) {
	$memory = memory_get_usage();
	if (!$human) return $memory;

	$i = 1;
	while ($memory / 1024 > 1) {
		$memory = $memory / 1024;
		switch ($i) {
			case '1' : $memory_str = 'kb';
				break;
			case '2' : $memory_str = 'mb';
				break;
			case '3' : $memory_str = 'gb';
				break;
		}
		$i++;
		if ($i > 3) return null;
	}

	return (!$as_float? ceil($memory) : $memory) . $memory_str;
}

/**
 * Послать сообщение встроенное в шаблон
 */
function mail_with_tpl($to, $title, $message) {
	$subject = 'Popcornnews.ru (no-replay)';
	$search = array('<#title>', '<#message>');
	$replace = array($title, $message);
	$text = str_replace($search, $replace, file_get_contents(MAIL_TPL));

	if (!class_exists('html_mime_mail')) {
		require_once LIB_DIR . 'vpa_mail.lib.php';
	}

	return html_mime_mail::getInstance()->quick_send($to, $subject, $text);
}

/**
 * Delete empty elements from array
 */
function array_not_empty($array) {
	$out = array();

	foreach ($array as $k => $v) {
		if ($v) $out[$k] = $v;
	}
	return $out;
}

function str_replace_first($search, $replace, $subject) {
	$pos = strpos($subject, $search);
	if ($pos !== false) {
		$subject = substr_replace($subject, $replace, $pos, strlen($search));
	}
	return $subject;
}

/**
 * Drop from string more than one white spaces (\n| |\t => pcre \s)
 * 
 * @param string $str
 * @return string|null
 */
function trimd($str) {
	if (empty($str)) {
		return null;
	}
	return trim(preg_replace('@\s{1,}@s', ' ', $str));
}