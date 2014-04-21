<?php

if (isset($_SERVER['POPCORN_MODE']) && $_SERVER['POPCORN_MODE'] != 'production'){
//	xhprof_enable();
}

$t = microtime(1);

setlocale(LC_TIME,"ru_RU.utf8");
date_default_timezone_set('Europe/Moscow');

require '../vendor/autoload.php';



session_name('popcorn-session');
session_start();

$app = new \popcorn\app\Popcorn();
$app->run();

echo microtime(1) - $t;



if (isset($_SERVER['POPCORN_MODE']) && $_SERVER['POPCORN_MODE'] != 'production'){
	/*$xhprof_data = xhprof_disable();


	require_once '../vendor/facebook/xhprof/xhprof_lib/utils/xhprof_lib.php';
	require_once '../vendor/facebook/xhprof/xhprof_lib/utils/xhprof_runs.php';

	$namespace = $_SERVER['SERVER_NAME'];
	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, $namespace);

	print sprintf('<div style="width:960px;margin:0 auto;padding:20px 0 30px 0;"><a href="http://popcornnews.loc/xhprof_html/index.php?run=%s&source=%s">XHPROF</a></div>', $run_id, $namespace);*/
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
