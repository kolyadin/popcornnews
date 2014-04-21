<?php
// $Id: base.class.php,v 1.8 2003/11/15 14:49:14 Andrey Pahomov Exp $
/**
 * иницализация класса base_api
 *
 * @author Пахомов Андрей
 * @version 1.01
 * @package base_engine
 */

/**
 * Bug Fixes:
 *
 *     01.02.2006 - поправлена ошибка в функции get_param при обработке POST данных в силу чего числовые значения =0 выдавались как null
 */

/**
 * Codes for form validation
 */
define ('FIELD_ANY', 0);
define ('FIELD_NOT_EMPTY', 1);
define ('FIELD_IS_STRING', 2);
define ('FIELD_IS_NUMBER', 4);
define ('FIELD_HAVE_PATTERN', 8);

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */

define('HTTP_STATUS_200', $_SERVER["SERVER_PROTOCOL"].' 200 Ok');

define('HTTP_STATUS_206', $_SERVER["SERVER_PROTOCOL"].' 206 Partial Content');

define('HTTP_STATUS_301', $_SERVER["SERVER_PROTOCOL"].' 301 Moved Permanently');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_303', $_SERVER["SERVER_PROTOCOL"].' 303 See other');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_304', $_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_400', $_SERVER["SERVER_PROTOCOL"].' 400 Bad Request');

define('HTTP_STATUS_401', $_SERVER["SERVER_PROTOCOL"].' 401 Unauthorized');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_403', $_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_404', $_SERVER["SERVER_PROTOCOL"].' 404 Not Found');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_500', $_SERVER["SERVER_PROTOCOL"].' 500 Internal Server Error');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_501', $_SERVER["SERVER_PROTOCOL"].' 501 Not Implemented');

/**
 * HTTP status messages
 *
 * should work only with mod_php, not CGI
 */
define('HTTP_STATUS_503', $_SERVER["SERVER_PROTOCOL"].' 503 Service Unavailable');

setlocale(LC_ALL, SITE_LOCALE);
// for print float variables like this "9.0", and like this "9,0"
setlocale(LC_NUMERIC, 'en_US.UTF-8');

header(CODEPAGE_HEADER);

require_once (LIB_DIR . "vpa_template.lib.php");
require_once (LIB_DIR . "vpa_logger.lib.php");
require_once (LIB_DIR . "vpa_engine.lib.php");
require_once (LIB_DIR . "vpa_memcache.lib.php");
require_once (LIB_DIR . "vpa_messages.lib.php");
require_once (LIB_DIR . "vpa_sess.lib.php");
require_once (LIB_DIR . "vpa_mail.lib.php");
require_once (LIB_DIR . "vpa_popcornnews.lib.php");
require_once (LIB_DIR . "vpa_form_generator.lib.php");
require_once (LIB_DIR . "vpa_mongodb.lib.php");

class base_api {
	/**
	 * @var array массив связей между типом страницы, свойстом action и функцией, которую необходимо выполнять если данные были переданы методом GET
	 */
	public $get_actions;
	/**
	 * @var array массив связей между типом страницы, свойстом action и функцией, которую необходимо выполнять если данные были переданы методом POST
	 */
	public $post_actions;
	/**
	 * @var array массив функций, которые должны будут выпоняться в самом начале перед выполнением функций заголовков
	 */
	public $header_actions;
	/**
	 * @var array массив для хранения URI путей, для которых определены обработчики
	 * имеет смысл только при использовании ЧПУ и требуется для того, чтобы можно было легко получать type и action для обработчиков
	 * виртульных директорий
	 */
	public $handler_paths;
	/**
	 * @var boolean результат работы функций $header_actions, если false - то функции заголовков не выполняются
	 */
	public $header_flag = true;
	/**
	 * @var string язык по умолчанию
	 */
	public $lang = 'ru';
	/**
	 * @var string все языки
	 */
	public $langs;
	/**
	 * @var object объект класса Smarty для работы с темплейтами
	 */
	public $tpl;
	/**
	 * @var object объект класса Logs
	 */
	public $log;
	/**
	 * @var object объект класса Session
	 */
	public $sess;
	/**
	 * $var object обхект класса vpa_memcache
	 */
	public $memcache;
	/**
	 * @var object объект класса Permissions
	 */
	public $perm;
	/**
	 * @var object объект класса Notices
	 */
	public $notices;
	/**
	 * @var string имя файла темплейта
	 */
	public $tpl_file_name;
	/**
	 * @var string URL страницы, куда необходимо будет произвести редирект
	 */
	public $url_jump;
	/**
	 * @var array Если установлен массив со значениями, то выводится содержимое этого массива (файла)
	 * - data - binary stream of data
	 * - mime_type - mime type of data stream
	 * - length - length of stream
	 */
	public $passthru = null;

	public $exit = false;
	/**
	 * @var string Smarty_cache_id - строка, определяющая страницу кеша
	 */
	public $cache_id;

	/**
	 * @var string xslt_path - путь от корня веб-сайта до xsl файла
	 */
	public $xslt_path;

	/**
	 * @var string transaction - флаг, указывающий, была ли начата транзакция
	 * 0 - транзакция не начата
	 * 1 - транзакция начата
	 */
	public $transaction;

	public $rollback = false;

	public $constants;

	/**
	 * Предназначена для хранения различных переменных, используемых внутри класса, чтобы не использовать глобальные переменные
	 */
	public $cache_vars;

	public $http_headers;

	/**
	 * @var array rewrite - список путей, которые нужно будет подставлять вместо type и action
	 */
	public $rewrite;

	public $use_rewrite;
	public $type;
	public $action;


	public function __construct() {
		$GLOBALS['base_class'] = &$this;
		$this->log = VPA_logger::getInstance();
		$this->sess = session::getInstance();
		$this->memcache = VPA_memcache::getInstance();
		$this->tpl = VPA_template::getInstance();
		$this->tpl->left_delimiter = '{{';
		$this->tpl->right_delimiter = '}}';
		$this->use_rewrite = USE_REWRITE_404;
	}

	/**
	 * Передает заголовок HTTP header
	 */
	public function set_header($header) {
		$this->http_headers[] = $header;
		return true;
	}

	public function quit() {
		$this->exit = true;
	}

	/**
	 * Используется если для данного набора интерфейсов требуется изменить настройки кеширования
	 *
	 * @value bool нужно ли использовать кеш
	 * @value int время жизни файла кеша в секундах
	 */
	public function set_cache_params($use_cache, $cache_life_time) {
		$this->tpl->caching = $use_cache;
		$this->tpl->cache_lifetime = $cache_life_time;
	}

	/**
	 * Используется для установки уникального идентификатора страницы кеша
	 *
	 * @value string уникальный идентификатор страницы кеша
	 */
	public function set_cache_id ($id) {
		$this->cache_id = $id;
	}

	public function begin() {
		if (!$this->transaction) {
			$sql = &VPA_sql::getInstance();
			$sql->begin();
			// $this->transaction=1;
		}
	}

	public function commit() {
		if ($this->transaction) {
			$sql = &VPA_sql::getInstance();
			$sql->commit();
			// $this->transaction=0;
		}
	}

	public function rollback() {
		if ($this->transaction) {
			$sql = &VPA_sql::getInstance();
			$sql->rollback();
			// $this->transaction=0;
		}
	}

	/**
	 * Иницализация класса, определение метода передачи данных (GET,POST), определение переменной action  и вызов функции, которая соответствует указанному
	 * типу и действию
	 */
	public function init() {
		$status = 1;
		$start = $this->log->get_time();
		ob_start();

		$this->action = $this->get_param('action');
		$this->type = $this->get_param('type');
		$use_get = intval($this->get_param('ug'));

		if ($this->use_rewrite && $_SERVER['REQUEST_METHOD'] != 'POST' && $use_get == 0) {
			$this->parse_path('/');
		}

		$this->action = ($this->action) ? strtolower($this->action) : 'default';
		$this->type = ($this->type) ? strtolower($this->type) : 'default';
		$this->tpl->assign(array('page_type' => $this->type, 'page_action' => $this->action));

		if (is_array($this->header_actions)) {
			foreach ($this->header_actions as $indx => $val) {
				if ($this->header_flag) $this->header_flag = $this->$val();
				else break;
			}
		}

		if ($this->header_flag) {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$current_func = isset($this->post_actions['/'][$this->type][$this->action]) ? $this->post_actions['/'][$this->type][$this->action] : 'null';
				if ($current_func == 'null') {
					$current_func = isset($this->get_actions['/'][$this->type]['any']) ? $this->get_actions['/'][$this->type]['any'] : 'null';
				}
			} else {
				$current_func = 'null';
				if ($this->use_rewrite && $use_get == 0) {
					/**
					 * содержимое данного условия занимается проверкой всех возможных путей для поиска обработчика
					 * один недостаток: на данный момент реализована поддержка только одного шаблона "any" в пути, то есть
					 * если мы имеем путь от которого надо искать обработчик, допустим /guide/country/Indonesia/fraud
					 * то мы можем в шаблоне пути, который мы укажем в get_handler_func, использовать только один any, чтобы, допустим, убрать
					 * идентификатор страны: /guide/country/any/fraud
					 */
					$pts = $this->rewrite;
					$i = 1;
					$this->type = 'default';
					$this->action = 'default';
					$current_any_count = count($pts);
					$current_any_value = '';
					do {
						$handler_path = "/" . join("/", $pts);
						$current_func = $this->get_rewrite_path_function($handler_path);
						if (count($pts) > 0 && $current_any_count == 0) {
							$pts[0] = $current_any_value;
							$this->action = $this->type;
							$this->type = $pts[count($pts)-1];
							unset($pts[count($pts)-1]);
							$current_any_count = count($pts);
						} elseif (count($pts) > 0 && $current_any_count > 0) {
							if ($current_any_count < count($pts)) {
								$pts[$current_any_count] = $current_any_value;
							}
							$current_any_count -= 1;
							$current_any_value = $pts[$current_any_count];
							$pts[$current_any_count] = 'any';
						}
						if (empty($pts)) {
							$i++;
						}
					} while ($current_func == 'null' && $i < 3);
				} else {
					$current_func = isset($this->get_actions['/'][$this->type][$this->action]) ? $this->get_actions['/'][$this->type][$this->action] : 'null';
					if ($current_func == 'null') {
						$current_func = isset($this->get_actions['/'][$this->type]['any']) ? $this->get_actions['/'][$this->type]['any'] : 'null';
					}
				}
			}
			$methods = get_class_methods($this);
			if (in_array($current_func, $methods)) $this->$current_func();
			else $this->null_func();
		}

		$this->log->add_message(get_class($this), "Init engine(create page)", $start, $status);
		return $status;
	}

	public function get_rewrite_path_function($handler_path) {
		$current_func = isset($this->get_actions[$handler_path][$this->type][$this->action]) ? $this->get_actions[$handler_path][$this->type][$this->action] : 'null';
		if ($current_func == 'null') {
			$current_func = isset($this->get_actions[$handler_path][$this->type]['any']) ? $this->get_actions[$handler_path][$this->type]['any'] : 'null';
		}
		return ($current_func == 'null') ? 'null' : $current_func;
	}

	public function parse_path($handler_path) {
		$this->check_redirect();

		$url = $_SERVER['REQUEST_URI'];
		$handler_path = str_replace("/", "\/", $handler_path);
		$url = preg_replace ("/index.php.*/is", "", $url);
		$url = preg_replace ("/^" . $handler_path . "/", "", $url);
		$url = preg_replace ("/\/$/", "", $url);
		$dej = explode("/", $url);
		$handler_path == '\/' && ($this->rewrite = $dej);
		$this->type = (isset($dej[0]) && !empty($dej[0])) ? $dej[0] : 'default';
		$this->action = (isset($dej[1]) && !empty($dej[1])) ? $dej[1] : 'default';
	}

	/**
	 * 301 Redirect from "/" to without "/"
	 *
	 * @example http://popcornnews.ru/news/108803/ => http://popcornnews.ru/news/108803
	 * @example http://popcornnews.ru/index.php => http://popcornnews.ru/
	 *
	 * @see NOT USE $this->url_jump for not call callback's for URL paths
	 */
	public function check_redirect() {
		if (preg_match('@(?:^/index.php.*$|^(.+)/$)@Uis', $_SERVER['REQUEST_URI'], $matches)) {
			header(HTTP_STATUS_301);
			header('Location: ' . (isset($matches[1]) ? $matches[1] : '/'));
			die;
		}
	}

	/**
	 * Механизм для AJAX обмена данными с сервером
	 */
	public function handler_start_observer() {
		$observer = new VPA_observer();
		$params = $this->get_param('params');
		$method = $params[1];
		if (method_exists($observer, $method)) {
			$value = $observer-> {
				$method}
			($this, $params);
		}
		$this->set_tpl_dir("/");
		$this->tpl->assign(array('field' => $params[0], 'value' => $value));
		$this->set_tpl_file("observer.ajax");
	}

	/**
	 * Завершение работы класса, вывод полученной страницы или редирект на указанный URL, вывод логов(если разрешено)
	 */
	public function close() {
		$status = 1;
		$output = '';
		$start = $this->log->get_time();

		if ($this->transaction) {
			if ($this->rollback || !$this->commit()) {
				$this->rollback();
			}
		}

		if ($this->exit) {
			return true;
		}

		if (!$this->url_jump && !$this->passthru) {
			$this->tpl->assign ('base_class', $this);
			$this->tpl->make();
			$output = $this->tpl->output();
			if (!empty($this->http_headers)) {
				foreach ($this->http_headers as $indx => $header) {
					header($header);
                    if ($header == $_SERVER["SERVER_PROTOCOL"].' 304 Not Modified') {
						return true;
					}
				}
			}

			$output = $this->parseHTMLentities($output);

			$fileinfo = pathinfo($this->tpl->template);
			if (isset($fileinfo['extension']) && $fileinfo['extension'] == 'xml') {
				if (USE_XML2HTML_TRANSFORM) {
					$html = "text/html";
					$xhtml = "application/xhtml+xml";
					$xslt = new xsltProcessor;
					if ($xsldata = DomDocument::load($this->xslt_path)) {
						$xslt->importStyleSheet($xsldata);
						$output = $xslt->transformToXML(DomDocument::loadXML($output));
					}
					if (strpos($_SERVER["HTTP_ACCEPT"], $xhtml) > 0) {
						// echo $_SERVER["HTTP_ACCEPT"];
						header (CODEPAGE_HEADER_XHTML);
					}
				} else {
					header (CODEPAGE_HEADER_XML);
				}
			}

			$support_gzip = (USE_GZIP && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') >= 0) ? true : false;

			if ($support_gzip && !DEBUG && $_GET['show_debug'] != 1) {
				$this->log->add_message(get_class($this), "Stop engine(output page:" . $this->tpl_file_name . ") " . ((!isset($error)) ? "" : "($error)"), $start, $status);
				// if ($status) header ("HTTP/1.0 200 OK"); else header(HTTP_STATUS_500);
				if (!$status) header(HTTP_STATUS_500);
				{
					$errors = ob_get_contents ();
					ob_end_clean ();
					// Если расширение файла ajax - значит отладку выводить нельзя (перестает корректно работать JS)
					// if (isset($fileinfo['extension']) && $fileinfo['extension'] != 'ajax' || DEBUG_AJAX) {
					if ($fileinfo['filename'] != 'ajax' || (defined('DEBUG_AJAX') && DEBUG_AJAX)) {
						$output .= $this->log->show(DEBUG_MODE_TREE, false);
						$output .= "<div style='border:1px solid #000; width:90%; background-color:#ffeeee; padding:15px; margin:20px;'>" . $errors . "</div>";
					}
				}
				header('Content-Encoding: gzip');
				$compress = gzencode($output);
				header('Content-Length: ' . strlen($compress));
				echo $compress;
			} else {
				// header ("HTTP/1.0 200 OK");
				$this->log->add_message(get_class($this), "Stop engine(output page:" . $this->tpl_file_name . ") " . ((!isset($error)) ? "" : "($error)"), $start, $status);
				echo $output;
				// Если расширение файла ajax - значит отладку выводить нельзя (перестает корректно работать JS)
				// if ((isset($fileinfo['extension']) && $fileinfo['extension'] != 'ajax')) {
				if (DEBUG && ($fileinfo['filename'] != 'ajax' || (defined('DEBUG_AJAX') && DEBUG_AJAX))) {
					echo $this->log->show(DEBUG_MODE_TREE, false);
				}
			}
		} elseif ($this->url_jump) {
			header('Location: ' . $this->url_jump);
		} else {
			// header ("HTTP/1.0 200 OK");
			$this->output_passthru();
		}
		// $this->log->add_message(get_class($this),"Stop engine(output page) ".((!isset($error)) ? "" : "($error)"),$start,$status);
		return $status;
	}

	/**
	 * переводит &nbsp; в код
	 */
	public function parseHTMLentities($str) {
		$str = str_replace("&nbsp;", "&#160;", $str);
		return $str;
	}

	/**
	 * Пустая функция - вызывается если не указано ни одного обработчика для указанного типа или действия
	 */
	public function null_func() {
		header(HTTP_STATUS_404);
		$this->set_tpl_file("u_error_404.html");
		return true;
	}

	/**
	 * Занимается тем, что транслирует по заданным нами правилам список директорий в URL в набор параметров GET
	 *
	 * @param string $ правила
	 */
	public function parse_url2get($pattern) {
		$names = explode('/', $pattern);
		foreach ($names as $i => $name) {
			$_GET[$name] = $this->rewrite[$i];
		}
		return true;
	}

	/**
	 * Получение значение переменной, переданной методом GET или POST
	 *
	 * @param string $ название переменной
	 * @return string значение переменной
	 */
	public function get_param($param_name) {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$post = (isset($_POST[$param_name])) ? $_POST[$param_name] : null;
			if (!is_array($post)) {
				$post = get_magic_quotes_gpc() ? stripslashes($post) : $post;
			}
			if (empty($post) && isset($_FILES[$param_name])) {
				$post = $_FILES[$param_name];
			}
			return $post;
		} else {
			return (isset($_GET[$param_name])) ? (get_magic_quotes_gpc() ? stripslashes($_GET[$param_name]) : $_GET[$param_name]) : null;
		}
	}

	/**
	 * Получение хеша параметров, отвечающих определенной маске
	 *
	 * @param string $ маска
	 * @return array хеш с результатами
	 */
	public function get_params_with_pattern($pattern) {
		$data = ($_SERVER["REQUEST_METHOD"] == "POST") ? $_POST : $_GET;
		$results = array();
		foreach ($data as $indx => $value) {
			if (strpos($indx, $pattern) !== false) {
				$results[$indx] = $value;
			}
		}
		return $results;
	}

	/**
	 * Установка функции для выполнения в самом начале
	 *
	 * @param string $ имя функции
	 */
	public function header_handler_func ($func_name) {
		$this->header_actions[] = $func_name;
	}

	/**
	 * Установка обработчика для заданного типа страницы и действия в случае передачи их методом GET
	 *
	 * @param string $ тип страницы
	 * @param string $ действие
	 * @param string $ имя функции
	 */
	public function get_handler_func ($type, $action, $func_name, $handler_path = '/') {
		$handler_path = $handler_path != '/' ? rtrim($handler_path, "/") : "/";
		!isset($this->handler_paths[$handler_path]) && ($this->handler_paths[$handler_path] = 0);
		$this->handler_paths[$handler_path] += 1;
		$this->get_actions[$handler_path][$type][$action] = $func_name;
	}

	/**
	 * Установка обработчика для заданного типа страницы и действия в случае передачи их методом POST
	 *
	 * @param string $ тип страницы
	 * @param string $ действие
	 * @param string $ имя функции
	 */
	public function post_handler_func ($type, $action, $func_name, $handler_path = '/') {
		empty($this->handler_paths[$handler_path]) && ($this->handler_paths[$handler_path] = 0);
		$this->handler_paths[$handler_path] += 1;
		$this->post_actions[$handler_path][$type][$action] = $func_name;
	}

	/**
	 * Установка обработчика для заданного типа страницы и действия в случае передачи их методом GET
	 *
	 * @param string $ URL страницы, куда будет осуществлен переход после завершения работы функции close
	 */
	public function url_jump($url) {
		$this->url_jump = $url;
	}

	public function output_passthru() {
		header('Content-Type: ' . $this->passthru['mime_type']);
		header('content-disposition: inline; filename="' . $this->passthru['name'] . '"');
		// header('Last-Modified: '.gmdate("D, d M Y H:i:s",$this->passthru['last-modified']).' GMT');
		// header('Expires: '.gmdate("D, d M Y H:i:s",time()+604800).' GMT');
		// header('Content-Length: '.strlen($this->passthru['data']));
		// header('Cache-Control: public, max-age=604800');
		header ('Pragma: public');
		echo $this->passthru['data'];
	}

	public function param_type_explorer($type, $namespace, $name, &$array_results, &$errors, $form_validation = FIELD_ANY, $add_info = '') {
		$start = $this->log->get_time();
		$class_name = 'VPA_explorer_' . $type;
		if (!class_exists($class_name, false)) {
			$plugin_file = TYPE_EXPLORER_DIR . $type . '.type_explorer.php';
			if (!is_file($plugin_file)) {
				$this->log->add_message(get_class($this), "exec(type_explorer file '$plugin_file' not found)", $start, false);
				return false;
			}
			include_once($plugin_file);
		}
		/*if (!class_exists($class_name,false))
                {
                        $this->log->add_message(get_class($this),"exec(class '$class_name' not defined)",$start,false);
                        return false;
                }*/

		$type_explorer = new $class_name($this, $namespace, $name, $array_results, $errors, $form_validation, $add_info);
		$result = $type_explorer->get();
		$this->log->add_message(get_class($this), "$namespace::$name transform data successful", $start, true);
		return $result;
	}

	public function error($code, $message, $header) {
		header($header);
		$this->tpl->assign('code', $code);
		$this->tpl->assign('message', $message);
		$this->tpl->tpl('', '/manager/', 'inc_error.php');
	}
}

class VPA_type_explorer {
	public $base;
	public $namespace;
	public $name;
	public $array_results;
	public $errors;
	public $validation_code;
	public $add_info;

	public function VPA_type_explorer(&$base_obj, $namespace, $name, &$array, &$errors, $form_validation, $add_info) {
		$this->base = $base_obj;
		$this->namespace = $namespace;
		$this->name = $name;
		$this->array_results = &$array;
		$this->errors = &$errors;
		$this->validation_code = $form_validation;
		$this->add_info = $add_info;
	}

	public function get() {
		return false;
	}

	public function validation($value) {
		$error_code = 0;
		if ($this->validation_code &1) {
			if (empty($value)) $error_code = $error_code &1;
		}

		if ($this->validation_code &2) {
			if (!empty($value) && !is_string($value)) $error_code = $error_code &2;
		}

		if ($this->validation_code &4) {
			if (!empty($value) && !is_numeric($value)) $error_code = $error_code &4;
		}

		if ($this->validation_code &8) {
			if (!empty($value) && !preg_match($this->add_info, $value)) $error_code = $error_code &8;
		}
	}
}
