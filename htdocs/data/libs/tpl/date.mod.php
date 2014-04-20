<?php
/**
 * Date plugin
 */

class vpa_tpl_date {
	public $months;
	public $nominative;

	public function vpa_tpl_date() {
		$this->months = array(
			1 => '€нвар€',
			2 => 'феврал€',
			3 => 'марта',
			4 => 'апрел€',
			5 => 'ма€',
			6 => 'июн€',
			7 => 'июл€',
			8 => 'августа',
			9 => 'сент€бр€',
			10 => 'окт€бр€',
			11 => 'но€бр€',
			12 => 'декабр€',
		);

		$this->nominative = array(
			1 => '€нварь',
			2 => 'февраль',
			3 => 'март',
			4 => 'апрель',
			5 => 'май',
			6 => 'июнь',
			7 => 'июль',
			8 => 'август',
			9 => 'сент€брь',
			10 => 'окт€брь',
			11 => 'но€брь',
			12 => 'декабрь',
		);
	}

	public function _parse($time, $format) {
		// тут мы реализуем аналогичные методы форматировани€ дат, как в доке по функции date
		// реализованы далеко не все, а только те, что надо.
		// используетс€ ручное форматирование, чтобы можно было реально называть мес€ца и дни недели в независомости от настроек локали на сервере.
		// можно выдумывать свои модификаторы, только тогда - документируйте плиз :)
		$date = $format;
		$date = str_replace("%Y", date('Y', $time), $date);
		$date = str_replace("%m", date('m', $time), $date);
		$date = str_replace("%F", $this->months[intval(date('m', $time))], $date);
		$date = str_replace("%N", $this->nominative[intval(date('m', $time))], $date);
		$date = str_replace("%d", date('d', $time), $date);
		$date = str_replace("%j", date('j', $time), $date);
		$date = str_replace("%H", date('H', $time), $date);
		$date = str_replace("%i", date('i', $time), $date);		
		return $date;
	}

	/**
	 * формат даты см. в доке по PHP дл€ функции date
	 */
	public function parse($date, $format) {
		preg_match_all("|(\d{4})(\d{2})(\d{2})|is", $date, $out);
		$time = mktime(0, 0, 0, (isset($out[2][0]) ? $out[2][0] : null), (isset($out[3][0]) ? $out[3][0] : null), (isset($out[1][0]) ? $out[1][0] : null));
		return $this->_parse($time, $format);
	}

	public function dmyhi($date, $format) {
		preg_match_all("|(\d{2})-(\d{2})-(\d{4})\s+(\d{2}):(\d{2})|is", $date, $outs);
		$time = mktime($outs[4][0], $outs[5][0], 0, $outs[2][0], $outs[1][0], $outs[3][0]);
		return $this->_parse($time, $format);
	}

	public function unixtime($date, $format) {
		return $this->_parse($date, $format);
	}
}

?>