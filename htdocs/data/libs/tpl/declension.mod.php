<?php

/**
 * плагин склонения слов в зависимости от числа
 */
class vpa_tpl_declension {
	public function vpa_tpl_declension() {
	}

	/**
	 * int v - число
	 * string f1 - существительное для числа 1 - 1 голос
	 * string f2 - существительное для чисел в диапазоне от 2 до 4-х: 2 голоса
	 * string f3 - существительное для остальных чисел:5 голосов
	 */
	public function get($v, $f1, $f2, $f3) {
		$v = str_pad($v, 2, '0', STR_PAD_LEFT);
		$s2 = intval(substr($v, -2, 1));
		$s1 = intval(substr($v, -1, 1));
		if ($s2 == 1 || $s1 == 0 || ($s1 > 4 && $s1 <= 9)) return $f3;
		if ($s1 == 1) return $f1;
		if ($s1 >= 2 && $s1 <= 4) return $f2;
	}
}

?>