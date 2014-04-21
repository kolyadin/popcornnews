<?php

namespace popcorn\lib;

class RuHelper{

	public static $ruMonth = [
		[ 'январь'   , 'января'   ],
		[ 'февраль'  , 'февраля'  ],
		[ 'март'     , 'марта'    ],
		[ 'апрель'   , 'апреля'   ],
		[ 'май'      , 'мая'      ],
		[ 'июнь'     , 'июня'     ],
		[ 'июль'     , 'июля'     ],
		[ 'август'   , 'августа'  ],
		[ 'сентябрь' , 'сентября' ],
		[ 'октябрь'  , 'октября'  ],
		[ 'ноябрь'   , 'ноября'   ],
		[ 'декабрь'  , 'декабря'  ]
	];

	public static function ruDate($format, $timestamp = null){

		if ($timestamp instanceof \DateTime){
			$timestamp = $timestamp->getTimestamp();
		}elseif (is_null($timestamp)){
			$timestamp = time();
		}

		$date = (int)date('m',$timestamp)-1;

		$format = str_replace('f2' , self::$ruMonth[$date][1], $format);
		$format = str_replace('f'  , self::$ruMonth[$date][0], $format);

		return date($format,$timestamp);

	}

	public static function ruNumber($n, array $text = []){

		$pluralNum = ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? 1 : 2);

		if ($n == 1) $pluralNum = 0;

		return $n . ' ' . $text[$pluralNum];

	}

	/**
	 * @param null $timestamp
	 * @return string
	 */
	public static function ruAge($timestamp = null){

		if ($timestamp instanceof \DateTime){
			$timestamp = $timestamp->getTimestamp();
		}elseif (is_null($timestamp)){
			$timestamp = time();
		}

		$date1 = new \DateTime(date('c',$timestamp));
		$date2 = new \DateTime();

		$diff = $date1->diff($date2);

		if ($diff->format('%Y') > 0){
			return self::ruNumber((int)$diff->format('%Y'),['год','года','лет']);
		}elseif ($diff->format('%m') > 0){
			return self::ruNumber((int)$diff->format('%m'),['месяц','месяца','месяцев']);
		}elseif ($diff->format('%d') > 0){
			return self::ruNumber((int)$diff->format('%d'),['день','дня','дней']);
		}else{
			return '1 день';
		}


	}

	/**
	 * @param null $timestamp
	 * @return string
	 */
	public static function ruDateFriendly($timestamp = null) {

		if ($timestamp instanceof \DateTime){
			$timestamp = $timestamp->getTimestamp();
		}elseif (is_null($timestamp)){

		}

		$output = '';
		if(date('Ymd') == date('Ymd', $timestamp)){
			$output = 'Сегодня, ' . date('H:i',$timestamp);
		} elseif (date('Ymd', strtotime('-1 day')) == date('Ymd', $timestamp)) {
			$output = 'Вчера, ' . date('H:i',$timestamp);
		} else {
			$output = strftime("%d %b %Y", $timestamp);
		}

		return $output;
	}


}