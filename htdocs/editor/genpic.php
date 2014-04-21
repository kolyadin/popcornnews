<?php
require_once(realpath(__DIR__.'/../../lib/ImageGenerator.php'));

use popcorn\lib\ImageGenerator;
use popcorn\lib\ImageGeneratorException;

try {

	#/k/1b/18/1bc29b36f623ba82aaf6724fd3b16718.png

	ImageGenerator::setup(array(
		'bin' => array(
			'convert'  => '/usr/bin/convert',
			'identify' => '/usr/bin/identify',
			'mogrify'  => '/usr/bin/mogrify',
			'lock'     => '/usr/bin/flock -n'
		)
	));

	$imgGen = new ImageGenerator();

	$imgGen->setupDocumentRoot(realpath(__DIR__.'/../'));

	$imgGen->setupRemote(array(
		'http://v1.popcorn-news.ru'
	));

	$imgGen->setupDirs(array(
		'sourceDir' => '/data/sites/popcornnews.loc/htdocs/upload/auto',
		'outputDir' => '/data/sites/popcornnews.loc/htdocs/k/%%/%%',
		'locksDir'  => '/tmp'
	));

	# /data/sites/popcornnews.loc/htdocs/k/95/92/9592f14c3ccbf0083f33b2d947d7bf5b.jpg

	//По названию определяем полный путь до фотографии
	$imgGen->registerHook('source',function($oldName) use($imgGen){
		$hash = md5($oldName);

		return sprintf('%s/%s/%s/%s'
			,$imgGen->getUploadDir()
			,substr($hash,0,2)
			,substr($hash,2,3)
			,$oldName
		);
	});

	$imgGen->registerCallback('resize',function($source,$resizeOptions) use($imgGen){
		return $imgGen->convert($source,array('resize' => $resizeOptions));
	});

	$res = $imgGen->resize('alba.jpg','101x');

	echo $res , "<br/><br/>\n\n" , $res->getImgTag();

} catch (ImageGeneratorException $e) {


	echo $e->getMessage();

	echo $e->getDetails(), '<br/>';
	#echo '<pre>', $e->getTraceAsString(), '</pre>';

}