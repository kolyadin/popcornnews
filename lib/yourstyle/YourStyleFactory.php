<?php

namespace popcorn\lib\yourstyle;

/**
 * Class YourStyleFactory
 * Date begin: 28.02.2011
 *
 * Factory
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
abstract class YourStyleFactory {
	/**
	 * VPA sess
	 *
	 * @var object of session
	 */
	protected $sess;
	/**
	 * VPA Memcache
	 *
	 * @var object of VPA_Memcache
	 */
	protected $memcache;
	/**
	 * VPA tpl
	 *
	 * @var VPA_template
	 */
	protected $tpl;
	/**
	 * VPA SQL
	 *
	 * @var object of VPA_sql
	 */
	protected $sql;
	/**
	 * User info
	 *
	 * @var array
	 */
	protected $user;
	/**
	 * user_base_api
	 *
	 * @var user_base_api
	 */
	protected $user_lib;
	/**
	 * Old include path
	 *
	 * @var string
	 */
	protected $oldIncPath;
	/**
	 * Upload path, without DOCUMENT_ROOT
	 *
	 * @var string
	 */
	protected static $UPLOAD_PATH = '/upload/yourstyle/';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct(user_base_api &$user_lib = null, $noInitRoutes = false) {

		if ($user_lib) {
			// Base
			$this->sess = session::getInstance();
			$this->memcache = VPA_memcache::getInstance();
			$this->tpl = VPA_template::getInstance();
			$this->sql = VPA_sql::getInstance();
			// $this->mongo = VPA_MongoDB::getInstance()->yourstyle;

			$this->user = $this->sess->restore_var('sess_user');
			$this->user_lib = $user_lib;
			$this->oldIncPath = set_include_path(get_include_path() . ':' . AKLIB_DIR);

			if (!$noInitRoutes) $this->_initRoutes();
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		set_include_path($this->oldIncPath);
	}

	/**
	 * Generate upload path
	 *
	 * @param int $gid - group id of tile
	 * @return string
	 */
	static public function generateUploadTilesPath($gid) {
		$revGid = strrev($gid);
		$path = $_SERVER['DOCUMENT_ROOT'] . '/' . self::$UPLOAD_PATH . 'tiles/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revGid[$i]) ? $revGid[$i] : 0) . '/';
			if (!is_dir($path)) {
				mkdir($path, 0777);
			}
		}
		$path .= $gid . '/';
		if (!is_dir($path)) {
			mkdir($path, 0777);
		}

		return realpath($path) . '/';
	}

	/**
	 * Get www upload path
	 *
	 * @param int $gid - group id of tile
	 * @param int $image - image file name
	 * @param string $size - size of output image
	 * @return string
	 */
	static public function getWwwUploadTilesPath($gid, $image, $size = null) {
		$revGid = strrev($gid);
		$path = ($size ? '/k/yourstyle/' . $size : null) . self::$UPLOAD_PATH . 'tiles/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revGid[$i]) ? $revGid[$i] : 0) . '/';
		}
		return 'http://v1.popcorn-news.ru' . $path . $gid . '/' . $image;
//		return 'http://test.popcornnews.ru' . $path . $gid . '/' . $image;
//		return VPA_template::getInstance()->getStaticPath($path . $gid . '/' . $image);
	}

	/**
	 * Generate upload path
	 *
	 * @param int $id - id of set
	 * @return string
	 */
	static public function generateUploadSetPath($id) {
		$revId = strrev($id);
		$path = $_SERVER['DOCUMENT_ROOT'] . '/' . self::$UPLOAD_PATH . 'final/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revId[$i]) ? $revId[$i] : 0) . '/';
			if (!is_dir($path)) {
				mkdir($path, 0777);
			}
		}

		return realpath($path) . '/';
	}


	public function random_file_name($dir, $ext = 'tmp', $length = 10) {
		if ($ext) $ext = '.' . $ext;
		$filename = sprintf('%s%s', self::rand_str($length), $ext);
		while (file_exists(sprintf('%s/%s', $dir, $filename))) {
			$filename = sprintf('%s%s', self::rand_str($length), $ext);
		}
		return $filename;
	}

	public function rand_str($length = 11) {
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
	 * Get www upload path
	 *
	 * @param int $id - id of tile
	 * @param int $image - image file name
	 * @return string
	 */
	static public function getWwwUploadSetPath($id, $image, $size = null) {
		$revId = strrev($id);
		$path = ($size ? '/k/yourstyle/' . $size : null) . self::$UPLOAD_PATH . 'final/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revId[$i]) ? $revId[$i] : 0) . '/';
		}
		//return 'http://test.popcornnews.ru' . $path . $image;
		return 'http://v1.popcorn-news.ru' . $path . $image;
		//return VPA_template::getInstance()->getStaticPath($path . $image);
	}

	/*for brands*/

	/**
	 * Generate upload path
	 *
	 * @param int $gid - group id of tile
	 * @return string
	 */
	static public function generateUploadBrandsPath($gid) {
		$revGid = strrev($gid);
		$path = $_SERVER['DOCUMENT_ROOT'] . '/' . self::$UPLOAD_PATH . 'brands/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revGid[$i]) ? $revGid[$i] : 0) . '/';
			if (!is_dir($path)) {
				mkdir($path, 0777);
			}
		}
		$path .= $gid . '/';
		if (!is_dir($path)) {
			mkdir($path, 0777);
		}

		return realpath($path) . '/';
	}

	/**
	 * Get www upload path
	 *
	 * @param int $gid - group id of tile
	 * @param int $image - image file name
	 * @param string $size - size of output image
	 * @return string
	 */
	static public function getWwwUploadBrandsPath($gid, $image, $size = null) {
		$revGid = strrev($gid);
		$path = ($size ? '/k/yourstyle/' . $size : null) . self::$UPLOAD_PATH . 'brands/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revGid[$i]) ? $revGid[$i] : 0) . '/';
		}
		return 'http://v1.popcorn-news.ru' . $path . $gid . '/' . $image;
	}

	/*---for brands---*/
	/**
	 * Check is color exist, and return color, if it exist
	 *
	 * @param string $color
	 * @return array|false
	 */
	protected function isSuchColorExist($color) {
	    if(is_null($color) || empty($color)) {
	        return false;
	    }
		foreach (YourStyleBackEnd::$humanColors as $rgb => $humanColor) {
			if ($humanColor['en'] == $color) {
				return array_merge($humanColor, array('rgb' => $rgb));
			}
		}
		return false;
	}

	/**
	 * Get colors
	 *
	 * @return array
	 */
	protected function getColors() {
		return YourStyleBackEnd::$humanColors;
	}

	/**
	 * Get request Uri
	 *
	 * @return string
	 */
	protected function getRequestUrl() {
		$requestUri = preg_replace('@(&|\?).*@', '', $_SERVER['REQUEST_URI']);
		if (substr($requestUri, 0, 1) != '/') $requestUri = '/' . $requestUri;
		if (substr($requestUri, -1) == '/') $requestUri = substr($requestUri, 0, -1);

		return $requestUri;
	}

	/**
	 * @alias for user_lib::get_param
	 */
	protected function get_param() {
		return call_user_func_array(array(&$this->user_lib, 'get_param'), func_get_args());
	}

	/**
	 * @alias for user_lib::redirect
	 */
	protected function redirect() {
		return call_user_func_array(array(&$this->user_lib, 'redirect'), func_get_args());
	}

	/**
	 * @alias for user_lib::handler_show_error
	 */
	protected function handler_show_error() {
		return call_user_func_array(array(&$this->user_lib, 'handler_show_error'), func_get_args());
	}

	/**
	 * @alias for user_lib::url_jump
	 */
	protected function url_jump() {
		return call_user_func_array(array(&$this->user_lib, 'url_jump'), func_get_args());
	}
}
