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
		<?
		$search = ($d['person'] != 0)?'&person=' . $d['person']:'';
		$where = $d['person'] != 0?array('person'=>$d['person']):null;
		$num_topics = $p['query']->get_num('talk_topics', $where);
		?>
		<form method='get' style='margin:0px'>
			<input name="type" type="hidden" value="topics">
			Персона: <select name='person'><option value='0'>Все</option>
				<?
				$cmd = "SELECT goods.id, goods.name, count(topics.id) topics FROM popcornnews_talk_topics topics left join popconnews_goods_ as goods on goods.id=topics.person where goods_id=3 GROUP by person ORDER BY name";

				foreach ($p['query']->get_query($cmd) as $i => $persons) {
					echo "<option value='" . $persons['id'] . "'" . ($d['person'] == $persons['id']?' selected':'') . ">" . $persons['name'] . " (" . $persons['topics'] . ")</option>";
				}
				?>
			</select><input type="submit" value="Выбрать">
		</form>
		<?
		$limit = 50;
		$d['page'] = ($d['page'] > 0)?$d['page']:1;
		$offset = $d['page'] > 1?(($d['page'] - 1) * $limit):0;

		if ($num_topics > $limit) {
			echo "<br>Страницы: ";
			foreach ($p['pager']->make($d['page'], ceil($num_topics / $limit), 20) as $i => $pi) {
				if (!isset($pi['current'])) {
					echo "<a href='./admin.php?type=topics&page=" . $pi['link'] . $search . "'>" . $pi['text'] . "</a>";
				}
				else {
					echo $pi['text'];
				}
				echo "&nbsp; ";
			}
		}
		?>
		<form method="POST" action="/manager/user_act.php">
			<a name="topper"></a>
			<table cellspacing="1" class="TableFiles">
				<tr>
					<td class="TFHeader">ID</td>
					<td class="TFHeader">Обсуждение</td>
					<td class="TFHeader">Персона</td>
					<td class="TFHeader">Автор</td>
					<td class="TFHeader">Дата</td>
					<td class="TFHeader">Удалить</td></tr>
				<?
				foreach ($p['query']->get('talk_topics', $where, array('cdate desc'), $offset, $limit) as $i => $fact) {
					$person = array_shift($p['query']->get('persons', array('id'=>$fact['person']), null, 0, 1));
					$user = array_shift($p['query']->get('users', array('id'=>$fact['uid']), null, 0, 1));
					?>
				<tr>
					<td><?=$fact['id']?></td>
					<td><a href="admin.php?type=messages&tid=<?=$fact['id']?>"><?=$fact['name']?><br><?=$fact['content']?></a></td>
					<td><?=$person['name']?></td>
					<td><?=htmlspecialchars($user['nick']);?></td>
					<td><?=date("d.m.Y H:i", $fact['cdate'])?></td>
					<td align="center"><a href="admin.php?type=topic&action=del&id=<?=$fact['id'] . $search?>" onclick="return window.confirm('Вы точно хотите удалить этот топик ?');">&raquo;</a></td>
				</tr>
					<?
				}
				?>
				<tr><td class="TFHeader" colspan="7" align="right"><input type="submit" value="Сохранить" style="font-size:12px;"></td></tr>
			</table>
		</form>
	</body>
</html>