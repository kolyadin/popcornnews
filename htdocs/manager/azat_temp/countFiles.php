<?php

/**
 * @author Azat Khuzhin
 */

$timeStart = microtime(true);
require_once sprintf('%s/../../inc/connect.php', dirname(__FILE__));
define('dir', sprintf('%s/../../upload/', dirname(__FILE__)));

function mysqlFetchAll($resource) {
	$result = array();
	while ($data = mysql_fetch_assoc($resource)) {
		$result[] = $data;
	}
	return $result;
}
function how_many_memory_allocated($as_float = true, $human = true) {
	$memory = memory_get_usage();
	if (!$human) return $memory;

	$i = 1;
	while ($memory / 1024 > 1) {
		$memory = $memory / 1024;
		switch ($i) {
			case '1' : $memory_str = 'kb';
				break;
			case '2' : $memory_str = 'mb';
				break;
			case '3' : $memory_str = 'gb';
				break;
		}
		$i++;
		if ($i > 3) return null;
	}

	return (!$as_float? ceil($memory) : $memory) . $memory_str;
}

$q = mysql_query(sprintf('SELECT diskname FROM popconnews_pix'));
$rowsCount = mysql_num_rows($q);
$filesCount++;
foreach (mysqlFetchAll($q) as $row) {
	$filesCount += count(glob(sprintf('%s_[0-9]*_[0-9]*_[0-9]*_%s', dir, $row['diskname'])));
}
sprintf('Rows:   %100u' . "\n", $rowsCount);
sprintf('Files:  %100u' . "\n", $filesCount);
sprintf('Time:   %0.2f sec.' . "\n", (microtime(true) - $timeStart));
sprintf('Memery: %s', how_many_memory_allocated(false, true));
