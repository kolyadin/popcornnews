<?php
/*vpa_packet: image*/
/**
 */

class VPA_explorer_image extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$file = $this->base->get_param($this->namespace . '::' . $this->name);
		$file_del = $this->base->get_param($this->namespace . '::' . $this->name . '_del');
		if ($file_del) {
			$ret['value'] = null;
			$ret['write'] = true;
		} elseif (isset($file['name']) && $file['name']) {
			$ret['value'] = $file;
			$ret['write'] = true;
		} else {
			$ret['value'] = '?n?';
			$ret['write'] = true;
		}
		$ret['write'] && $this->array_results[$this->name] = $ret['value'];
		return true;
	}
}

?>