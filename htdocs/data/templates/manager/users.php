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
		<a name="topper"></a>

		<form method="get" action="">
			<input type="text" name="q" value="<?=$d['query']?>" />
			<input type="submit" value="Поиск" />
		</form>
		<a href="./admin.php?type=users">Все</a> &nbsp; <a href="./admin.php?type=users&leter=other">#</a> &nbsp;
		<a href="./admin.php?type=users&leter=dig">0-9</a> &nbsp;
		<?foreach ($p['query']->get('users_first_letter', array('fl_nick'=>'[a-z]'), array('let'), null, null, array('substring(nick,1,1)')) as $i => $let) {?>
		<a href="./admin.php?type=users&leter=<?=$let["let"];?>"><?=$let["let"];?></a> &nbsp;
		<?}?>
		<?foreach ($p['query']->get('users_first_letter', array('fl_nick'=>'[а-я]'), array('let'), null, null, array('substring(nick,1,1)')) as $i => $let) {?>
		<a href="./admin.php?type=users&leter=<?=urlencode($let["let"]);?>"><?=$let["let"];?></a> &nbsp;
		<?}?>

		<?
		$limit = 100;

		$params = array();
		if ($d['leter']) $params['fl_nick'] = $d['leter'];
		if ($d['query']) {
			if (preg_match('|^([a-z_\.\-]+)@([a-z]+\.)+([a-z]+)$|Uis', $d['query'])) {
				$params['email'] = $d['query'];
			} else {
				$params['nick'] = mysql_escape_string($d['query']);
			}
		}

		$d['page'] = ($d['page'] > 0) ? $d['page'] : 1;
		$num_users = $p['query']->get_num('users', $params);
		$let_search = ($d['leter'] != '') ? '&leter=' . $d['leter'] : '';

		if ($num_users > $limit) {
			echo "<br>Страницы: ";
			foreach ($p['pager']->make($d['page'], ceil($num_users / $limit), 20) as $i => $pi) {
				if (!isset($pi['current'])) {
					echo "<a href='./admin.php?type=users&page=" . $pi['link'] . $let_search . "'>" . $pi['text'] . "</a>";
				}
				else {
					echo $pi['text'];
				}
				echo "&nbsp; ";
			}
		}
		?>

		<table cellspacing="1" class="TableFiles">
			<tr>
				<td class="TFHeader">ID</td>
				<td class="TFHeader">Ник</td>
				<td class="TFHeader">Пол</td>
				<td class="TFHeader">День рождения</td>
				<td class="TFHeader">Город</td>
				<td class="TFHeader">Редактировать</td>
				<td class="TFHeader">Удалить</td></tr>
			<?

			$offset = $d['page'] > 1?(($d['page'] - 1) * $limit):0;
			$users = $p['query']->get('users', $params, array('nick'), $offset, $limit);
			//var_dump($params);
			foreach ($users as $i => $country) {
				?>
			<tr>
				<td><?=$country['id']?></td>
				<td width="70%"><?=htmlspecialchars($country['nick']);?></td>
				<td><?=$country['sex'] == 0 ? 'не указан' : ($country['sex'] == 1 ? 'мужской' : 'женский')?></td>
				<td><?=$country['birthday'] == 0 ? 'не указан' : substr($country['birthday'], 6, 2) . "." . substr($country['birthday'], 4, 2) . "." . substr($country['birthday'], 0, 4)?></td>
				<td><?=$country['city'] == '' ? 'не указан' : $country['city']?></td>
				<td><a href="admin.php?type=users&action=edit&id=<?=$country['id']?>#form">&raquo;</a></td>
				<td align="center"><a href="admin.php?type=users&action=del&id=<?=$country['id']?>" onclick="return window.confirm('Вы точно хотите удалить этого пользователя ?');">&raquo;</a></td>
			</tr>
				<?
			}
			?>
			<tr><td class="TFHeader" colspan="7" align="right">&nbsp;</td></tr>
		</table>

	</body>
</html>