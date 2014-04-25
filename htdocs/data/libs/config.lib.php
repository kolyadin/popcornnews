<?php
$root_dir = dirname(dirname(__FILE__));

/**
 * Moder's emails
 * Delete comments
 *
 * lowercase
 */
$modersEmails = array(
	'helenakruger@mail.ru',
	'azat@traf.spb.ru',
	'vera_a@bk.ru',
	'koma@tehn.ru',
	'elkyy@mail.ru',
	'subten@mail.ru',
	'svetlanak@sjs.ge',
	'svetlana.smirnova79@yandex.ru',
	'write2nadya@yandex.ru',
	'liberatore@mail.ru',
	'kari6e4ka87@mail.ru',
	'ballet95@mail.ru',
    'donflash@gmail.com',
    'anubis@t-agency.ru',
	'javoronok1983@mail.ru',
    'lelya_fomina@mail.ru',
    'editor@popcornnews.ru',
    'utyanova@yandex.ru',
);
/**
 * обновлять вручную, нужно для проверки на возможность внесения в черный список
 * 57 - служебный профиль администрации
 * @see ui/blackList/BlackList.php
 */
$modersIds = array(57,36,247,277,299,602,10543,13509,17839,29288,31017,42403,44429,60692,62689,83368,94637,107011);
/**
 * Community moders emails
 *
 * lowercase
 */
$communityModersEmails = array(
	'vera_a@bk.ru',
	'liberatore@mail.ru',
	'azat@traf.spb.ru',
);

/**
 * Emails for users who can anwser in /ask
 *
 * lowercase
 */
$whoCanAnwser = array(
	'azat@traf.spb.ru',
	'vera_a@bk.ru',
	'write2nadya@yandex.ru',
	'liberatore@mail.ru',
);

/**
 * Emails for yourstyle moderators
 */
$YSmoderEmails = array(
        'anubis@t-agency.ru',
        'donflash@gmail.com',
        'editor@popcornnews.ru',
        'lelya_fomina@mail.ru',
        'liberatore@mail.ru',
        'vera_a@bk.ru',
);

// spam's emails
$bad_emails = array(
    'yopmail',
    '12minutemail',
    '10minutemail',
    'owlpic',
    'mailinator',
    'thisisnotmyrealemail',
    'wh4f',
);

// фраза, которая будет показываться вместо удаленного комментария
define ('COMMENTS_DELETE_PHRASE', '<span class="deleted">Комментарий удален</span>');

// комментариев к новостям\встречам на страницу
define ('COMMENTS_PER_PAGE', 100);
// кол-во комментариев на страницу, для обсуждений
define ('TALKS_TOPIC_COMMENTS_PER_PAGE', 50);

define ('WWW', '/');
define ('ROOT_DIR', $root_dir);
define ('LIB_DIR', ROOT_DIR . '/libs/');
define ('AKLIB_DIR', ROOT_DIR . '/akLib/');
define ('TYPE_EXPLORER_DIR', LIB_DIR . 'vpa_type_explorer/');
define ('UI_DIR', ROOT_DIR . '/libs/ui/');
define ('WWW_DIR', realpath (ROOT_DIR . '/..'));

/**
 * Баллы, за которые пользователь получает возможность что либо делать
 */
define ('POST_FACT', 100); // добавлять факты
define ('POST_PHOTO', 0); // закачивать фото для персон
define ('POST_TALK_TOPIC', 10); // добавлять обсуждения

/*
 * подакри
 */
define ('FREE_GIFTS_LIMIT', 1); // какое кол-во бесплатных подарков может отправить пользователь в день

/**
 * локаль сайта
 */
define ('SITE_LOCALE', 'ru_RU.CP1251');
/**
 * отдаем заголовок через header с кодировкой (нужно для яндекса)
 */
define ('CODEPAGE_HEADER', 'Content-Type: text/html; charset=windows-1251');
define ('FILES_DIR', ROOT_DIR . '/upload/');
define ('SESSION_LIFETIME', 86400);
define ('STATIC_TEMPLATES', ROOT_DIR . '/templates');
define ('TPL_MODULES', LIB_DIR . '/tpl');
define ('DB_CACHE', ROOT_DIR . '/var/db');
define ('ONLINE_CACHE', ROOT_DIR . '/var/online');
define ('USE_GZIP', false);

$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
$octets = explode('.', $ip);
/*
 * включает отладку (ПРАВИТЬ DEBUG только здесь !!!)
 * @see VPA_template::isDeveloper()
 */
if (isset($_COOKIE['debug']) && $_COOKIE['debug'] == 'true') define('DEBUG', true);
else define('DEBUG', false);
/*
 * показывать все запросы которые проходят через vpa_mysql
 * @see VPA_template::isDeveloper()
 */
if (isset($_COOKIE['show_query']) && $_COOKIE['show_query'] == 'true') define('SHOW_QUERY', true);
else define('SHOW_QUERY', false);
/**
 * показывать или нет рекламу
 * показывается только для главного сайта
 */
define('SHOW_ADS', (in_array($_SERVER['HTTP_HOST'], array('popcornnews.ru', 'www.popcornnews.ru')) ? true : false));
/*
 * Использовать ли перед запросом
 * LOCK TABLE
 * UNLOCK TABLES
 * сначала было TRUE
 */
define('IS_NEED_LOCK', false);

/*
 * показывать отладку в древовидном виде
 */
define ('DEBUG_MODE_TREE', false);

/**
 * Включить/выключить поддержку трансформации путей из ЧПУ в GET запрос через 404 ошибку или mod_rewrite
 */
define('USE_REWRITE_404', 1);
// тип базы данных (mysql)
define ('DB_TYPE', 'mysql');

// хост БД
define ('DB_HOST', ':/tmp/mysql-kino.sock');
// логин пользователя БД
define ('DB_LOGIN', 'sky');
// пароль пользователя БД
define ('DB_PASS', 'uGrs7u8rN');
// имя БД
define ('DB_NAME', 'popcornnews');

// кодировка
define ('DB_CHARSET', 'cp1251');
// название проекта (используется для connect.php админки)
define ('PROJECT', 'popconnews');
// название таблицы разделов
define ('TBL_GOODS', PROJECT . '_goods');
// название таблицы файлов
define ('TBL_GOODS_', PROJECT . '_goods_');

define('JPEGOPTIM_BIN', '/usr/local/bin/jpegoptim');

define('END_OF_YEAR_RESULTS', false);
define('NO_DISPATCHER_FOR_YEAR_RESULTS', true);

// developement
define('ENV', getenv('APPLICATION_ENV'));
define('DEVELOPMENT', ENV == 'development');

// sphinx
define ('DB_SPHINX_HOST', ':/var/run/sphinxsearch/searchd-mysql.sock');
define ('PROJECT_NAME', 'popcornnews');

//всякие другие конфиги
define('NEWS_COMMENTS_CLOSE', false);
