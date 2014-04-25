<?php
/*vpa_packet: checkbox*/
/**
 */

class VPA_explorer_checkbox extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$name = trim($this->base->get_param($this->namespace . '::' . $this->name));
		$ret['value'] = $name;
		$ret['write'] = true;
		$ret['write'] && $this->array_results[$this->name] = $ret['value'];
		return true;
	}
}

?>