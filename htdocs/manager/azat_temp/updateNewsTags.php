<?php

/**
 * @author Azat Khuzhin
 * 
 * Update news tags
 */
die('Every thing is up to date (Wed Dec  8 18:11:25 MSK 2010)' . "\n");

$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/data/libs/compat.lib.php';

/**
 * Transport tags to new table
 * 
 * @param array $data
 * @param string $type - enum(events, persons)
 */
function transport($data, $type) {
	foreach ($data as &$row) {
		$id = $row['id'];
		unset($row['id']);

		$tags = array_unique(array_map('intval', array_values($row)));

		foreach ($tags as $tag) {
			if (!$tag) continue; // zero
			mysql_sprintf('INSERT INTO popcornnews_news_tags SET type = "%s", nid = %u, regtime = %u, tid = %u', $type, $id, time(), $tag);
		}
	}
}

mysql_sprintf('TRUNCATE TABLE popcornnews_news_tags');

$events = mysql_fetch_all(mysql_sprintf('SELECT id, pole27, pole28, pole29 FROM %s WHERE goods_id = 2 AND page_id = 2', $tbl_goods_));
transport($events, 'events');

$persons = mysql_fetch_all(mysql_sprintf('SELECT id, pole7, pole8, pole9, pole10, pole17, pole18, pole19, pole20, pole21, pole22, pole23, pole24, pole25, pole26 FROM %s WHERE goods_id = 2 AND page_id = 2', $tbl_goods_));
transport($persons, 'persons');
