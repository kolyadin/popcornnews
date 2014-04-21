<?php

namespace popcorn\cli;

use popcorn\cli\command;
use popcorn\cli\entity\CommunityEntity;
use popcorn\cli\entity\GeneratorEntity;
use popcorn\cli\entity\KidEntity;
use popcorn\cli\entity\PersonEntity;
use popcorn\cli\entity\PostEntity;
use popcorn\cli\entity\UserEntity;
use Symfony\Component\Console\Application;

setlocale(LC_TIME,"ru_RU.utf8");
date_default_timezone_set('Europe/Moscow');

include '../vendor/autoload.php';


class PopcornCliApp extends Application {

    static $cliPath;

    function __construct() {
        parent::__construct('Popcorn CLI Application', '1.0');
    }

    function addEntityCommands(array $obj) {
        $this->addCommands($obj);
    }

}

$cliApp = new PopcornCliApp();

$cliApp->addEntityCommands(PersonEntity::getCommands());
$cliApp->addEntityCommands(PostEntity::getCommands());
$cliApp->addEntityCommands(UserEntity::getCommands());
$cliApp->addEntityCommands(GeneratorEntity::getCommands());
$cliApp->addEntityCommands(KidEntity::getCommands());
$cliApp->addEntityCommands(CommunityEntity::getCommands());

$cliApp->run();