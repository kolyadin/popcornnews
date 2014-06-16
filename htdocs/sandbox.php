<?php

require '../vendor/autoload.php';

$mail = \popcorn\lib\MailHelper::getInstance();

$mail->setFrom('robot@popcornnews.ru');
$mail->addAddress('ak@localhost');
$mail->Subject = 'Уведомление о новом комментарии на сайте';
$mail->msgHTML(777);

var_dump($mail->send());