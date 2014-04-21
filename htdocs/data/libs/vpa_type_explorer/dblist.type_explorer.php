<?php
/*vpa_packet: image*/
/**
 */

class VPA_explorer_dblist extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$name = $this->base->get_param($this->namespace . '::' . $this->name);
		if (is_array($name)) {
			$ret['value'] = ',' . implode(',', $name) . ',';
		} else {
			$ret['value'] = $name;
		}
		$ret['write'] = true;
		$ret['write'] && $this->array_results[$this->name] = $ret['value'];
		return true;
	}
}

?>