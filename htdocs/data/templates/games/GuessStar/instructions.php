<?$this->_render('inc_header', array('header' => 'Угадай звезду', 'top_code' => '*', 'title' => 'Угадай звезду'));?>
<?php
$fromProfile = strpos($_SERVER['REQUEST_URI'], 'user') !== false;
$firstTime = $fromProfile ? $d['firstTime'] : false;
?>
<style>
.quess_instructions dl dt {
	font-weight: bold;
}
</style>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active">игра</li>
			<?php if(!$firstTime && count($d['ratingData']) > 0) { ?>
			<li><a href="/games/guess_star/rating<?=($fromProfile ? '/profile' : '');?>">рейтинг участников</a></li>
			<?php } ?>
		</ul>
		<div class="start_game">
			<img class="fon" src="/i/quess/start_game_f.jpg" alt="" />
			<p>Если вам интересны знаменитости, вы следите за их карьерой или личной жизнью, то, конечно, всех их вы знаете в лицо. Мы предлагаем вам убедиться в этом, приняв участие в игре "Угадай звезду".</p>
			<a href="/games/guess_star/start"></a>
		</div>
		<div class="quess_instructions">
			<img src="/i/quess/example.jpg" width="332" height="269" alt="Вид игры" />
			<p>У вас есть 50 секунд для того, чтобы угадать как можно больше известных лиц. К каждой фотографии прилагаются 4 варианта ответа. За каждый правильный ответ к вашему времени прибавляется 3 секунды, а за неправильный снимается 10 секунд. </p>
			<p>Игра заканчивается, как только у вас заканчивается время. В ходе игры вы можете воспользоваться подсказками:</p>
			<dl>
				<dt>50/50</dt><dd>– убрать два неправильных ответа</dd>
				<dt>5 секунд</dt><dd>– дополнительные пять секунд</dd>
				<dt>пропустить</dt><dd>– пропустить звезду</dd>
			</dl>
			<p>Если вы во время игры сворачиваете страницу или переходите в другое окно – игра будет считаться законченной.</p>
		</div>
		<?php if(count($d['ratingData']) > 0) {?>
		<table class="rating_quess" cellpadding="0" cellspacing="0">
			<caption>рейтинг участников</caption>
			<col />
			<thead>
				<tr>
					<td>&nbsp;</td>
					<td style="text-align:left;color:#000;">Пользователь</td>
					<td class="quess">угадано звезд</td>
					<td class="time">время игры</td>
					<td class="attempt">попыток</td>
				</tr>
			</thead>
			<tbody>
				<?$kk=1;?>
				<?foreach ($d['ratingData'] as $row) {?>
				<tr>
					<td><?=$kk++;?></td>
					<td>
						<a rel="nofollow" href="/profile/<?=$row['uid']?>" class="ava">
							<img src="<?=$this->getStaticPath($this->getUserAvatar($row['avatara']))?>" alt="" />
							<?=htmlspecialchars($row['nick'], ENT_IGNORE, 'cp1251', false);?>
						</a>
					</td>
					<td class="quess"><?=$row['answers_right']?></td>
					<td class="time"><?=$p['time']->writeTime($row['time'])?></td>
					<td class="attempt"><?=$row['attempts']?></td>
				</tr>
				<?}?>
			</tbody>
		</table>
		<?php } ?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>