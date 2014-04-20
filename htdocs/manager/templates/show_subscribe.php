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

<style>
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
--></style>

</head>

<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF" style="padding-left:15px; padding-top:10px;">
<script type="text/javascript">
function sel_all()
{
	var otb=document.getElementById('tb');
	for (var i=1;i<otb.rows.length;i++)
	{
		ls=otb.rows[i].cells.length;
		ol=otb.rows[i].cells[ls-1];
		inps=ol.getElementsByTagName('input');
		if (inps.length)
		{
			if (!inps[0].checked)
			{
				inps[0].checked=true;
			}
			else
			{
				inps[0].checked=false;
			}
		}
	}
}
</script>
<form method="POST" action="/manager/subscribe_act.php">
<table cellspacing="1" class="TableFiles" id="tb">
<tr><td class="TFHeader">ID</td><td class="TFHeader" width="30%">Ник</td><td class="TFHeader">Email</td><td class="TFHeader">Рейтинг</td><td class="TFHeader">Заспамить <input type="checkbox" value="1" onclick="sel_all()"> всех</td></tr>
<?
$q="SELECT * FROM popkorn_users WHERE daily_sub=1 order by id desc";
$result=mysql_query($q,$link);
if (is_resource($result))
{
while($row=mysql_fetch_array($result,MYSQL_ASSOC))
{
?>
<tr><td><?=$row['id']?></td><td><?=$row['nick']?></td>
	<td><?=$row['email']?></td><td><?=$row['rating']?></td>
	<td align="center"><input type="checkbox" name="im[<?=$row['id']?>]" value="2"></td>
	</tr>
<?
}
}
?>
<tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Сохранить" style="font-size:12px;"></td></tr>
</table>
</body>
</html>