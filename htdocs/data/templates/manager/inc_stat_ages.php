<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Возраст</td>
		<td class="TFHeader">График</td>
		<td class="TFHeader">Пользователей</td>
		<td class="TFHeader">%</td>
		<?
		$all = $p['query']->get_num('users', null);
		$info = $p['query']->get('stat_ages', null, array('age'));
		$sum = 0;
		$step = 2000 / ceil($info[0]['count'] / $all * 1000);
		$stat = array(
		    0=>array('age'=>'0-5', 'count'=>0),
		    1=>array('age'=>'6-10', 'count'=>0),
		    2=>array('age'=>'11-15', 'count'=>0),
		    3=>array('age'=>'16-20', 'count'=>0),
		    4=>array('age'=>'21-25', 'count'=>0),
		    5=>array('age'=>'26-30', 'count'=>0),
		    6=>array('age'=>'31-35', 'count'=>0),
		    7=>array('age'=>'36-40', 'count'=>0),
		    8=>array('age'=>'41-45', 'count'=>0),
		    9=>array('age'=>'не указан', 'count'=>0),
		);
		foreach ($info as $i => $k) {
			if ($k['age'] != 'не указан' && $k['age'] >= 0 && $k['age'] <= 5) {
				$stat[0]['count'] += $k['count'];
			}
			if ($k['age'] > 5 && $k['age'] <= 10) {
				$stat[1]['count'] += $k['count'];
			}
			if ($k['age'] > 10 && $k['age'] <= 15) {
				$stat[2]['count'] += $k['count'];
			}
			if ($k['age'] > 15 && $k['age'] <= 20) {
				$stat[3]['count'] += $k['count'];
			}
			if ($k['age'] > 20 && $k['age'] <= 25) {
				$stat[4]['count'] += $k['count'];
			}
			if ($k['age'] > 26 && $k['age'] <= 30) {
				$stat[5]['count'] += $k['count'];
			}
			if ($k['age'] > 30 && $k['age'] <= 35) {
				$stat[6]['count'] += $k['count'];
			}
			if ($k['age'] > 35 && $k['age'] <= 40) {
				$stat[7]['count'] += $k['count'];
			}
			if ($k['age'] > 40 && $k['age'] <= 45) {
				$stat[8]['count'] += $k['count'];
			}
			if ($k['age'] == 'не указан') {
				$stat[9]['count'] = $k['count'];
			}
		}

		foreach ($stat as $i => $country) {
			$proc = $country['count'] / $all * 100;
			$sum += $proc;?>
	<tr>
		<td><?=$country['age']?></td>
		<td width="200"><div style="width:<?=ceil($step * $proc)?>px; height:15px; background-color:#FF0000;"></div></td>
		<td><?=$country['count']?></td>
		<td><?printf("%.1f", $proc)?></td>
	</tr>
			<?
		}
		?>
	<tr><td class="TFHeader" colspan="3" align="right">Итого</td><td class="TFHeader"><?printf("%.1f", $sum)?> %</td></tr>
</table>
