<?php

$image = $_FILES['Filedata']['tmp_name'];
$nname = microtime(1).'.jpg';


$path = realpath(dirname(__FILE__).'/../').'/upload/'.$nname;

copy($image,$path);

die(json_encode(array(
	 'fileThumb' => '/upload/'.$nname
	,'fileBig'   => '/upload/'.$nname
)));