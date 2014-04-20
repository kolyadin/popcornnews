<?php

setlocale(LC_TIME,"ru_RU.utf8");
date_default_timezone_set('Europe/Moscow');

require '../../vendor/autoload.php';

session_name('office-session');
session_start();

$app = new \popcorn\app\OfficeApp();
$app->run();