<?php

/**
 * @author Azat Khuzhin
 *
 * Weekly users statistics
 */

?>
<html>
	<head>
		<title>Система управления сайтом "TRAFFIC"</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
	
		<link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
		<link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
	</head>

	<body>
		<a name="topper"></a>
		<form name="" method="get">
			Выберите год
			<select name="year" onChange="document.location = '/manager/admin.php?type=weekly_stat&year=' + this.value">
				<?foreach (range(2010, date('Y')) as $year) {?>
				<option value="<?=$year?>"<?=($d['year'] == $year ? ' selected' : null)?>><?=$year?></option>
				<?}?>
			</select>
		</form>
		
		<table width="100%">
			<table cellspacing="1" class="TableFiles">
				<tr>
					<td class="TFHeader">Номер недели</td>
					<td class="TFHeader">Пользователей</td>
					<td class="TFHeader">Всего</td>
					<td class="TFHeader">%</td>
				</tr>
				<?foreach ($d['info'] as $row) {?>
				<tr>
					<td><?=$row['week']?></td>
					<td><?=$row['count']?></td>
					<td><?=$row['count_all']?></td>
					<td><?=sprintf('%.2f', ($row['count'] * $d['percent_q']))?></td>
				</tr>
				<?}?>
			</table>
		</table>
	</body>
</html>