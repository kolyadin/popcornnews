<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Страна</td>
		<td class="TFHeader">Город</td>
		<td class="TFHeader">График</td>
		<td class="TFHeader">Пользователей</td>
		<td class="TFHeader">%</td>
		<?
		$all = $p['query']->get_num('users', null);
		$info = $p['query']->get('stat_cities', null, array('count desc'), 0, 20);
		$sum = 0;
		$step = 2000 / ceil($info[0]['count'] / $all * 1000);
		foreach ($info as $i => $country) {
			$proc = $country['count'] / $all * 100;
			$sum += $proc;
			?>
	<tr>
		<td><?=$country['country']?></td>
		<td><?=($country['city_id']) ? '<b>' . $country['city'] . '</b>' : 'не указан'?></td>
		<td width="200"><div style="width:<?=ceil($step * $proc)?>px; height:15px; background-color:#FF0000;"></div></td>
		<td><?=$country['count']?></td>
		<td><?printf("%.1f", $proc)?></td>
	</tr>
		<?
	}
	?>
	<tr><td class="TFHeader" colspan="4" align="right">Итого</td><td class="TFHeader"><?printf("%.1f", $sum)?> %</td></tr>
</table>
