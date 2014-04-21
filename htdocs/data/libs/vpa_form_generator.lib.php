<?php
/**
 * класс для разбора описаний полей заданных в виде набора строк
 * умеет понимать следующие типы:
 * interval - задает интервал значений
 * text - текстовая строка
 * textarea - текстовой блок
 * radio - набор радио-боксов
 * checkbox - набор чекбоксов
 * table - выпадающий список со значениями из таблицы
 * select - выпадающий список
 */
class vpa_form_generator {
	public $str;
	public $fields;

	public function vpa_form_generator($str) {
		$this->str = $str;
		preg_match_all("|(.+?):(.+?):(.+?):(.*?);|is", $str, $out);
		$fields = array();
		foreach ($out[1] as $key => $val) {
			$val = trim($val);
			$name = $out[2][$key];
			$part = $out[3][$key];
			$t = $this->_analyze_type($part);
			$js = $this->_analyze_handler($out[4][$key]);
			$s = $t['add_info'];
			$s = is_string($s) ? explode(',', $t['add_info']) : $s;
			$this->fields[$val] = array('name' => $name, 'type' => $t['type'], 'type_info' => $s, 'js_handler' => $js['handler'], 'js_params' => $js['params']);
		}
	}

	public function get() {
		return $this->fields;
	}

	/**
	 * получить названия полей для получения данных из формы
	 */
	public function get_nf() {
		$names = array();
		foreach ($this->fields as $i => $field) {
			switch ($field['type']) {
				case 'interval':
					$names[] = array('name' => $field['name'] . '_start', 'type' => 'number');
					$names[] = array('name' => $field['name'] . '_end', 'type' => 'number');
					break;
				case 'interval_simple':
					$names[] = array('name' => $field['name'] . '_start', 'type' => 'number');
					$names[] = array('name' => $field['name'] . '_end', 'type' => 'number');
					break;
				case 'table':
					$names[] = array('name' => $field['name'], 'type' => 'number');
					break;
				case 'select':
					$names[] = array('name' => $field['name'], 'type' => 'number');
					break;
				case 'image':
					$names[] = array('name' => $field['name'], 'type' => 'image');
					break;
				default:
					$names[] = array('name' => $field['name'], 'type' => 'string');
					break;
			}
		}
		return $names;
	}

	/**
	 * занимается распознаванием, нужен ли для данного поля какой либо JS обработчик
	 */
	public function _analyze_handler($str) {
		$str = trim($str);
		if (empty($str)) {
			return array('handler' => null, 'params' => null);
		}
		$parts = explode("(", $str);
		$js_func = $parts[0];
		$js_str = rtrim($parts[1], ")");
		$js_params = explode(",", $js_str);
		return array('handler' => $js_func, 'params' => $js_params);
	}

	public function get_value($field, $val, &$status) {
		switch ($field['type']) {
			case 'number':
				$record = $val + 0; // Дао приведения к типу int или float, в зависимости от реальных значений
				$status = true;
				break;
			case 'image':
				$name = trim($val['name']);
				$name_del = $name . '_del';
				$file = $_SERVER["DOCUMENT_ROOT"] . '/upload/' . $name;
				if (!empty($val) && !move_uploaded_file ($val['tmp_name'], $file)) $status = false;
				$record = $name;
				$status = true;
				break;
			default:
				$record = trim($val);
				$status = true;
				break;
		}
		return $record;
	}

	public function _analyze_type($str) {
		$parts = explode("(", $str);
		$type = $parts[0];
		switch ($type) {
			case 'interval':
				$str = rtrim($parts[1], ")");
				$add_info = explode(",", $str);
				break;
			case 'interval_simple':
				$str = rtrim($parts[1], ")");
				$add_info = explode(",", $str);
				break;
			case 'radio':
				$str = rtrim($parts[1], ")");
				$add_info = explode(",", $str);
				break;
			case 'select':
				$str = rtrim($parts[1], ")");
				$add_info = explode(",", $str);
				break;
			case 'checkbox':
				$str = rtrim($parts[1], ")");
				$add_info = explode(",", $str);
				break;
			case 'text':
				$add_info = null;
				break;
			case 'image':
				$add_info = null;
				break;
			case 'password':
				$add_info = null;
				break;
			case 'textarea':
				if (isset($parts[1])) {
					$str = rtrim($parts[1], ")");
					$add_info = $str;
				} else {
					$add_info = '0';
				}
				// $add_info=null;
				break;
			case 'table':
				$str = rtrim($parts[1], ")");
				$add_info = $str;

				break;
		}
		return array('type' => $type, 'add_info' => $add_info);
	}
}

?>