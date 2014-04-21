<?php

if ($argc < 3) {
	printf("Usage: %s method src dst[ arg, arg, ...]\n", basename(__FILE__));
	die;
}

$filename = array_shift($argv);
$method = array_shift($argv);
$src = getenv('PWD') . '/' . array_shift($argv);
$dst = getenv('PWD') . '/' . array_shift($argv);

require_once __DIR__ . '/YourStyle_BackEnd.php';

try {
	$ys = new YourStyle_BackEnd($src, $dst);

	if (!empty($argv)) {
		var_dump(call_user_func_array(array(&$ys, $method), $argv));
	} else {
		var_dump(call_user_func(array(&$ys, $method)));
	}
} catch (Exception $e) {
	// to stderr
	$m = $e->getMessage() . "\n";
	fputs(STDERR, $m, strlen($m));
}
