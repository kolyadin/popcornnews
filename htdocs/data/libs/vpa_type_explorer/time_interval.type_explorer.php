<?php
/*vpa_packet: date_only*/
/**
 */

class VPA_explorer_time_interval extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$hour = (int)$this->base->get_param($this->namespace . '::' . $this->name . "_hour");
		$minute = (int)$this->base->get_param($this->namespace . '::' . $this->name . "_minute");
		if ($hour || $minute) {
			$minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
			$ret['value'] = $hour * 100 + $minute;
			$ret['write'] = true;
		} else {
			$ret['value'] = null;
			$ret['write'] = true;
		}
		$ret['write'] && $this->array_results[$this->name] = $ret['value'];
		return true;
	}
}

?>