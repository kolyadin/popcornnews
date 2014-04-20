<?php 

$columns = $d['columns'];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"  "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="no-cache">
		<meta http-equiv="Cache-Control" content="no-cache">

		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
	</head>

	<body>
		<a name="topper"></a>

		<table cellspacing="1" class="TableFiles">
			<tr>
				<td class="TFHeader">ID</td>
				<td class="TFHeader">Название рубрики</td>
				<td class="TFHeader" width="20">Удалить</td></tr>
			<?

			foreach ($columns as $column) {
				?>
			<tr>
				<td><?=$column['id']?></td>
				<td width="70%"><a href="admin.php?type=columns&action=edit&cid=<?=$column['id'];?>"><?=$column['title']?></a></td>
				<td align="center"><a href="admin.php?type=columns&action=del&cid=<?=$column['id'];?>" onclick="return window.confirm('Вы точно хотите удалить эту рубрику ?');">&raquo;</a></td>
			</tr>
				<?
			}
			?>
			<tr><td class="TFHeader" colspan="7" align="right">&nbsp;</td></tr>
		</table><br />
		<a href="admin.php?type=columns&action=add">Добавить рубрику</a>
	</body>
</html>