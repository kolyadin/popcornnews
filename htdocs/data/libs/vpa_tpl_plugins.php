<?php

/**
 * File: vpa_tpl_plugins
 * Date begin: Apr 15, 2011
 *
 * Plugins
 * Lazy loading
 *
 * @package popcornnews
 * @author Azat Khuzhin <dohardgopro@gmail.com>
 */

class VPA_tpl_plugins extends ArrayObject {
	/**
	 * Instance of base class
	 *
	 * @var VPA_tpl_plugins
	 */
	private static $instance;
	/**
	 * Plugins
	 *
	 * @var array
	 */
	private $plugins = array();
	/**
	 * Path with plugins
	 *
	 * @var string
	 */
	private $pluginsPath;

	/**
	 * Create an instance
	 *
	 * @return VPA_tpl_plugins
	 */
	static public function getInstance($pluginsPath) {
		if (!self::$instance) {
			self::$instance = new self($pluginsPath);
		}
		return self::$instance;
	}

	/**
	 * Array Getter
	 *
	 * @param string $name
	 * @return object
	 */
	public function offsetGet($name) {
		return $this->__get($name);
	}

	/**
	 * Array Setter
	 *
	 * @param string $name
	 * @param object $value
	 * @return VPA_tpl_plugins
	 */
	public function offsetSet($name, $value = null) {
		return $this->__set($name, $value);
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return object
	 */
	public function __get($name) {
		if (!isset($this->plugins[$name])) {
			$this->__set($name);
		}
		return $this->plugins[strtolower($name)];
	}

	/**
	 * Setter
	 *
	 * @param string $name
	 * @param object $value
	 * @return VPA_tpl_plugins
	 */
	public function __set($name, $value = null) {
		if ($value === null) {
			$this->initPlugin(strtolower($name));
		} else {
			$this->plugins[strtolower($name)] = $value;
		}
		return $this;
	}

	/**
	 * Init plugin
	 *
	 * @param string $name
	 * @return bool
	 */
	private function initPlugin($name) {
		$filename = sprintf('%s/%s.mod.php', $this->pluginsPath, $name);
		if (file_exists($filename)) {
			require_once $filename;
		}

		$filename = sprintf('%s/%s.php', $this->pluginsPath, $name);
		if (file_exists($filename)) {
			require_once $filename;
		}
		$className = 'vpa_tpl_' . $name;
		if (class_exists($className, false)) {
			$this->__set($name, new $className);
			return true;
		}

		throw new Exception('Plugin ' . $name . ' doesn`t exist');
	}

	/**
	 * Constructor
	 */
	public function __construct($pluginsPath) {
		$this->pluginsPath = $pluginsPath;
	}
}
