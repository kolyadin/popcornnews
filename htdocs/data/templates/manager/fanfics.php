<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
		<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
		<meta NAME="description" CONTENT="">
		<meta NAME="keywords" CONTENT=''>
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Cache-Control" content="no-cache">

		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">

	</head>
	<body>
		<?
		$search = ($d['person'] != 0) ? '&person=' . $d['person'] : '';
		$where = ($d['person'] != 0 ? array('person'=>$d['person']) : null);
		$num_fanfics = $p['query']->get_num('fanfics', $where);
		?>
		<form method="get" style="margin:0px">
			<input name="type" type="hidden" value="fanfics">
			Персона:
			<select name='person'><option value='0'>Все</option>
				<?
				$cmd = 'SELECT goods.id, goods.name, count(fanfics.id) fanfics FROM popcornnews_fanfics fanfics left join popconnews_goods_ as goods on goods.id = fanfics.pid where goods_id = 3 GROUP by pid ORDER BY name';
				foreach ($p['query']->get_query($cmd) as $i => $persons) {
					printf(
						'<option value="%d"%s>%s (%d)</option>',
						$persons['id'], ($d['person'] == $persons['id']?' selected':''), $persons['name'], $persons['fanfics']
					);
				}
				?>
			</select>
			<input type="submit" value="Выбрать">
		</form>
		<br>Страницы:
		<?
		$limit = 50;
		$d['page'] = ($d['page'] > 0)?$d['page']:1;
		$offset = $d['page'] > 1?(($d['page'] - 1) * $limit):0;

		if ($num_fanfics > $limit) {
			foreach ($p['pager']->make($d['page'], ceil($num_fanfics / $limit), 20) as $i => $pi) {
				if (!isset($pi['current'])) {
					printf('<a href="./admin.php?type=fanfics&page=%d%s">%s</a>', $pi['link'], $search, $pi['text']);
				} else {
					echo $pi['text'];
				}
				echo "&nbsp; ";
			}
		}
		?>
		<form method="POST" action="">
			<input type="hidden" name="type" value="fanfics">
			<input type="hidden" name="action" value="edit">
			<a name="topper"></a>
			<table cellspacing="1" class="TableFiles">
				<tr>
					<td class="TFHeader">ID</td>
					<td class="TFHeader">Название</td>
					<td class="TFHeader">Персона</td>
					<td class="TFHeader">Автор</td>
					<td class="TFHeader">Дата</td>
					<td class="TFHeader">Действия</td>
				</tr>
				<?
				foreach ($p['query']->get('fanfics_admin', $where, array('fanfics.time_create desc, fanfics.enabled asc'), $offset, $limit) as $i => $fanfic) {
					$person = array_shift($p['query']->get('persons', array('id'=>$fanfic['pid']), null, 0, 1));
					$user = array_shift($p['query']->get('users', array('id'=>$fanfic['uid']), null, 0, 1));
					?>
				<tr>
					<td><?=$fanfic['id']?></td>
					<td><a href="admin.php?type=fanfic_comments&fid=<?=$fanfic['id']?>"><?=$fanfic['name']?><br><?=substr($fanfic['content'], 0, 50) . '...'?></a></td>
					<td><?=$fanfic['artist_name']?></td>
					<td><?=htmlspecialchars($fanfic['nick']);?></td>
					<td><?=$fanfic['time_create']?></td>
					<td align="center">
						<p><b><?=($fanfic['enabled'] != 0 ? 'Разрешен' : 'Запрещен')?></b></p>
						<p>
							<label>Удалить<input type="radio" value="7" name="data[<?=$fanfic['id']?>]"></label>
							<label>Разрешить<input type="radio" value="1" name="data[<?=$fanfic['id']?>]"></label>
							<label>Запретить<input type="radio" value="0" name="data[<?=$fanfic['id']?>]"></label>
						</p>
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