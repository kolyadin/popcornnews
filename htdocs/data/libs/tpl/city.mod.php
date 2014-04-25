<?php

/**
 * плагин получения города
 */
class vpa_tpl_city {
	public $tpl;
	public $aliases;

	public function vpa_tpl_city() {
		$this->tpl = VPA_template::getInstance();
	}

	public function get($city) {
		$ct = array_shift($this->tpl->plugins['query']->get('cities', array('name' => $city), array('name'), 0, 1));
		return !empty($ct) ? $city : 'Другой';
	}
}

?>