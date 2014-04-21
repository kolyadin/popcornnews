<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 14:54
 */

ini_set('display_errors',1);
ini_set('html_errors',1);

use popcorn\app\EditorApp;

require_once '../../app/Bootstrap.php';

Bootstrap::apply(__DIR__.'/../../');

$app = new EditorApp();

$app->run();