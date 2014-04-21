<?

/**
 * Сумма прописью
 */

/**
 * # RUS
 */

/**
 * @author runcore
 * @link http://habrahabr.ru/blogs/php/53210/
 */
function ru_num2str($inn) {
	if (!function_exists('morph')) {
		/**
		 * Склоняем словоформу
		 */
		public function morph($n, $f1, $f2, $f5) {
			$n = abs($n) % 100;
			$n1 = $n % 10;
			if ($n > 10 && $n < 20)
				return $f5;
			if ($n1 > 1 && $n1 < 5)
				return $f2;
			if ($n1 == 1)
				return $f1;
			return $f5;
		}

	}

	$nol = 'ноль';
	$str[100] = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
	$str[11] = array('', 'десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать', 'двадцать');
	$str[10] = array('', 'десять', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
	$sex = array(
		array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'), // m
		array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять') // f
	);
	$forms = array(
		array('', '', '', 1), // array('копейка',  'копейки',   'копеек',     1), // 10^-2
		array('', '', '', 0), // array('рубль',    'рубля',     'рублей',     0), // 10^ 0
		array('тысяча', 'тысячи', 'тысяч', 1), // 10^ 3
		array('миллион', 'миллиона', 'миллионов', 0), // 10^ 6
		array('миллиард', 'миллиарда', 'миллиардов', 0), // 10^ 9
		array('триллион', 'триллиона', 'триллионов', 0), // 10^12
	);
	$out = $tmp = array();
	// Поехали!
	$tmp = explode('.', str_replace(',', '.', $inn));
	$rub = number_format($tmp[0], 0, '', '-');
	if ($rub == 0)
		$out[] = $nol;
	// нормализация копеек
	$kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0, 2) : '00';
	$segments = explode('-', $rub);
	$offset = sizeof($segments);
	if ((int) $rub == 0) { // если 0 рублей
		$o[] = $nol;
		$o[] = morph(0, $forms[1][0], $forms[1][1], $forms[1][2]);
	} else {
		foreach ($segments as $k => $lev) {
			$sexi = (int) $forms[$offset][3]; // определяем род
			$ri = (int) $lev; // текущий сегмент
			if ($ri == 0 && $offset > 1) {// если сегмент==0 & не последний уровень(там Units)
				$offset--;
				continue;
			}
			// нормализация
			$ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
			// получаем циферки для анализа
			$r1 = (int) substr($ri, 0, 1); //первая цифра
			$r2 = (int) substr($ri, 1, 1); //вторая
			$r3 = (int) substr($ri, 2, 1); //третья
			$r22 = (int) $r2 . $r3; //вторая и третья
			// разгребаем порядки
			if ($ri > 99) $o[] = $str[100][$r1]; // Сотни
			if ($r22 > 20) {// >20
				$o[] = $str[10][$r2];
				$o[] = $sex[$sexi][$r3];
			} else { // <=20
				if ($r22 > 9) $o[] = $str[11][$r22 - 9]; // 10-20
				elseif ($r22 > 0) $o[] = $sex[$sexi][$r3]; // 1-9

			}
			// Рубли
			$o[] = morph($ri, $forms[$offset][0], $forms[$offset][1], $forms[$offset][2]);
			$offset--;
		}
	}
	return trim(preg_replace("/\s{2,}/", ' ', implode(' ', $o)));
}

/**
 * # ENG
 */


/**
 * @link http://www.phpro.org/examples/Convert-Numbers-to-Words.html
 */
function en_num2str($number) {
	if (($number < 0) || ($number > 999999999)) {
		return false;
	}

	$Gn = floor($number / 1000000);  /* Millions (giga) */
	$number -= $Gn * 1000000;
	$kn = floor($number / 1000);     /* Thousands (kilo) */
	$number -= $kn * 1000;
	$Hn = floor($number / 100);	/* Hundreds (hecto) */
	$number -= $Hn * 100;
	$Dn = floor($number / 10);	 /* Tens (deca) */
	$n = $number % 10;		   /* Ones */

	$res = "";

	if ($Gn) {
		$res .= en_num2str($Gn) . " million";
	}

	if ($kn) {
		$res .= ( empty($res) ? "" : " ") .
			en_num2str($kn) . " thousand";
	}

	if ($Hn) {
		$res .= ( empty($res) ? "" : " ") .
			en_num2str($Hn) . " hundred";
	}

	static $ones = array("", "one", "two", "three", "four", "five", "six",
		"seven", "eight", "nine", "Ten", "eleven", "twelve", "thirteen",
		"fourteen", "fifteen", "sixteen", "seventeen", "eightteen",
		"nineteen");
	static $tens = array("", "", "twenty", "thirty", "fourty", "fifty", "sixty",
		"seventy", "eigthy", "ninety");

	if ($Dn || $n) {
		if (!empty($res)) {
			$res .= " and ";
		}

		if ($Dn < 2) {
			$res .= $ones[$Dn * 10 + $n];
		} else {
			$res .= $tens[$Dn];

			if ($n) {
				$res .= "-" . $ones[$n];
			}
		}
	}

	if (empty($res)) {
		$res = "zero";
	}

	return $res;
}
