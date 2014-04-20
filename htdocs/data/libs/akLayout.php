<?php

/**
 * @author Azat Khuzhin
 *
 * Layout transfer for search engine
 */

class akLayout {
	/**
	 * Layout
	 *
	 * @var array
	 */
	protected static $_layout = array(
		// lowercase
		'é' => 'q',
		'ö' => 'w',
		'ó' => 'e',
		'ê' => 'r',
		'å' => 't',
		'í' => 'y',
		'ã' => 'u',
		'ø' => 'i',
		'ù' => 'o',
		'ç' => 'p',
		'õ' => '[',
		'ú' => ']',
		'ô' => 'a',
		'û' => 's',
		'â' => 'd',
		'à' => 'f',
		'ï' => 'g',
		'ð' => 'h',
		'î' => 'j',
		'ë' => 'k',
		'ä' => 'l',
		'æ' => ';',
		'ý' => '\'',
		'ÿ' => 'z',
		'÷' => 'x',
		'ñ' => 'c',
		'ì' => 'v',
		'è' => 'b',
		'ò' => 'n',
		'ü' => 'm',
		'á' => ',',
		'þ' => '.',
		// uppercase
		'É' => 'Q',
		'Ö' => 'W',
		'Ó' => 'E',
		'Ê' => 'R',
		'Å' => 'T',
		'Í' => 'Y',
		'Ã' => 'U',
		'Ø' => 'I',
		'Ù' => 'O',
		'Ç' => 'P',
		'Õ' => '{',
		'Ú' => '}',
		'Ô' => 'A',
		'Û' => 'S',
		'Â' => 'D',
		'À' => 'F',
		'Ï' => 'G',
		'Ð' => 'H',
		'Î' => 'J',
		'Ë' => 'K',
		'Ä' => 'L',
		'Æ' => ':',
		'Ý' => '"',
		'ß' => 'Z',
		'×' => 'X',
		'Ñ' => 'C',
		'Ì' => 'V',
		'È' => 'B',
		'Ò' => 'N',
		'Ü' => 'M',
		'Á' => '<',
		'Þ' => '>',
	);


	/**
	 * Transfer
	 */
	public function transfer($text) {
		static $ru, $en, $forRuLayout, $forEnLayout;

		if (!$ru) {
			$ru = join('', array_keys(self::$_layout));
			$en = join('', array_values(self::$_layout));

			$forRuLayout = self::$_layout;
			$forEnLayout = array_flip(self::$_layout);
		}

		return (preg_match(sprintf('@[%s]@s', preg_quote($ru)), $text) ? strtr($text, $forRuLayout) : strtr($text, $forEnLayout));
	}
}
