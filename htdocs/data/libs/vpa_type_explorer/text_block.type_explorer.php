<?php
/*vpa_packet: image*/
/**
 */

class VPA_explorer_text_block extends VPA_type_explorer {
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