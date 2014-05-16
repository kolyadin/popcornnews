<?php

require '../vendor/autoload.php';

$pdo = \popcorn\lib\PDOHelper::getPDO();

$stmt = $pdo->prepare('select count(*) from pn_news where editDate > (select max_doc_datetime from sph_counter where counter_id = "news")');

while (true) {
	$stmt->execute();
	$count = $stmt->fetchColumn();

	if ($count) {

	}

	sleep(1);
}