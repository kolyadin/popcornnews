<?php
include ("vpa_mail.lib.php");
include "inc/connect.php";

// общее
$text=""; // текст и в африке текст
$maket="title.php"; // макет по умолчанию 
$roothead=""; // Заголовок меню
$title="попкорнnews";
$clouds=""; // облака справа
$clouds2="";
$right_arch=""; // блок со ссылками на архив новостей

$acts=$_POST['im'];

$del_ids=$mod_ids=array();
$date=date('Ymd');
$q="select * from popconnews_goods_ where goods_id=2 and pole3='".$date."' order by id desc";
$result=mysql_query($q,$link);
$txt='<table border=0>';
while ($row=mysql_fetch_array($result,MYSQL_ASSOC ))
{
 $txt.='<tr><td>'.date('d.m.Y').'<br><a href="http://popcornnews.ru/news/'.$row['id'].'">'.$row['name'].'</a><br>'.$row['pole1'].'</td><tr>';
}
$txt.='</table>';
$sub_ids=array();
foreach ($acts as $id => $action)
{
  $sub_ids[]=$id;
}

if (!empty($sub_ids))
{
 $result=mysql_query("SELECT id,email FROM popkorn_users WHERE id IN (".join($sub_ids,',').") AND sub_date<>'".$date."'",$link);
 while ($row=mysql_fetch_array($result,MYSQL_ASSOC))
 {
  send_mail($row['email'],'Рассылка новостей за '.date('d.m.Y').' с popcornnews.ru',$txt);
  @mysql_query("UPDATE popkorn_users SET sub_date='".$date."' WHERE id='".$row['id']."'",$link);
 }
}

function send_mail($email,$subj,$message)
{
 $mail=new html_mime_mail();

 $msg=$message;
 $subj="=?windows-1251?B?".base64_encode($subj)."?=";
 $text=convert_cyr_string ($msg,"w","k");
 $mail->add_html($text);
 $mail->build_message('koi8');
 mail ($email,$subj,$mail->mime,$mail->headers);
}


header ('Location: show_subscribe.php');

?>