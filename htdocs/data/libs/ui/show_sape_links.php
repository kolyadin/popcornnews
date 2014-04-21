<?php
/**
 * @author Azat Khuzhin
 *
 * Класс для отображения ссылок для системы sape
 */

class show_sape_links {
	/*
	 * таблица со ссылками, но без названия домена
	 */
	public $table = 'sape_links';
	/*
	 * сколько показывать ссылок на первой странице
	 */
	public $first_page_links = 500;
	/*
	 * сколько показывать ссылок на остальных страницах
	 */
	public $others_page_links = 150;
	/*
	 * название домена
	 */
	public $host = '';
	/*
	 * последний результат
	 */
	public $last_result = array();
	/*
	 * кол-во записей в таблице с ссылками
	 */
	public $num = 0;

	public function show_sape_links () {
		$this->host = $_SERVER['HTTP_HOST'];
	}

	public function show () {
		if (empty($this->last_result)) return false;

		$links = '';
		foreach ($this->last_result as $value)  {
			$links .= sprintf('<a href="http://%s/%s">.</a>', $this->host, $value);
		}
		return $links;
	}

	/**
	 * @global resource $link
	 * @param boolean $main - главная страница или нет, по умолчанию - главная
	 * @param int $first - с какого id (если не главная страница)
	 * @param int $last - по какой id (если не главная страница)
	 * @return array
	 */
	public function get($main = true, $first = null, $last = null) {
		if (!$main && (is_null($first) || is_null($last))) return false;

		if ($main == true) {
			$query = sprintf('SELECT url FROM %s ORDER BY id ASC LIMIT 0, %d', $this->table, $this->first_page_links);
		} else {
			$query = sprintf('SELECT url FROM %s WHERE (id >= %d AND id < %d) AND (id >= %d) LIMIT 0, %d', $this->table, $first, $last, $this->first_page_links, $this->others_page_links);
		}
		$result = mysql_query($query);
		while ($data = mysql_fetch_assoc($result)) {
			$this->last_result[] = $data['url'];
		}

		return $this->last_result;
	}

	public function num() {
		$query = sprintf('SELECT COUNT(*) FROM %s', $this->table);
		$result = mysql_query($query);
		$this->num = mysql_fetch_row($result);
		$this->num = $this->num[0];
		return $this->num;
	}
}
?>