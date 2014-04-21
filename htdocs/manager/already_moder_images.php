<?
include "inc/connect.php";
include 'inc/functions.php';

// общее
$text=""; // текст и в африке текст
$maket="title.php"; // макет по умолчанию 
$roothead=""; // Заголовок меню
$title="попкорнnews";
$clouds=""; // облака справа
$clouds2="";
$right_arch=""; // блок со ссылками на архив новостей

ob_start();
require_once('templates/already_moder_images.php');
$text.=ob_get_clean();
echo $text;
?>