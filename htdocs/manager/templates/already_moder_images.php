<html>
<head>
<title>Система управления сайтом "TRAFFIC"</title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
<meta NAME="description" CONTENT="">
<meta NAME="keywords" CONTENT=''>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<LINK rel="stylesheet" type="text/css" href="styles/global.css">

<style><!--
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
--></style>

</head>

<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF" style="padding-left:15px; padding-top:10px;">
  <form method="POST" action="/manager/moder_act.php">
    <table cellspacing="1" class="TableFiles">
      <tr>
	<td colspan="20" width="100%">
<?
//pages
if (!isset($_GET['begin'])) $_GET['begin'] = 0;
if (!isset($_GET['end'])) $_GET['end'] = 30;
elseif ($_GET['end'] > 100) $_GET['end'] = 100;

$q = 'SELECT COUNT(*) FROM popkorn_user_pix WHERE moderated = 1';// count lines
$result = mysql_query($q, $link);
list($count) = mysql_fetch_row($result);
getPages($count, $_GET['end'], $pages = 10, $begin = 'begin', $end = 'end');
?>
	</td>
      </tr>
      <tr>
	<td class="TFHeader">ID</td>
	<td class="TFHeader">Картинка</td>
	<td class="TFHeader">Знаменитость</td>
	<td class="TFHeader">Автор</td><td class="TFHeader">Дата</td>
	<td class="TFHeader">Отменить</td>
	<td class="TFHeader">Удалить</td>
      </tr>
<?
// get content
$q = 'SELECT * FROM popkorn_user_pix ' .
     'WHERE moderated = 1 ORDER BY id DESC LIMIT ' . $_GET['begin'] . ', ' . $_GET['end'];
$result=mysql_query($q, $link);
if (is_resource($result))
{
  while($row=mysql_fetch_array($result,MYSQL_ASSOC))
  {
    $q="SELECT * FROM popkorn_users WHERE id=".$row['uid']." LIMIT 1";
    $info=mysql_fetch_array(mysql_query($q,$link),MYSQL_ASSOC);

    $q='SELECT id,name,pole5 as img FROM '.$tbl_goods_.' WHERE goods_id=3 AND id='.$row['gid_'];
    $artist=mysql_fetch_array(mysql_query($q,$link),MYSQL_ASSOC);
  ?>
      <tr>
	<td><?=$row['id']?></td>
	<td>
	  <a href="/upload/<?=$row['filename']?>" target="_blank" onclick="window.open(this.href,this.target,'width=400,height=700,resizable=yes, scrollbars=yes'); return false;">
	    <img src="/upload/_50_50_60_<?=$row['filename']?>" alt="">
	  </a>
	</td>
	<td>
	  <div style="background: url('/upload/_50_50_70_<?=$artist['img']?>') 10px 0 no-repeat; height:50px; padding-left:60px;">
	    <a href="/tag/<?=$artist['id']?>" target="_blank"><?=$artist['name']?></a>
	  </div>
	</td>
	<td><?=$info['nick']?></td>
	<td><?printf("%s.%s.%s",substr($row['cdate'],0,4),substr($row['cdate'],4,2),substr($row['cdate'],6,4))?></td>
	<td align="center"><input type="radio" name="aim[<?=$row['id']?>]" value="0"></td>
	<td align="center"><input type="radio" name="aim[<?=$row['id']?>]" value="2"></td>
      </tr>
  <?
  }
}
?>
      <tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Сохранить" style="font-size:12px;"></td></tr>
    </table>
  </body>
</html>
