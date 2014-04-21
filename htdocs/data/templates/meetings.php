<?
$this->_render('inc_header', array('title'=>'Звездные пары', 'header'=>'Звездные пары', 'top_code'=>'&#9829;', 'header_small'=>'Голосование за пары &ndash; какая пара лучше?'));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<div class="pairsTrack">
			<?
			$limit = 12;
			$offset = ($d['page'] - 1) * $limit;
			$num_meet = $p['query']->get_num('meet', array('no_show'=>1));

			foreach ($p['query']->get('meet', array('no_show'=>1), array('rating_up desc', 'id desc'), $offset, $limit) as $i => $meet) {
				if ($meet['person1'] != '') {
					$person1 = $p['query']->get('persons', array('id'=>$meet['person1']));
					$person_img1 = '<img src="' . $this->getStaticPath('/upload/_192_243_90_' . $person1[0]['main_photo']) . '" />';
					$person_bd1 = ($person1[0]['birthday'] != '')?$person1[0]['birthday']:$meet['person_bd1'];
					$person_bd1 = date('Y', time() - strtotime(str_pad($person_bd1, 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
					$person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');
				} else {
					$person_img1 = '<img src="' . $this->getStaticPath('/upload/' . $meet['person_img1']) . '" />';
					$person_bd1 = date('Y', time() - strtotime(str_pad($meet['person_bd1'], 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
					$person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');

				}
				if ($meet['person2'] != '') {
					$person2 = $p['query']->get('persons', array('id'=>$meet['person2']));
					$person_img2 = '<img src="' . $this->getStaticPath('/upload/_192_243_90_' . $person2[0]['main_photo']) . '" />';
					$person_bd2 = ($person2[0]['birthday'] != '')?$person2[0]['birthday']:$meet['person_bd2'];
					$person_bd2 = date('Y', time() - strtotime(str_pad($person_bd2, 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
					$person_bd2 .= $p['declension']->get($person_bd2, ' год', ' года', ' лет');
				} else {
					$person_img2 = '<img src="' . $this->getStaticPath('/upload/' . $meet['person_img2']) . '" />';
					$person_bd2 = date('Y', time() - strtotime(str_pad($meet['person_bd2'], 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
					$person_bd2 .= $p['declension']->get($person_bd2, ' год', ' года', ' лет');
				}
			?>
			<div class="pair">
				<h3><span><a href="/meet/<?=$meet['id']?>"><?=$meet['name']?></a></span></h3>
				<div class="pics">
					<dl>
						<dt><a href="/meet/<?=$meet['id']?>"><?=$person_img1?></a></dt>
						<dd><?=$person_bd1?></dd>
					</dl>
					<dl>
						<dt><a href="/meet/<?=$meet['id']?>"><?=$person_img2?></a></dt>
						<dd><?=$person_bd2?></dd>
					</dl>
				</div>
				<div class="stats">
					<ul class="dkvoter2" onclick="return <?=$meet['id']?>">
						<li class="up">
							<span><big><?=(int)$meet['rating_up']?></big><br/><?=$p['declension']->get($meet['rating_up'], ' голос', ' голоса', ' голосов')?></span>
							<span class="button"></span>
						</li>
						<li class="down">
							<span><big><?=(int)$meet['rating_down']?></big><br/><?=$p['declension']->get($meet['rating_down'], ' голос', ' голоса', ' голосов')?></span>
							<span class="button"></span>
						</li>
					</ul>

                    <?php $commentsCount = RoomFactory::load('meet-'.$meet['id'])->getCount(); ?>

					<a href="/meet/<?=$meet['id']?>#comments" class="cCounter"><?= ($commentsCount > 0)?
                            $commentsCount. $p['declension']->get($commentsCount, ' комментарий', ' комментария', ' комментариев'):'нет комментариев'?> </a><br />
					<a href="/meet/<?=$meet['id']?>#write" class="comment" rel="nofollow">Написать комментарий</a>
				</div>
				<div class="desc">
					<p><?=$meet['anounce']?></p>
				</div>
			</div>
			<?}?>
		</div>
		<?
		$pages = ceil($num_meet / $limit);
		if ($pages > 1) {
		?>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $pages, 50) as $i => $pi) { ?>
                        <li>
					<?if (!isset($pi['current'])) {?>
					<a href="/meet/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?} else {?>
					<?=$pi['text']?>
					<?}?>
                        </li>
				<?}?>
			</ul>
		</div>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
