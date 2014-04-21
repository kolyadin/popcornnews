<?

$additional = ($d['page'] > 1) ? " - продолжение (стр.{$d['page']})" : '';

$this->_render('inc_header', array(
		'title'=>'Правдивые факты - ' . $d['person']['name'].$additional, 
		'meta' => array(
			'description' => sprintf('Архив правдивых фактов о %s - только проверенные факты о %s, присланные вами и проверенные сайтом Popcornnews.ru'.$additional, $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('архив фактов, проверенные, %s, %s', $d['person']['name'], $d['person']['eng_name']),
		),
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader'
));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>">персона</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news">новости</a></li>
			<?if ($p['query']->get_num('kino_films', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/kino">фильмография</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/photo">фото</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans">поклонники</a></li>
			<?if ($p['query']->get_num('puzzles', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/puzli">пазлы</a></li>
			<?}?>
			<?if ($p['query']->get_num('person_wallpapers', array('id'=>$d['person']['id'], 'name'=>$d['person']['name'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/oboi">обои</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics">фанфики</a></li>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts">факты</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>			
		</ul>
		<ul class="menu bLevel">
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/for_test">на проверку</a></li>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/true">архив правдивых</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/best">архив лучших</a></li>
			<?if (!$d['person']['no_adding_facts']) {?><li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/post">прислать факт</a></li><?}?>
		</ul>
		<h2>Правдивые факты <?=$d['person']['genitive']?></h2>
		<div class="trackContainer factsTrack">
			<?foreach ($d['facts'] as $i => &$fact) {?>
			<div class="trackItem">
				<div class="entry">
					<p><?=$fact['content']?></p>
				</div>
				<div class="factMeta">
					<div class="trust">
						<span class="mark" id="f_<?=$fact['id']?>_1"><?printf("%.1f", ($fact['trust'] / $fact['trust_votes']) / 10)?></span>
						<h4>достоверность</h4>
						<span class="counter"><?=$fact['trust_votes']?> проголосовавших</span>
						<span class="action">Верю</span>
						<noindex>
							<ul class="mark">
								<? foreach ($p['property']->_class($fact['trust'] / 10) as $i => $class) {?>
								<li class="<?=$class?>"><a href="#" onclick="alert('Голосование по этому факту закончено'); return false;" rel="<?=($i + 1)?>"><?=($i + 1)?></a></li>
								<?}?>
							</ul>
						</noindex>
					</div>
					<div class="like">
						<span class="mark" id="f_<?=$fact['id']?>_2"><?printf("%.1f", ($fact['liked'] / $fact['liked_votes']) / 10)?></span>
						<h4>оценка</h4>
						<span class="counter"><?=$fact['liked_votes']?> проголосовавших</span>
						<span class="action">Нравится</span>
						<noindex>
							<ul class="mark">
								<? foreach ($p['property']->_class($fact['liked'] / 10) as $i => $class) {?>
								<li class="<?=$class?>"><a href="#" onclick="alert('Голосование по этому факту закончено'); return false;" rel="<?=($i + 1)?>"><?=($i + 1)?></a></li>
								<?}?>
							</ul>
						</noindex>
					</div>
					<div class="sender">
						<?$user = array_shift($p['query']->get('users', array('id'=>$fact['uid']), null, 0, 1));?>
						<a class="ava" rel="nofollow" href="/profile/<?=$user['id']?>"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
						<div class="details">
							<span>Прислал<?=$user['sex'] == 1 ? '' : 'a'?></span>
							<a rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
							<div class="rating"></div>
						</div>
					</div>
				</div>
			</div>
			<?}?>
			<p class="more">Есть другие факты? <a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/post">Присылай!</a></p>
		</div>

		<?if ($d['pages'] > 0) {?>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {
					    if($pi['link'] == '1') {?>
					    <a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/<?=$d['act']?>"><?=$pi['text']?></a>
					    <?php } else { ?>
						<a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/<?=$d['act']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?}} else {?>
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