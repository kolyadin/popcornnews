<?php

include "inc/connect.php";

$aid=57; // ID автора бана

// общее
$text=""; // текст и в африке текст
$maket="title.php"; // макет по умолчанию 
$roothead=""; // Заголовок меню
$title="попкорнnews";
$clouds=""; // облака справа
$clouds2="";
$right_arch=""; // блок со ссылками на архив новостей

$uid=intval($_POST['uid']);
$mode=intval($_POST['ban']);
if ($mode==1)
{
 $date=trim($_POST['ban_date']);
 $dt=explode(".",$date);
 $time=mktime(23,59,59,$dt[1],$dt[0],$dt[2]);
}
else
{
 $time=-1;
}
$comment=trim($_POST['comment']);


mysql_query("UPDATE popkorn_users SET banned=1, ban_date='".$time."' WHERE id=".$uid,$link);
// mysql_query("INSERT INTO popkorn_user_msgs(uid, aid, cdate, name, content, private) VALUES('".$uid."','".$aid."','".time()."','Вы забанены','".mysql_real_escape_string($comment)."','1')",$link);
mysql_query(
	sprintf(
		'INSERT INTO popkorn_user_msgs (uid, aid, cdate, content, private) VALUES (%d, %d, %d, "%s", 1)',
		$uid, $aid, time(), str_replace('"', '\"', mysql_real_escape_string($comment))
	),
	$link
);

/*if (!empty($del_ids))
{
	@mysql_query('DELETE FROM popkorn_users WHERE id IN ('.join($del_ids,',').')',$link);
}*/
header ('Location: show_users.php');

?>