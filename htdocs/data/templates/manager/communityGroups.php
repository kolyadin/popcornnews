<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
	</head>
	<body>
		<h1><?=$error?></h1>
		<div id="q">
			<form>
				<input type="hidden" name="type" value="community" />
				
				<input type="text" name="q" />
				<input type="submit" value="Поиск" />
			</form>
		</div>
		
		<?if ($d['groupsNum'] > $d['perPage']) {?>
		<div>
			<div class="paginator smaller">
				<h4>Страницы:</h4>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/manager/admin.php?type=community&page=<?=$pi['link']?>&q=<?=$d['q']?>"><?=$pi['text']?></a>
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
				<td class="TFHeader">Описание</td>
				<td class="TFHeader">Удалить</td>
			</tr>
			<?foreach ($d['groups'] as $group) {?>
			<tr>
				<td><a href="/community/group/<?=$group['id']?>" target="_blank"><?=$group['title']?></td>
				<td><?=$group['description']?></td>
				<td><a href="/manager/admin.php?type=community&action=delete&page=<?=$d['page']?>&id=<?=$group['id']?>">Удалить</a></td>
			</tr>
			<?}?>
		</table>
	</body>
</html>