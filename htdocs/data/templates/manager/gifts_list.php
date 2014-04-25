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
		<p><a href="?type=gifts&action=add">добавить</a></p>
		<table cellspacing="1" class="TableFiles">
			<tr>
				<td class="TFHeader">Привью</td>
				<td class="TFHeader">Цена</td>
				<td class="TFHeader">Подпись</td>
				<td class="TFHeader">Разрешен</td>
			</tr>
			<?foreach ($d['gifts'] as $gift) {?>
			<tr>
				<td><img src="/gifts/<?=$gift['small_pic']?>" alt=""></td>
				<td><?=$gift['amount']?></td>
				<td><?=$gift['title']?></td>
				<td><?=($gift['enabled'] ? 'да' : 'нет')?></td>
			</tr>
			<?}?>
		</table>
	</body>
</html>