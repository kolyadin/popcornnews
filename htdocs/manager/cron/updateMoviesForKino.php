<?php

/**
 * File: updateMoviesForKino
 * Date begin: Apr 1, 2011
 * 
 * #5052
 * 
 * @package popcornnews
 * @author Azat Khuzhin
 */

die('Every thing is up to date');

require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../inc/connect.php';
require_once dirname(__FILE__) . '/../../data/libs/compat.lib.php';

// get all news
$news = mysql_fetch_all(mysql_sprintf('SELECT id, pole30 moviesString, pole42 moviesIntString FROM %s WHERE goods_id = 2 AND page_id = 2 AND pole30 != ""', $tbl_goods_));
foreach ($news as &$new) {
	// update info
	$movies = array_not_empty(array_map('trim', preg_split('@,@Uis', $new['moviesString'])));
	$moviesIds = array();
	foreach ($movies as $movie) {
		$moviesIds[] = mysql_fetch_first_column(mysql_sprintf('SELECT id FROM kinoafisha.kinoafisha_v2_goods_ WHERE goods_id = 110 AND page_id = 2 AND name LIKE "%%%s%%" LIMIT 1', $movie));
	}
	$moviesIds = array_not_empty($moviesIds);
	
	$moviesIntString = array_not_empty(array_map('trim', preg_split('@,@Uis', $new['moviesIntString'])));
	$moviesIntString = array_merge($moviesIds, $moviesIntString);
	if (empty($moviesIntString)) {
		printf('Can`t find movie[s] "%s" for new "%u"' . "\n", join(',', $movies), $new['id']);
		continue;
	}
	
	mysql_fetch_all(mysql_sprintf('UPDATE %s SET pole42 = "%s" WHERE id = %u', $tbl_goods_, join(',', $moviesIntString), $new['id']));
}
