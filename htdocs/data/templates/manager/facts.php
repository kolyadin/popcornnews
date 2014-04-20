<html>
	<head>
		<script type="text/javascript" src="/js/as.js"></script>
		<script type="text/javascript">
			function select_all_checkboxes() {
				var list = as.$('.facts');

				for (i = 0; i < list.length; i++) {
					list[i].checked = ((list[i].checked == true) ? false : true);
				}

				return false;
			}
		</script>

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
		<?=($d['enabled'] == 1)?'<a href="./admin.php?type=facts&enabled=0">обычные</a> | <b>архив</b>':'<b>обычные</b> | <a href="./admin.php?type=facts&enabled=1">архив</a>'?>
		<form method="POST" action="/manager/slq_query.php">
			<input type="hidden" name="action" value="fact_archive">
			<input type="hidden" name="order" value="<?=$d["order"]?>">
			<input type="hidden" name="enabled" value="<?=$d["enabled"]?>">
			<a name="topper"></a><table cellspacing="1" class="TableFiles">
				<tr>
					<td class="TFHeader"><a href="?type=facts&enabled=<?=$d['enabled']?>&order=id<?=$d["order"] == "id"?' desc':'';?>">ID</a></td>
					<td class="TFHeader"><a href="?type=facts&enabled=<?=$d['enabled']?>&order=name<?=$d["order"] == "name"?' desc':'';?>">Факт</a></td>
					<td class="TFHeader"><a href="?type=facts&enabled=<?=$d['enabled']?>&order=persone<?=$d["order"] == "persone"?' desc':'';?>">Персона</a></td>
					<td class="TFHeader"><a href="?type=facts&enabled=<?=$d['enabled']?>&order=unick<?=$d["order"] == "unick"?' desc':'';?>">Автор</a></td>
					<td class="TFHeader"><a href="?type=facts&enabled=<?=$d['enabled']?>&order=archive<?=$d["order"] == "archive"?' desc':'';?>">В архиве</a></td>
					<td class="TFHeader"><a href="?type=facts&enabled=<?=$d['enabled']?>&order=cdate<?=$d["order"] == "cdate"?' desc':'';?>">Дата</a></td>
					<td class="TFHeader"><a href="#" onclick="return select_all_checkboxes();">Удалить</a></td>
				</tr>
				<?

				switch ($d["order"]) {
					default:case"cdate desc":$order = array('cdate desc');
						break;
					case"cdate":$order = array('cdate');
						break;
					case"name":$order = array('name');
						break;
					case"name desc":$order = array('name desc');
						break;
					case"persone":$order = array('person1_name');
						break;
					case"persone desc":$order = array('person1_name desc');
						break;
					case"unick":$order = array('u_nick');
						break;
					case"unick desc":$order = array('u_nick desc');
						break;
					case"archive":$order = array('enabled');
						break;
					case"archive desc":$order = array('enabled desc');
		break;
	case"id":$order = array('id');
		break;
	case"id desc":$order = array('id desc');
		break;
}
foreach ($p['query']->get('admin_facts', array('enabled'=>abs($d['enabled'] - 1)), $order, null, null) as $i => $fact) {
	/* так как слишком сильно дрючится база отключил андрюхин вывод, заменил на свои left join <cmpeko3a>
	$person=array_shift($p['query']->get('persons',array('id'=>$fact['person1']),null,0,1));
	$user=array_shift($p['query']->get('users',array('id'=>$fact['uid']),null,0,1));
	*/
	?>
				<input type="hidden" name="ids[]" value="<?=$fact['id']?>">
				<tr>
					<td><?=$fact['id']?></td>
					<td><?=$fact['name']?></td>
					<td><?=$fact['person1_name']?></td>
					<td><?=$fact['u_nick']?></td>
					<td><input type="checkbox" name="ids_arch[]" value="<?=$fact['id'];?>"<?=$fact['enabled']?'':' checked';?>></td>
					<td><?=date("d.m.Y H:i", $fact['cdate'])?></td>
					<td align="center">
						<a href="admin.php?type=fact&action=del&id=<?=$fact['id']?>&order=<?=$d["order"]?>" onclick="return window.confirm('Вы точно хотите удалить этот факт ?');">&raquo;</a> &nbsp;
						<input type='checkbox' name='ids_del[]' value='<?=$fact['id']?>' class="facts">
					</td>
				</tr>
	<?
}
?>
				<tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Сохранить" style="font-size:12px;"></td></tr>
			</table>
		</form>
	</body>
</html>
