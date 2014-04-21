<?php

/**
 * @author Azat Khuzhin
 *
 * Возмем пазлы с киноафишы и скопируем их к себе
 */

require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../inc/connect.php';
require_once dirname(__FILE__) . '/../../data/libs/config.lib.php';

// нужно для user.lib.php
$_SERVER['DOCUMENT_ROOT'] = WWW_DIR;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'popcornnews.ru';

require_once UI_DIR  . 'user.lib.php';
require_once LIB_DIR . 'vpa_popcornnews.lib.php';
define('PATH_CACHE', '/var/www/sites/popcornnews.ru/htdocs/upload/');
define('KINO_PATH_CACHE', 'http://www.kinoafisha.msk.ru/upload/');
define('KINO_GOODS', 'kinoafisha_v2_goods_');

error_reporting(E_ALL);
ini_set('display_errors', 'On');

/**
 * @param string $src_name - имя файла которое надо скопировать
 * @return mixed
 * string - имя нового файла, если удачно,
 * иначе false
 */
function kino_copy($src_name) {
	// проверяем, если файл существует, то меняем название
	$dst_name = $src_name;
	// расширение
	$src_ext = preg_split('/\./Uis', $src_name);
	$src_ext = '.' . strtolower($src_ext[count($src_ext)-1]);
	
	if (file_exists(PATH_CACHE . $dst_name)) {
		while (file_exists(PATH_CACHE . $dst_name)) {
			$dst_name = rand_str() . $src_ext;
		}
	}
	// копируем
	if (copy(KINO_PATH_CACHE . $src_name, PATH_CACHE . $dst_name)) {
		return $dst_name;
	}

	return false;

}

/*
 * БАЗА
 */
$main = new user_base_api();
/*
 * шаблонные прибомбасы
 */
$tpl = $main->tpl;
/**
 * Коннект к киноафише
 */
$link_kino = mysql_connect('217.112.36.238:3308', 'sky', 'uGrs7u8rN');
if (!$link_kino) {
	cat('Can`t connect to kinoafisha (' . mysql_error() . ')' . "\n");
	die;
}
mysql_query('use kinoafisha', $link_kino);

// возьмем список актеров
$persons = new VPA_table_persons_tiny_ajax;
$persons->get($persons_list, null, null, null);
$persons_list->get($persons_list);
foreach ($persons_list as $person) {
	$puzzles_kino = mysql_query('SELECT id, name, pole1 small, pole2 big FROM ' . KINO_GOODS . ' WHERE (goods_id = 122 AND page_id = 2 AND pole16 = "") AND pole15 LIKE "%' . $person['name'] . '%"', $link_kino);
	if (mysql_num_rows($puzzles_kino) > 0) {
		while ($puzzle = mysql_fetch_assoc($puzzles_kino)) {
			// копируем большую картинку
			$big = kino_copy($puzzle['big']);
			// копируем маленькую картинку
			$small = ($puzzle['small'] ? kino_copy($puzzle['small']) : null);

			if ($big || $small) {
				// заносим в базу попкорна
				$query = sprintf(
					'INSERT INTO %s SET name = "%s", pole1 = "%s", pole2 = "%s", pole3 = "%s", page_id = 2, goods_id = 8',
					$tbl_goods_, $person['name'], $big, $small, $person['id']
				);
				// апдейтим поле на киноафише что взяли этот пазл
				$query_kino = sprintf('UPDATE %s SET pole16 = "Yes" WHERE id = %d', KINO_GOODS, $puzzle['id']);
				if (mysql_query($query, $link) && mysql_query($query_kino, $link_kino)) {
					cat(
						sprintf(
							'Добавлен пазл c id = %d, Персона: %s',
							$puzzle['id'], $person['name']
						)
					);
					// удачно
					continue;
				}
			}
			// ошибка
			cat(
				sprintf(
					'Ошибка при добавление пазла c id = %d, Персона: %s (%s, %s)',
					$puzzle['id'], $person['name'], mysql_error($link), mysql_error($link_kino)
				)
			);
		}
	}
}
cat('Подборка пазлов завершена');
?>