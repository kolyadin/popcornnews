<?php
// файл поздравление посетителей с днем рождением запускается по крону

$PATH = "/var/www/sites/popcornnews.ru/htdocs/";
include $PATH . "inc/connect.php";
require_once $PATH . 'data/libs/vpa_mail.lib.php';

$tbl_cities = "popcornnews_cities";
$tbl_countries = "popcornnews_countries";
$tbl_facts = "popcornnews_facts";
$tbl_facts_votes = "popcornnews_fact_votes";
$tbl_friends = "popkorn_friends";
$tbl_comments = "popconnews_comments";
$tbl_users = "popkorn_users";
$tbl_user_pix = "popkorn_user_pix";

$title = 'Поздравляем с днем рождения';
$maket = file_get_contents($PATH . "data/templates/mail/message.inc");
$maket = str_replace('<#title>', $title, $maket);
$cmd = "select * from $tbl_users where substring(birthday,5,4)=" . date("md");
$line = mysql_query($cmd, $link);
while ($s = mysql_fetch_assoc($line)) {
	$message = ($s['sex'] != '' ? ($s['sex'] == 1 ? 'Уважаемый ' : 'Уважаемая ') : '') . $s['nick'] . '! Администрация сайта от всей души поздравляет Вас с днем рождения! Мы желаем Вам счастья, здоровья и успехов во всех начинаниях.';
	$content = str_replace('<#message>', $message, $maket);
	if (html_mime_mail::getInstance()->quick_send(sprintf('"%s" <%s>', htmlspecialchars($s['nick']), $s['email']), $title, $content)) {
		printf('Message send for: %100s' . "\n", $s['nick']);
	} else {
		printf('Error while sending message for: %100s' . "\n", $s['nick']);
	}
}
