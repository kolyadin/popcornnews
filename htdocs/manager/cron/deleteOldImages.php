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
	// $rev = str_split(strrev($pic['goods_id_']));
	// preg_match('@\.(.+)$@Uis', $pic['diskname'], $ext);
	// $ext = strtolower($ext[1]);
	// $newName = sprintf('/upload/news_images/%u/%u/%u/%u/%s.%s', $rev[0], $rev[1], $rev[2], $pic['goods_id_'], md5($pic['diskname']), $ext);

	rename(sprintf('%s/upload/%s', root, $pic['diskname']), sprintf('/data/sites/azat/images/old/%s', $pic['diskname']));
	printf('[mv] %s %s' . "\n", sprintf('%s/upload/%s', root, $pic['diskname']), sprintf('/data/sites/azat/images/old/%s', $pic['diskname']));
	foreach (glob(sprintf('%s/upload/_[0-9]*_[0-9]*_[0-9]*_%s', root, $pic['diskname']), GLOB_NOSORT) as $file) {
		unlink($file);
		printf('[un] %s' . "\n", $file);
	}
}
