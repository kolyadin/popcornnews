<?php

/**
 * @author Azat Khuzhin
 */
die;

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
$percentQ = 100 / $rowsCount;

$i = 1;
$percents = array();
foreach (mysqlFetchAll($q) as $row) {
	if (!in_array(round($i * $percentQ), $percents) && round($i * $percentQ) == count($percents)) {
		$percents[] = round($i * $percentQ);
		printf('Item: %u, Persents: %u%%' . "\n", $i, $percents[count($percents)-1]);
	}
	
	$filesCount += count(glob(sprintf('%s_[0-9]*_[0-9]*_[0-9]*_%s', dir, $row['diskname'])));
	$i++;
}

sprintf('Rows:   %100u' . "\n", $rowsCount);
sprintf('Files:  %100u' . "\n", $filesCount);
sprintf('Time:   %uf minutes' . "\n", (microtime(true) - $timeStart) / 60);
sprintf('Memery: %s', how_many_memory_allocated(false, true));
