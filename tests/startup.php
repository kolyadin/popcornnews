<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 22:57
 */

use popcorn\lib\PDOHelper;

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once 'vendor/autoload.php';
require_once 'PopcornTest.php';

spl_autoload_register(function ($class) {
    $className = str_replace('popcorn\\', '', $class);
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $className).'.php';
    if(!file_exists($file)) return;
    require_once $file;
});

function connect() {
}

function cleanUp() {
	/*
    PDOHelper::getPDO()->query("TRUNCATE pn_news");
    PDOHelper::getPDO()->query("TRUNCATE pn_tags");
    PDOHelper::getPDO()->query("TRUNCATE pn_news_tags");
    PDOHelper::getPDO()->query("TRUNCATE pn_images");
    PDOHelper::getPDO()->query("TRUNCATE pn_news_images");
    PDOHelper::getPDO()->query("TRUNCATE pn_persons");
    PDOHelper::getPDO()->query("TRUNCATE pn_persons_link");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_news");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_news_abuse");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_news_vote");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_news_subscribe");
    PDOHelper::getPDO()->query("TRUNCATE pn_users");
    PDOHelper::getPDO()->query("TRUNCATE pn_users_info");
    PDOHelper::getPDO()->query("TRUNCATE pn_users_settings");
    PDOHelper::getPDO()->query("TRUNCATE pn_voting");
    PDOHelper::getPDO()->query("TRUNCATE pn_votes");
    PDOHelper::getPDO()->query("TRUNCATE pn_opinions");
    PDOHelper::getPDO()->query("TRUNCATE pn_meetings");
    PDOHelper::getPDO()->query("TRUNCATE pn_kids");
    PDOHelper::getPDO()->query("TRUNCATE pn_albums");
    PDOHelper::getPDO()->query("TRUNCATE pn_album_images");
    PDOHelper::getPDO()->query("TRUNCATE pn_talks");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_talks");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_talks_abuse");
    PDOHelper::getPDO()->query("TRUNCATE pn_comments_talks_vote");
    PDOHelper::getPDO()->query("TRUNCATE pn_groups");
    PDOHelper::getPDO()->query("TRUNCATE pn_groups_tags");*/
}