<?php

setlocale(LC_TIME,"ru_RU.utf8");

require '../../vendor/autoload.php';

session_name('office-session');
session_start();

$app = new \popcorn\app\OfficeApp();
$app->run();

function echo_arr($a, $die = false) {

	echo '<pre>' . print_r($a, 1) . '</pre>';
	if (!$die) {
		die();
	}

}

function run_time_logger($file_name, $mode, $text) {
	$file_name = '/data/sites/popcorn/logs/' . $file_name;
	file_put_contents($file_name, '[' . date('d.m.Y H:i:s') . '] ' . $mode . ': [' . $text . "]\n", FILE_APPEND | LOCK_EX);
}
