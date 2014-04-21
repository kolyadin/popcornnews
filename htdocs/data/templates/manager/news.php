<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">

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
						<a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&nid=%u&sort=%s&sort_type=%s&page=%u&search=%s', $_GET['type'], $_GET['action'], $d['nid'], $d['sort'], $d['sort_type'], $pi['link'], $d['search'])?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		<div style="float: right;">
			<form method="get" action="" onsubmit="window.location = '<?=sprintf('/manager/admin.php?type=%s&action=%s', $_GET['type'], $_GET['action'])?>&search=' + document.getElementById('search').value; return false;">
				<label>Поиск <input type="text" value="<?=$d['search']?>" name="search" id="search"></label>
			</form>
		</div>
		<table cellspacing="1" class="TableFiles">
			<tr>
				<td class="TFHeader"><a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&sort=%s&sort_type=%s&search=%s', $_GET['type'], $_GET['action'], 'n.id', $d['sort_type'] == 'DESC' ? 'ASC' : 'DESC', $d['search'])?>">ID</a></td>
				<td class="TFHeader">Анонс</td>
				<td class="TFHeader">Дата</td>
				<td class="TFHeader">Кол-во комментариев</td>
				<td class="TFHeader"><a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&sort=%s&sort_type=%s&search=%s', $_GET['type'], $_GET['action'], 'max_complains', $d['sort_type'] == 'DESC' ? 'ASC' : 'DESC', $d['search'])?>">Максимально жалоб</a></td>
			</tr>
			<?foreach ($d['news'] as $new) {?>
			<tr>
				<td><?=sprintf('<a href="/manager/admin.php?type=%s&action=comments&nid=%u">%u</a>', $_GET['type'], $new['id'], $new['id'])?></td>
				<td><?=$new['name']?></td>
				<td><?=$new['cdate']?></td>
				<td><?=$new['num_comments']?></td>
				<td><?=$new['max_complains']?></td>
			</tr>
			<?}?>
		</table>
	</body>
</html>