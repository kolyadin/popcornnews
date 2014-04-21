<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Пол</td>
		<td class="TFHeader">График</td>
		<td class="TFHeader">Пользователей</td>
		<td class="TFHeader">%</td>
		<?
		$all = $p['query']->get_num('users', null);
		$info = $p['query']->get('stat_sex', null, array('cnt desc'), null, null);
		$sum = 0;
		$step = 2000 / ceil($info[0]['cnt'] / $all * 1000);
		foreach ($info as $i => $country) {
			$proc = $country['cnt'] / $all * 100;
			$sum += $proc;
			?>
	<tr>
		<td><?=($country['sex'] > 0) ? ($country['sex'] == 1 ? 'мужчина' : 'женщина') : 'не указан'?></td>
		<td width="200"><div style="width:<?=ceil($step * $proc)?>px; height:15px; background-color:#FF0000;"></div></td>
		<td><?=$country['cnt']?></td>
		<td><?printf("%.1f", $proc)?></td>
	</tr>
		<?
	}
	?>
	<tr><td class="TFHeader" colspan="3" align="right">Итого</td><td class="TFHeader"><?printf("%.1f", $sum)?> %</td></tr>
</table>
