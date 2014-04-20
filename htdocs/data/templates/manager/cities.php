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
		<script type="text/javascript">
			function change_pole(pole)
			{
				var o=document.forms['frm'].elements[pole+'_'];
				o.value=document.forms['frm'].elements[pole].value.length;
			}

			function js_reset()
			{
				change_pole('name');
			}
		</script>
	</head>

	<body>
		<a name="topper"></a>
		<form method="POST" action="/manager/slq_query.php" name="wfm">
			<input type="hidden" name="country_id" value="<?=$d['country_id']?>">
			<input type="hidden" name="action" value="city_skip">
			<table cellspacing="1" class="TableFiles">
				<? $country = array_shift($p['query']->get('countries', array('id'=>$d['country_id']), null, 0, 1));?>
				<tr><td colspan="5"><a href="admin.php?type=countries">Страны</a> &raquo; <?=$country['name']?></td></tr>
				<tr>
					<td class="TFHeader">ID</td>
					<td class="TFHeader">Название</td>
					<td class="TFHeader">Рейтинг</td>
					<td class="TFHeader">Скрыть</td>
					<td class="TFHeader">Редактировать</td>
					<td class="TFHeader">Удалить</td></tr>
				<?
				foreach ($p['query']->get('cities', array('country_id'=>$d['country_id']), array('rating'), null, null) as $i => $country) {?>
				<tr>
					<td><?=$country['id']?></td>
					<td width="70%"><?=$country['name']?></td>
					<td><?=$country['rating']?></td>
					<td><input type="checkbox" name="skip_ids[]" value="<?=$country['id']?>"<?=$country['skip'] == 1?' checked':''?>></td>
					<td><a href="admin.php?type=city&action=edit&id=<?=$country['id']?>&country_id=<?=$d['country_id']?>#form">&raquo;</a></td>
					<td align="center"><a href="admin.php?type=city&action=del&id=<?=$country['id']?>&country_id=<?=$d['country_id']?>" onclick="return window.confirm('Вы точно хотите удалить этот город ?');">&raquo;</a></td>
				</tr>
					<?
				}
				?>
				<tr><td class="TFHeader" colspan="5" align="right"><input type="submit" value="Выполнить"></td><td><a href="admin.php?type=cities&country_id=<?=$d['country_id']?>#form">Добавить новую</a></td></tr>
			</table>
		</form>
		<a name="form"></a>
		<form method="POST" action="admin.php" name="frm">
			<input type="hidden" name="type" value="city">
			<input type="hidden" name="country_id" value="<?=$d['country_id']?>">
			<?if (!isset($d['edit_id'])) {?>
			<input type="hidden" name="action" value="add">
				<?} else {?>
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="id" value="<?=$d['edit_id']?>">
				<?
				$edit = array_shift($p['query']->get('cities', array('id'=>$d['edit_id']), null, 0, 1));
			}?>
			<div class="FName">
				<h5>Название файла</h5>
				<table cellspacing="1" width="100%">
					<tr>
						<td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="name" value="<?=@$edit['name']?>" onChange="change_pole('name')" onPaste="change_pole('name')" onKeyUp="change_pole('name')" tabindex=1></td>
						<td class="FStat"><input class="Fcfreadonly" type="text" value="" readonly="readonly" title="Количество символов" name="name_"></td>
					</tr>
				</table>
			</div>
			<div class="FName">
				<h5>Рейтинг (чем меньше число - тем выше страна в списке)</h5>
				<table cellspacing="1" width="100%">
					<tr>
						<td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="rating" value="<?=@$edit['rating']?>" onChange="change_pole('rating')" onPaste="change_pole('rating')" onKeyUp="change_pole('rating')" tabindex=1></td>
						<td class="FStat"><input class="Fcfreadonly" type="text" value="" readonly="readonly" title="Количество символов" name="rating_"></td>
					</tr>
				</table>
			</div>
			<table cellspacing="3" width="100%">
				<tr>
					<td><input tabindex=60 type="submit" value="Сохранить файл" class="button" style="font-weight:700"></td>
					<td><input  onClick="setTimeout(js_reset,10)" tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
					<td align="right" width="100%"></td>
				</tr>
			</table>
		</form>
	</body>
</html>
