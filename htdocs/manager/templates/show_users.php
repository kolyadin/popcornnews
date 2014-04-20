<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/data/libs/compat.lib.php';

function page_make($page,$pages,$num)
{
	$pgs=array();
	$pgs=array();
	for ($i=0;$i<$pages;$i++) { $pgs[]=$i+1; }
	$le=$page+$num/2-$pages;
	$start=$le<0 ? $page-$num/2 : $page-$num/2-$le;
	$start=$start>=0 ? $start : 0;
	$last=array_slice($pgs,$start,$num);
	$data=array();
	$i=0;
	//if ($page>1) { $data[0]=array('text'=>'<b>Предыдущая</b>','link'=>$page-1); $i++; }
	if ($page-$num>0) { $data[1]=array('text'=>'...','link'=>$page-$num); $i++; }

	foreach ($last as $key => $pg)
	{
		$data[$i]=array('text'=>$pg,'link'=>$pg);
		if ($page==$pg) $data[$i]['current']=true;
		$i++;
	}
	if ($page+$num<$pages) { $data[$i]=array('text'=>'...','link'=>$page+$num); $i++; }
	if ($page<$pages) { $data[$i]=array('text'=>'<b>Следующая</b>','link'=>$page+1); }
	return $data;
}

?>
<html>
<head>
<title>Система управления сайтом "TRAFFIC"</title>
	<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
	<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
	<meta NAME="description" CONTENT="">
	<meta NAME="keywords" CONTENT=''>
	<link rel="stylesheet" type="text/css" href="styles/global.css" />

	<style type="text/css">
	fieldset {
		border:1px solid #b8d1ff;
		margin:7px;
		padding-bottom:10px;
	}

	.sub {
		height:20px;
		background-color:#f4f4f4;
		border:1px solid #b8d1ff;
	}
	<!--
	.cw { font-family: Arial,Verdana,Helvetica,Tahoma; font-size: 11px; color: #FFFFFF;}
	.cwg { font-family: Arial,Verdana,Helvetica,Tahoma; font-size: 11px; color: #CCCCCC;}
	td { font-family: Arial,Verdana,Helvetica,Tahoma; font-size: 11px; color: #000000;}

	.toolbar {border-bottom:#808080 2px solid;font-size:15px;color:#000000;background:#D4D0C8;cursor: normal;}

	.progbut {border-top:#FFFFFF 2px solid;border-left:#FFFFFF 2px solid;border-bottom:#808080 2px solid;border-right:#808080 2px solid;font-size:15px;color:#000000;background:#D4D0C8; width: 100%; height: 22px; line-height: 16px; padding-left: 5px; padding-right: 5px;cursor: normal;}
	.progbutactive {border-top:#808080 2px solid;border-left:#808080 2px solid;border-bottom:#FFFFFF 2px solid;border-right:#FFFFFF 2px solid;font-size:15px;color:#000000;background:#EAE8E4; width: 100%; height: 22px; line-height: 16px; padding-left: 5px; padding-right: 5px; font-weight: bold;cursor: normal;}

	.topname {border-top:#00366F 0px solid;border-left:#00366F 0px solid;border-bottom:#00366F 0px solid;border-right:#00366F 0px solid;font-size:11px;color:#FFFFFF;background:#00366F; width: 100%; padding-left: 5px; padding-right: 5px; font-weight: bold;cursor: normal;}
	.topnamegray {border-top:#808080 0px solid;border-left:#808080 0px solid;border-bottom:#808080 0px solid;border-right:#808080 0px solid;font-size:11px;color:#FFFFFF;background:#808080; width: 100%; padding-left: 5px; padding-right: 5px; font-weight: bold;cursor: normal;}

	.tblwin {border-top:#FFFFFF 1px solid;border-left:#FFFFFF 1px solid;border-bottom:#808080 1px solid;border-right:#808080 1px solid;color:#000000;cursor: normal;}
	.tblwin2 {border-top:#FFFFFF 2px solid;border-left:#FFFFFF 2px solid;border-bottom:#808080 2px solid;border-right:#808080 2px solid;color:#000000;cursor: normal;}

	.adminname { font-family: Arial,Tahoma,Verdana,Helvetica; font-size: 20px; color: #FFFFFF; font-weight: 500; line-height: 30px;}
	.menutext { font-family: Verdana,Arial,Tahoma,Helvetica; font-size: 11px; color: #000000; text-decoration: none;}

	.shadow { FILTER: progid:DXImageTransform.Microsoft.Shadow(color='#222222', Direction=150, Strength=3) }

	.Folders {padding:10px 10px 10px 10px; margin-bottom:20px;}
	.FolderBig {width:80px; height:80px; vertical-align:top; text-align:center; margin:0px; float:left;}

	.ContextMenu {padding:2px; width:240px; white-space:nowrap;  background:#D4D0C8; }
	.ContextMenu a {width:100%; text-decoration:none; font-size:11px; color:#000000; padding:8 10 8 10;padding-left:38px;}
	.ContextMenu a:hover { font-size:11px; color:#FFFFFF; background:#000080;}

	#search {float: right;}
	--></style>

	<script type="text/javascript" src="/js/as.js"></script>
</head>

<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF" style="padding-left:15px; padding-top:10px;">
<a name="topper"></a>
<a href="./show_users.php">Все</a> &nbsp; <a href="./show_users.php?leter=other">#</a> &nbsp;
<a href="./show_users.php?leter=dig">0-9</a> &nbsp;
<?
$cmd = "select upper(substring(nick,1,1)) as let from popkorn_users where substring(nick,1,1) REGEXP '[a-z]' group by let";
$r=mysql_query($cmd,$link);
while ($s = mysql_fetch_assoc($r)) {
	echo "<a href='./show_users.php?leter=".$s["let"]."'>".$s["let"]."</a> &nbsp;";
}
echo '<br />';
$cmd = "select upper(substring(nick,1,1)) as let from popkorn_users where substring(nick,1,1) REGEXP '[а-я]' group by let";
$r=mysql_query($cmd,$link);
while ($s = mysql_fetch_assoc($r)) {
	echo "<a href='./show_users.php?leter=".urlencode($s["let"])."'>".$s["let"]."</a> &nbsp;";
}
echo "<a href='./show_users.php?leter=".urlencode('Ё')."'>E</a> &nbsp;";
?>
<div id="search">
	<form method="get" action="">
		<input type="text" name="q" value="<?=$_GET['q']?>" />
	</form>
</div>
<?

// SEARCH OPTIONS
$where = array();
if ($leter=="dig") {
	$where['SUBSTRING(nick,1,1) REGEXP'] = '[0-9]';
} elseif($leter=="other") {
	$where['SUBSTRING(nick,1,1) REGEXP'] = '[^a-zа-я0-9]';
} elseif($leter!='') {
	$where['SUBSTRING(nick,1,1) REGEXP'] = sprintf('[%s]', trim(urldecode($leter)));
}

$q = urldecode($_GET['q']);
if ($q) {
	if (preg_match('|^([a-z_\.\-]+)@([a-z]+\.)+([a-z]+)$|Uis', $q)) {
		$where['email LIKE'] = $q;
	} else {
		$where['nick LIKE'] = sprintf('%s%%', $q);
	}
	
}
if (count($where) > 0) $where = 'WHERE ' . mysql_and_join($where);
// \SEARCH OPTIONS


$limit=100;
$page=($page>0)?$page:1;
$offset=$page>1?($page-1) * $limit:0;


$cmd = "select count(*) num from popkorn_users $where";
$r=mysql_query($cmd,$link);
$s = mysql_fetch_assoc($r);
$num_users=$s['num'];

$let_search=($leter!='')?'&leter='.$leter:'';

if($num_users>$limit){
echo "<br>Страницы: ";

	foreach (page_make($page,ceil($num_users/$limit),20) as $i => $pi) {
		if (!isset($pi['current'])) {
			echo "<a href='./show_users.php?&page=".$pi['link'].$let_search."'>".$pi['text']."</a>";
		}
		else {
			echo $pi['text'];
		}
		echo "&nbsp; ";
	}
}
?>


<form method="POST" action="/manager/user_act.php">
<table cellspacing="1" class="TableFiles">
<tr>
<td class="TFHeader">ID</td>
<td class="TFHeader">Ник</td>
<td class="TFHeader">Email</td>
<td class="TFHeader">Рейтинг</td>
<td class="TFHeader">Забанить</td>
<td class="TFHeader">Удалить все комментарии</td>
</tr>
<?

$q="SELECT * FROM popkorn_users $where order by id desc limit $offset, $limit";
$result=mysql_query($q,$link);
$t_temp='';
if (is_resource($result))
{
while($row=mysql_fetch_array($result,MYSQL_ASSOC))
{
?>
<tr><td><?=$row['id']?></td><td><?=$row['nick']?></td>
	<td><?=$row['email']?></td><td><?=$row['rating']?></td>
	<td align="center" style="position:relative; z-index:0;"><a href="#topper" onclick="var v=document.getElementById('ban_<?=$row['id']?>').style.display; document.getElementById('ban_<?=$row['id']?>').style.display=v!='none' ? 'none' : 'block';/* return false;*/">&raquo;</a>
<?php
//Патамушта низя делать вложенные теги form. Читать надобно спецификации :-|
$t_temp.='	<div id="ban_'.$row['id'].'" style="display:none; position:absolute; top:20px; left:300px; width:280px; height:200px; border:1px solid #FFFFFF; background-color:#b8d1ff; z-index:10;">
	<div style="margin:1px; background-color:#FFFFFF; height:100%">
	<form method="POST" action="user_ban.php">
	<input type="hidden" name="uid" value="'.$row['id'].'">
	<fieldset>
	<legend>Забанить пользователя '.$row['nick'].'&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="document.getElementById(\'ban_'.$row['id'].'\').style.display=\'none\'; return false;" title="Закрыть окошечко"><b>X</b></a></legend>
	<div style="padding:5px;">
	<table cellspacing="1" class="TableFiles">
	<tr><td><input type="radio" name="ban" value="1" checked="checked"> до даты</td><td><input type="text" name="ban_date" value="'.date('d.m.Y',time()+86400*3).'"></td></tr>
	<tr><td colspan="2"><input type="radio" name="ban" value="2"> навсегда</td></tr>
	<tr><td colspan="2"><textarea name="comment" style="width:100%; height:80px;"></textarea></td></tr>
	<tr><td colspan="2" align="right"><input type="submit" value="Забанить" class="sub"></td></tr>
	</table>
	</div>
	</fieldset>
	</form>
	</div>
	</div>';?>
	</td>
	<td align="center"><input type="radio" name="im[<?=$row['id']?>]" value="3"></td>
	</tr>
<?
}
}
?>
<tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Сохранить" style="font-size:12px;"></td></tr>
</table>
</form>
<?=$t_temp?>
</body>
</html>