<?php

require_once("../data/libs/config.lib.php");
require_once(UI_DIR.'admin.lib.php');

$ui = new user_base_api();
$ui->init();
$ui->close();
