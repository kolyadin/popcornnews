<?php 

$column = isset($d['column']) ? $d['column'] : null;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"  "http://www.w3.org/TR/html4/strict.dtd">
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

		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
	</head>

	<body>
		<a name="form"></a>
		<form method="POST" action="admin.php" name="frm" class="Fform">
			<input type="hidden" name="type" value="columns">
			<?
			if (is_null($column)) {?>
			<input type="hidden" name="action" value="add">
			<label for="title">Название рубрики</label><br />
			<input type="text" name="title"	value="" size="100" />
				<?} else {?>
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="cid" value="<?=$column['id'];?>">
			<label for="title">Название рубрики</label><br />
			<input type="text" name="title"	value="<?=$column['title'];?>" size="100" />
			<?php } ?>
			<table cellspacing="3" width="100%">
				<tr>
					<td><input tabindex=60 type="submit" value="Сохранить" class="button" style="font-weight:700"></td>
					<td><input tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
					<td align="right" width="100%"></td>
				</tr>
			</table>
		</form>
	</body>
</html>