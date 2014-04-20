<?php
/*vpa_packet: date_only*/
/**
 */

class VPA_explorer_date_only extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$day = (int)$this->base->get_param($this->namespace . '::' . $this->name . "Day");
		$month = (int)$this->base->get_param($this->namespace . '::' . $this->name . "Month");
		$year = (int)$this->base->get_param($this->namespace . '::' . $this->name . "Year");
		if ($day && $month && $year) {
			$ret['value'] = $year * 10000 + $month * 100 + $day;
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