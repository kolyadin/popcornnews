<?php

setlocale(LC_TIME, "ru_RU.utf8");

require '../vendor/autoload.php';

if (\popcorn\lib\Config::getMode() != 'production') {
//	xhprof_enable();
}

session_name('popcorn-session');
session_start();

$t = microtime(1);

$app = new \popcorn\app\Popcorn();
$app->run();

echo microtime(1) - $t;


if (\popcorn\lib\Config::getMode() != 'production') {
//	$xhprof_data = xhprof_disable();
//
//	require_once '../vendor/facebook/xhprof/xhprof_lib/utils/xhprof_lib.php';
//	require_once '../vendor/facebook/xhprof/xhprof_lib/utils/xhprof_runs.php';
//
//	$namespace = 'popcorn';
//	$xhprof_runs = new XHProfRuns_Default();
//	$run_id = $xhprof_runs->save_run($xhprof_data, $namespace);
//
//	printf('<div style="margin:0 auto;padding:20px 0 30px 0;"><iframe width="100%%" height="1000" src="/xhprof_html/index.php?run=%s&source=%s"/></div>', $run_id, $namespace);
}

function echo_arr($a) {
	echo '<pre>';
	print_r($a);
	echo '</pre>';
}

function run_time_logger($file_name, $mode, $text) {
	$file_name = '/data/sites/popcorn/logs/' . $file_name;
	file_put_contents($file_name, '[' . date('d.m.Y H:i:s') . '] ' . $mode . ': [' . $text . "]\n", FILE_APPEND | LOCK_EX);
}