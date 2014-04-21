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

		<script type="text/javascript">
			window.t_fields=Object();
			function change_pole(pole)
			{
				var o=document.forms['frm'].elements[pole+'_'];
				o.value=document.forms['frm'].elements[pole].value.length;
			}

			function js_reset()
			{
				for (val in window.t_fields) {
					change_pole(val);
				}
			}
		</script>
	</head>

	<body>
		<a name="form"></a>
		<form method="POST" action="admin.php" name="frm" class="Fform">
			<input type="hidden" name="type" value="users">
			<?
			if (!isset($d['edit_id'])) {?>
			<input type="hidden" name="action" value="add">
				<?} else {?>
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="id" value="<?=$d['edit_id']?>">
				<?
				$editor = array_shift($p['query']->get('users', array('id'=>$d['edit_id']), null, 0, 1));
			}

			foreach ($d['fields'] as $key => $field) {
				if ($field['type'] != 'private' && isset($field['view_as']) && $field['view_as'] == 'hidden') {
					$this->_render('g_edit', array('key'=>$key, 'field'=>$field, 'editor'=>isset($editor) ? $editor : ''));
				}
			}

			foreach ($d['fields'] as $key => $field) {
				if ($field['type'] != 'private' && (!isset($field['view_as']) || $field['view_as'] != 'hidden')) {?>
					<?$this->_render('g_edit', array('key'=>$key, 'field'=>$field, 'editor'=>$editor, 'name'=>$field['human_name']));?>
					<?
				}
			}
			?>
			<table cellspacing="3" width="100%">
				<tr>
					<td><input tabindex=60 type="submit" value="Сохранить файл" class="button" style="font-weight:700"></td>
					<td><input  onClick="setTimeout(js_reset,10)" tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
					<td align="right" width="100%"></td>
				</tr>
			</table>
		</form>
		<script>
			js_reset();
		</script>
	</body>
</html>