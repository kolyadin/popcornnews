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
$pics = mysql_fetch_all(mysql_sprintf('SELECT id, diskname, goods_id_, seq, regtime FROM popconnews_pix WHERE goods_id_ IN (SELECT id FROM popconnews_goods_ WHERE goods_id = 2 AND page_id = 2)'));
foreach ($pics as &$pic) {
	$rev = str_split(strrev($pic['goods_id_']));
	preg_match('@\.(.+)$@Uis', $pic['diskname'], $ext);
	$ext = strtolower($ext[1]);
	$newName = sprintf('/upload/news_images/%u/%u/%u/%u/%s.%s', $rev[0], $rev[1], $rev[2], $pic['goods_id_'], md5($pic['diskname']), $ext);

	if (mysql_sprintf('INSERT INTO popcornnews_news_images SET filepath = "%s", news_id = %u, seq = %u, timestamp = "%s"', $newName, $pic['goods_id_'], $pic['seq'], $pic['regtime'])) {
		// create dirs
		$mkpath = realpath(root);
		$paths = explode('/', $newName);
		array_pop($paths);
		foreach ($paths as $p) {
			$mkpath .= '/' . $p;
			
			if (!is_dir($mkpath)) {
				mkdir($mkpath, 0777);
				chmod($mkpath, 0777);
			}
		}
		
		if (!copy(sprintf('%s/upload/%s', root, $pic['diskname']), sprintf('%s%s', root, $newName))) {
			printf('[err= copy] id: %u, new_id: %u, name: %s' . "\n", $pic['id'], $pic['goods_id_'], $pic['diskname']);
		}
	} else {
		printf('[err= copy] id: %u, new_id: %u, name: %s' . "\n", $pic['id'], $pic['goods_id_'], $pic['diskname']);
	}
}
