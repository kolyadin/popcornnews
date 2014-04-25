<?php

/**
 * Class: ys
 * Date begin: Mar 11, 2011
 *
 * Wrapper for yourstyle model methods
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
class vpa_tpl_ys {
	public static function __callStatic($name, $arguments) {
		if ($arguments) {
			return call_user_func_array('YourStyle_Factory::' . $name, $arguments);
		} else {
			return call_user_func('YourStyle_Factory::' . $name);
		}
	}
}
