<?

include "inc/connect.php";
//include "../func.php";
include 'inc/functions.php';

//$REQUEST=clean_request($_SERVER["REDIRECT_URL"]);
//$URL=explode("/",$REQUEST);
//$url=$URL;

// общее
$text=""; // текст и в африке текст
$maket="title.php"; // макет по умолчанию 
$roothead=""; // Заголовок меню
$title="попкорнnews";
$clouds=""; // облака справа
$clouds2="";
$right_arch=""; // блок со ссылками на архив новостей


ob_start();
require_once('templates/moder_images.php');
$text.=ob_get_clean ();
echo $text;
?>