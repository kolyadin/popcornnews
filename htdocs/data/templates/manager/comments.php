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
						<a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&nid=%u&sort=%s&sort_type=%s&page=%u', $_GET['type'], $_GET['action'], $d['nid'], $d['sort'], $d['sort_type'], $pi['link'])?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		<form method="POST" action="">
			<input type="hidden" name="type" value="comments">
			<input type="hidden" name="action" value="comments">
			
			<table cellspacing="1" class="TableFiles">
				<tr>
					<td class="TFHeader">ID</td>
					<td class="TFHeader">Содержание</td>
					<td class="TFHeader"><a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&nid=%u&sort=%s&sort_type=%s', $_GET['type'], $_GET['action'], $d['nid'], 'u.nick', $d['sort_type'] == 'DESC' ? 'ASC' : 'DESC')?>">Автор</a></td>
					<td class="TFHeader"><a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&nid=%u&sort=%s&sort_type=%s', $_GET['type'], $_GET['action'], $d['nid'], 'a.pole1', $d['sort_type'] == 'DESC' ? 'ASC' : 'DESC')?>">Дата</a></td>
					<td class="TFHeader"><a href="<?=sprintf('/manager/admin.php?type=%s&action=%s&nid=%u&sort=%s&sort_type=%s', $_GET['type'], $_GET['action'], $d['nid'], 'a.complain', $d['sort_type'] == 'DESC' ? 'ASC' : 'DESC')?>">Кол-во жалоб</a></td>
					<td class="TFHeader">Удаление</td>
					<td class="TFHeader">IP в черный список</td>
				</tr>
				<?foreach ($d['comments'] as $comment) {?>
				<tr>
					<td><?=$comment['id']?></td>
					<td><?=$comment['content']?></td>
					<td><?=$comment['unick']?></td>
					<td><?=$comment['ctime']?></td>
					<td><?=$comment['complain']?></td>
					<td>
						<label>Да<input type="radio" name="del[<?=($comment['id'])?>]" value="1"<?=($comment['del'] ? ' checked' : null)?> /></label>
						<label>Нет<input type="radio" name="del[<?=($comment['id'])?>]" value="2"<?=(!$comment['del'] ? ' checked' : null)?> /></label>
					</td>
					<td>
						<label>Да<input type="radio" name="black_ip[<?=ip2long($comment['ip'])?>]" value="1"<?=($comment['ip_black'] ? ' checked' : null)?> /></label>
						<label>Нет<input type="radio" name="black_ip[<?=ip2long($comment['ip'])?>]" value="2"<?=(!$comment['ip_black'] ? ' checked' : null)?> /></label>
					</td>
				</tr>
				<?}?>
			</table>
			<input type="submit" value="Сохранить" />
		</form>
	</body>
</html>