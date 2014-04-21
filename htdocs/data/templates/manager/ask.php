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
		<h1><?=$error?></h1>
		<?if ($d['num'] > $d['per_page']) {?>
		<div>
			<div class="paginator smaller">
				<h4>Страницы:</h4>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/manager/admin.php?type=ask&page=<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		<table cellspacing="1" class="TableFiles">
			<tr>
				<td class="TFHeader">Название</td>
				<td class="TFHeader">Вопрос</td>
				<td class="TFHeader">Ответ</td>
				<td class="TFHeader">Ник пользователя</td>
				<td class="TFHeader">Удалить</td>
			</tr>
			<? foreach ($d['list'] as $theme) { ?>
			<tr>
				<td><?=$theme['name']?></td>
				<td><?=$theme['question']?></td>
				<td><?=$theme['anwser']?></td>
				<td><?=$theme['user_nick']?></td>
				<td><a href="/manager/admin.php?type=ask&action=delete&page=<?=$d['page']?>&id=<?=$theme['id']?>">Удалить</a></td>
			</tr>
			<? } ?>
		</table>
	</body>
</html>