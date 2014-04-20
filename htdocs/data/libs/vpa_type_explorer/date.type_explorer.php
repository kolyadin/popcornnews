<?php
/*vpa_packet: image*/
/**
 */

class VPA_explorer_date extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$day = (int)$this->base->get_param($this->namespace . '::' . $this->name . "Day");
		$month = (int)$this->base->get_param($this->namespace . '::' . $this->name . "Month");
		$year = (int)$this->base->get_param($this->namespace . '::' . $this->name . "Year");
		if ($day && $month && $year) {
			$ret['value'] = mktime(0, 0, 0, $month, $day, $year);
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