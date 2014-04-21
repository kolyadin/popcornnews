<?php
/*
 * информер попкорна
 * пишется в файл /informer_popcorn_{$_GET['mode']}.html
 * файл удаляется каждый час
 * 
 * 33 * * * * rm /data/sites/popcornnews.ru/htdocs/data/var/informer_popcorn_*.html
*/
$filename = $_SERVER['DOCUMENT_ROOT'] . '/data/var/informer_popcorn_' . (int)$_GET['mode'] . '.html';
/*
 * если файл существует
 * просто выдаем его контент и все
 * иначе берем из базы и записываем в файл
 * ну + выводим содержимое
*/
if (file_exists($filename)) die(file_get_contents($filename));

require_once __DIR__ . '/inc/connect.php';
require_once __DIR__ . '/data/libs/config.lib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/data/libs/vpa_template.lib.php';

switch ($_GET['mode']) {
	case '240400':
		$result = mysql_query("SELECT id,name,dat,pole5,pole16 FROM $tbl_goods_ WHERE goods_id=2 ORDER BY dat DESC,id DESC limit 4", $link);
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Untitled Document</title>
</head>

<body style="margin:0px;padding:0px;">

<style>
td {
	padding-bottom:14px;
}
.bigpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#f70080;
	font-weight:bold;
}
.smallpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#f70080;
}
.date {
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	color:#6d6d6d;
}
</style>
<div style="border:1px solid black;width:237px; height:396px;overflow:hidden;">
	<div style="padding:7px 5px 5px 12px;">
		<table style="width:100%;">
';
		break;
	case '240200':
		$result = mysql_query("SELECT id,name,dat,pole5,pole16 FROM $tbl_goods_ WHERE goods_id=2 ORDER BY dat DESC,id DESC limit 2", $link);
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Untitled Document</title>
</head>

<body style="margin:0px;padding:0px;">

<style>
td {
	padding-bottom:14px;
}
.bigpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#f70080;
	font-weight:bold;
}
.smallpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#f70080;
}
.date {
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	color:#6d6d6d;
}
</style>
<div style="border:1px solid black;width:237px; height:196px;overflow:hidden;">
	<div style="padding:7px 5px 5px 12px;">
		<table style="width:100%;">
';
		break;
	case '24090':
		$result = mysql_query("SELECT id,name,dat,pole5,pole16 FROM $tbl_goods_ WHERE goods_id=2 ORDER BY dat DESC,id DESC limit 1", $link);
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Untitled Document</title>
</head>

<body style="margin:0px;padding:0px;">

<style>
td {
	padding-bottom:14px;
}
.bigpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#f70080;
	font-weight:bold;
}
.smallpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#f70080;
}
.date {
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	color:#6d6d6d;
}
</style>
<div style="border:1px solid black;width:238px; height:88px;overflow:hidden;">
	<div style="padding:1px 5px 5px 12px;">
		<table style="width:100%;">
';
		break;
	case '60090':
		$result = mysql_query("SELECT id,name,dat,pole5,pole16 FROM $tbl_goods_ WHERE goods_id=2 ORDER BY dat DESC,id DESC limit 2", $link);
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Untitled Document</title>
</head>

<body style="margin:0px;padding:0px;">


<style>
.bigpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#f70080;
	font-weight:bold;
}
.smallpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#f70080;
}
.date {
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	color:#6d6d6d;
}
</style>
<div style="border:1px solid black;width:596px; height:86px;overflow:hidden;">
	<div style="padding:3px;padding-top:2px;">
		<table style="width:100%;"><tr>';
		break;
	case '72890':
		$result = mysql_query("SELECT id,name,dat,pole5,pole16 FROM $tbl_goods_ WHERE goods_id=2 ORDER BY dat DESC,id DESC limit 2", $link);
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Untitled Document</title>
</head>

<body style="margin:0px;padding:0px;">


<style>
.bigpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#f70080;
	font-weight:bold;
}
.smallpink{
	font-family:Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#f70080;
}
.date {
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	color:#6d6d6d;
}
</style>
<div style="border:1px solid black;width:724px; height:86px;overflow:hidden;">
	<div style="padding:3px;padding-top:2px;">
		<table style="width:100%;"><tr>';
		break;
	default:die(1);
}
unset($news_title);
unset($news_url);
unset($news_date);
unset($news_comments);
unset($news_img);
if ($result) {
	while ($res = mysql_fetch_array($result)) {
		$news_title[] = $res['name'];
		$news_url[] = 'http://popcornnews.ru/news/' . $res['id'];
		$date = $res['dat'];
		$y = substr($date, 0, 4);
		$m = get_month2(substr($date, 4, 2));
		$d = substr($date, 6, 2);
		$news_date[] = $d . ' ' . $m;

        $ch = mysql_query("SELECT count(id) as cnt FROM pn_comments_news WHERE news_id = {$res['id']}");
        $comments = 0;
        if($ch) {
            $comments = mysql_fetch_assoc($ch);
            $comments = $comments['cnt'];
            mysql_free_result($ch);
        }

		if ($comments == 0)$news_comments[] = 'Нет коментариев';
		elseif(substr($comments, strlen($comments) - 1, 1) == 1 && $comments != 11)$news_comments[] = $comments . ' комментарий';
		elseif(substr($comments, strlen($comments) - 1, 1) >= 2 && substr($comments, strlen($comments) - 1, 1) < 5 && $comments != 12 && $comments != 13 && $comments != 14)$news_comments[] = $comments . ' комментария';
		else $news_comments[] = $comments . ' комментариев';

		if ($res['pole5']) {
			$news_img[] = '<img src="' . VPA_template::getInstance(true)->getStaticPath('/upload/_65_65_80_' . $res['pole5']) . '" alt="' . $res["name"] . '">';
		} else {
			$news_img[] = '<img src="/i/0.gif" alt="">';
		}
	}
}
$i = 0;
foreach ($news_title as $title) {
	switch ($_GET['mode']) {
		case '240400':
			$out .= '<tr>
				<td valign="middle" align="center" style="padding-top:2px;width:75px;height:75px;"><table width="75" height="70" style="overflow:hidden;" cellspacing="0" border="0"><tr><td valign="middle" align="center" style="width:75px;height:75px;background-color:#000000;padding-bottom:0px;">' . $news_img[$i] . '</td></tr></table></td>
				<td style="padding-left:10px;" valign="middle">
					<a class="bigpink" href="' . $news_url[$i] . '" target="_blank">' . $title . '</a><br />
					<span class="date">' . $news_date[$i] . '</span><br />
					<a class="smallpink" href="' . $news_url[$i] . '" target="_blank">' . $news_comments[$i] . '</a>
				</td>
			</tr>';
			break;
		case '240200':
			$out .= '<tr>
				<td valign="middle" align="center" style="padding-top:2px;width:75px;height:75px;"><table width="75" height="70" style="overflow:hidden;" cellspacing="0" border="0"><tr><td valign="middle" align="center" style="width:75px;height:75px;background-color:#000000;padding-bottom:0px;">' . $news_img[$i] . '</td></tr></table></td>
				<td style="padding-left:10px;" valign="middle">
					<a class="bigpink" href="' . $news_url[$i] . '" target="_blank">' . $title . '</a><br />
					<span class="date">' . $news_date[$i] . '</span><br />
					<a class="smallpink" href="' . $news_url[$i] . '" target="_blank">' . $news_comments[$i] . '</a>
				</td>
			</tr>';
			break;
		case '24090':
			$out .= '<tr>
				<td valign="middle" align="center" style="padding-top:2px;width:75px;height:75px;"><table width="75" height="70" style="overflow:hidden;" cellspacing="0" border="0"><tr><td valign="middle" align="center" style="width:75px;height:75px;background-color:#000000;padding-bottom:0px;">' . $news_img[$i] . '</td></tr></table></td>
				<td style="padding-left:10px;" valign="middle">
					<a class="bigpink" href="' . $news_url[$i] . '" target="_blank">' . $title . '</a><br />
					<span class="date">' . $news_date[$i] . '</span><br />
					<a class="smallpink" href="' . $news_url[$i] . '" target="_blank">' . $news_comments[$i] . '</a>
				</td>
			</tr>';
			break;
		case '60090':
			$out .= '	<td valign="middle" align="center" style="width:75px;height:75px;"><table width="75" height="70 style="overflow:hidden;" cellspacing="0" border="0"><tr><td valign="middle" align="center" style="width:75px;height:75px;background-color:#000000;padding-bottom:0px;">' . $news_img[$i] . '</td></tr></table></td>
				<td valign="middle" style="padding-left:5px;width:212px">
					<a class="bigpink" href="' . $news_url[$i] . '" target="_blank">' . $title . '</a><br />
					<span class="date">' . $news_date[$i] . '</span><br />
					<a class="smallpink" href="' . $news_url[$i] . '" target="_blank">' . $news_comments[$i] . '</a>
				</td>
';
			break;
		case '72890':
			$out .= '	<td valign="middle" align="center" style="width:75px;height:75px;"><table width="75" height="70 style="overflow:hidden;" cellspacing="0" border="0"><tr><td valign="middle" align="center" style="width:75px;height:75px;background-color:#000000;padding-bottom:0px;">' . $news_img[$i] . '</td></tr></table></td>
				<td valign="middle" style="padding-left:5px;width:276px">
					<a class="bigpink" href="' . $news_url[$i] . '" target="_blank">' . $title . '</a><br />
					<span class="date">' . $news_date[$i] . '</span><br />
					<a class="smallpink" href="' . $news_url[$i] . '" target="_blank">' . $news_comments[$i] . '</a>
				</td>
';
			break;
	}
	$i++;
}
switch ($_GET['mode']) {
	case'240400':
		$out .= '</table>
	</div>
</div>


</body>
</html>';
		break;
	case'240200':
		$out .= '</table>
	</div>
</div>


</body>
</html>';
		break;
	case'24090':
		$out .= '</table>
	</div>
</div>


</body>
</html>';
		break;
	case'60090':
		$out .= '</tr>

		</table>
	</div>
</div>

</body>
</html>';
		break;
	case'72890':
		$out .= '</tr>

		</table>
	</div>
</div>

</body>
</html>';
		break;
}

$handle = fopen($filename, 'w');
fputs($handle, $out, strlen($out));
fclose($handle);

die($out);
?>