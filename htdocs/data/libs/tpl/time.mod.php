<?php

class vpa_tpl_time {
	public function vpa_tpl_time() {
	}

	/**
	 * формат времени см. в доке по PHP для функции date
	 */
	public function parse($date, $format) {
		preg_match_all("|(\d{2})(\d{2})(\d{2})|is", $date, $out);
		$date = $format;
		$date = str_replace("%H", $out[1][0], $date);
		$date = str_replace("%i", $out[2][0], $date);
		$date = str_replace("%s", $out[3][0], $date);
		return $date;
	}

	/**
	 * Write time
	 *
	 * @param int $seconds - seconds
	 * @return string
	 */
	public function writeTime($seconds) {
		$seconds = (int)$seconds;

		// hours & minutes & seconds
		if ($seconds >= 60*60*1) {
			$hours = round($seconds / 3600);
			$seconds -= ($hours * 3600);

			return sprintf('%u:%02u:%02u', $hours, ($seconds / 60), ($seconds % 60));
		}
		// only minutes & seconds
		else {
			return sprintf('%02u:%02u', floor($seconds / 60), ($seconds % 60));
		}
	}
}

?>