<?php
/*vpa_packet: date_only*/
/**
 */

class VPA_explorer_date_md extends VPA_type_explorer {
	public function get() {
		$ret = array();
		$month = (int)$this->base->get_param($this->namespace . '::' . $this->name . "_month");
		$day = (int)$this->base->get_param($this->namespace . '::' . $this->name . "_day");
		if ($day && $month) {
			// $day=str_pad($day,2,'0',STR_PAD_LEFT);
			// $month=str_pad($month,2,'0',STR_PAD_LEFT);
			$ret['value'] = $month * 100 + $day;
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