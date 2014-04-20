<?php

/**
 * @author Azat Khuzhin
 */

class vpa_tpl_iconv {
	public $from = '';
	public $to = '';
	public $ignore = false;
	protected $exhange_once = 0;


	/**
	 * @param string $from
	 * @param string $to
	 */
	public function  __construct($from = 'WINDOWS-1251', $to = 'UTF-8') {
		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * Преобразовывает массив / объект / строку
	 *
	 * @param mixed $mixed
	 * @return mixed
	 */
	public function iconv($mixed) {
		if (is_scalar($mixed)) {
			$result = $this->iconv_scalar($mixed);
			$this->check_exchange_once();
			return $result;
		}

		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				if (!is_scalar($value)) {
					$mixed[$key] = $this->iconv($value);
				} else {
					$mixed[$key] = $this->iconv_scalar($value);
				}
			}
		}
		// @TODO objects ?
		$this->check_exchange_once();
		return $mixed;
	}

	/**
	 * Меняет кодировки from и to местами
	 */
	public function iconv_exchange() {
		$this->exhange_once = 0;

		$to = $this->to;
		$this->to = $this->from;
		$this->from = $to;
		return $this;
	}

	/**
	 * Меняет кодировки from и to местами
	 * Только на одну операцию
	 */
	public function iconv_exchange_once() {
		if ($this->exhange_once) {
			return $this;
		}

		$this->iconv_exchange();
		$this->exhange_once = 1;
		return $this;
	}

	/**
	 * Преобразовывет скалярный тип из одной кодировки в другую
	 *
	 * @param mixed $var
	 * @return mixed
	 */
	private function iconv_scalar($var) {
		if (is_bool($var)) {
			return (bool)$var;
		} elseif (is_numeric($var)) {
			return (float)$var;
		} else {
			return iconv($this->from, $this->to . ($this->ignore ? '//IGNORE' : null), $var);
		}
	}

	/**
	 * Проверяет, нужно ли сменить кодировку
	 */
	private function check_exchange_once() {
		return ($this->exhange_once == 1 && $this->iconv_exchange());
	}
}
