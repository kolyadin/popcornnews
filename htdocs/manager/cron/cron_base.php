<?php

/**
 * @author Azat Khuzhin
 *
 * Вспомогательные функции
 */

class cron_base {
	/**
	 * Mysql resource
	 *
	 * @var resource
	 */
	public $link_identifier = null;
	/**
	 * Date format
	 * For function date()
	 *
	 * @var string
	 */
	public $date_format = null;

	/**
	 * Constructor
	 *
	 * @param resource $link_identifier
	 * @param string $date_format
	 */
	public function __construct($link_identifier = null, $date_format = null) {
		$this->link_identifier = $link_identifier;
		$this->date_format = $date_format;
	}

	/**
	 * The same as echo,
	 * but also echo date
	 *
	 * @param mixed $mixed
	 * @return void
	 */
	public function cat($mixed) {
		echo '---' . date($this->date_format) . '  :';

		if (is_array($mixed)) print_r($mixed);
		else echo $mixed;
		
		echo '---' . "\n";
	}
}
