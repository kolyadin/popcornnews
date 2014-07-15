<?php

namespace popcorn\cli;

use popcorn\cli\command;
use popcorn\cli\entity\CommunityEntity;
use popcorn\cli\entity\GeneratorEntity;
use popcorn\cli\entity\KidEntity;
use popcorn\cli\entity\PersonEntity;
use popcorn\cli\entity\PollEntity;
use popcorn\cli\entity\PostEntity;
use popcorn\cli\entity\SystemEntity;
use popcorn\cli\entity\TagEntity;
use popcorn\cli\entity\UserEntity;
use popcorn\cli\entity\YourStyleEntity;
use popcorn\cli\entity\MeetEntity;
use popcorn\lib\ImageGenerator;
use Symfony\Component\Console\Application;

setlocale(LC_TIME, 'ru_RU.utf8');
date_default_timezone_set('Europe/Moscow');

include __DIR__ . '/../vendor/autoload.php';

class PopcornCliApp extends Application {

	static $cliPath;

	function __construct() {
		parent::__construct('Popcorn CLI Application', '1.0');


	}
}

ImageGenerator::setup([
	'bin' => [
		'convert'  => '/usr/bin/convert',
		'identify' => '/usr/bin/identify',
		'mogrify'  => '/usr/bin/mogrify',
		'lock'     => '/usr/bin/flock -n'
	],
	'dir' => [
		'documentRoot' => __DIR__ . '/../htdocs',
		'source'       => __DIR__ . '/../htdocs/upload',
		'output'       => __DIR__ . '/../htdocs/k/%%/%%',
		'locks'        => '/tmp',
	]
]);

$cliApp = new PopcornCliApp();



$cliApp->addCommands(SystemEntity::getCommands());
$cliApp->addCommands(TagEntity::getCommands());
$cliApp->addCommands(PersonEntity::getCommands());
$cliApp->addCommands(PostEntity::getCommands());
$cliApp->addCommands(UserEntity::getCommands());
$cliApp->addCommands(GeneratorEntity::getCommands());
$cliApp->addCommands(KidEntity::getCommands());
$cliApp->addCommands(CommunityEntity::getCommands());
$cliApp->addCommands(PollEntity::getCommands());
$cliApp->addCommands(YourStyleEntity::getCommands());
$cliApp->addCommands(MeetEntity::getCommands());

$cliApp->run();