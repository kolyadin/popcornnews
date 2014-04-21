<?php

/**
 * Transform old images to new
 *
 * Only manual start
 *
 * @author Azat Khuzhin
 */
die('Only manual start' . "\n");

require_once dirname(__FILE__) . '/../inc/connect.php';
require_once dirname(__FILE__) . '/../../data/libs/compat.lib.php';
define('root', realpath(dirname(__FILE__) . '/../../'));

// SELECT id, CONCAT('', diskname) FROM popconnews_pix WHERE goods_id_ IN (SELECT id FROM popconnews_goods_ WHERE goods_id = 2 AND page_id = 2)
$pics = mysql_fetch_all(mysql_sprintf('SELECT name, id, diskname, goods_id_, seq, regtime FROM popconnews_pix WHERE goods_id_ IN (SELECT id FROM popconnews_goods_ WHERE goods_id = 2 AND page_id = 2)'));
foreach ($pics as &$pic) {
	$rev = str_split(strrev($pic['goods_id_']));
	preg_match('@\.(.+)$@Uis', $pic['diskname'], $ext);
	$ext = strtolower($ext[1]);
	$newName = sprintf('/upload/news_images/%u/%u/%u/%u/%s.%s', $rev[0], $rev[1], $rev[2], $pic['goods_id_'], md5($pic['diskname']), $ext);

	mysql_sprintf('UPDATE popcornnews_news_images SET name = "%s" WHERE filepath = "%s"', $pic['name'], $newName);
}
