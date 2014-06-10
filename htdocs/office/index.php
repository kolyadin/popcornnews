<?php

setlocale(LC_TIME,"ru_RU.utf8");

require '../../vendor/autoload.php';

session_name('office-session');
session_start();

$app = new \popcorn\app\OfficeApp();
$app->run();