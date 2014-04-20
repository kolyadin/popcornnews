<?php

/**
 * @author Azat Khuzhin
 *
 * Переписываем все новости и персоны
 * попкорна на киноафишу
 */
error_reporting(E_ALL);
ini_set('display_errors', 'On');
set_time_limit(60*60); // 1 hour

// remote options
define ('remote_host', '217.112.36.238');
define ('remote_port', 21);
define ('remote_user', 'sky');
define ('remote_pass', 'jiamroyk');
define ('remote_timeout', 60);
define ('remote_file', '/goods2kinoafisha.txt');

// db options
define ('db_tmp_file', '/tmp/goods2kinoafisha.txt');
define ('db_socket', '/tmp/mysql-kino.sock');
define ('db_user', 'azat');
define ('db_pass', 'Juhodav7');
define ('db_port', '3308');
define ('db_name', 'popcornnews');
define ('db_condition', 'SELECT * FROM popconnews_goods_ WHERE (goods_id = 2 OR goods_id = 3) AND page_id = 2');

function cat ($str) {
	printf('%s %s' . "\n", date('Ymd His'), $str);
}

// get dump
exec(
	sprintf(
		'mysql -e"%s" -u%s -p%s --port=%s --socket=%s %s > %s',
		str_replace('"', '\"', db_condition), db_user, db_pass, db_port, db_socket, db_name, db_tmp_file
	)
);

$ftp_stream = ftp_connect(remote_host, remote_port, remote_timeout);
if ($ftp_stream) {
	ftp_login($ftp_stream, remote_user, remote_pass);
	cat ('Connected to kinoafisha');
	if (ftp_put($ftp_stream, remote_file, db_tmp_file, FTP_BINARY)) {
		cat ('File upload success');
	} else {
		cat ('Cannot upload file!');
	}
	if (ftp_close ($ftp_stream)) {
		cat ('Disconnected from kinoafisha');
	} else {
		cat ('Cannot disconnect from kinoafisha!');
	}
} else {
	cat ('Cannot connect to kinoafisha!');
}

// try to delete file
unlink (db_tmp_file);
?>