<?
$this->_render('inc_header', array('title'=>'Добавить факт - ' . $d['person']['name'], 
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
		<?if ($d['facts_num'] > 0) {?>
		<ul class="menu bLevel">
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/for_test">на проверку</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/true">архив правдивых</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/best">архив лучших</a></li>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/post">прислать факт</a></li>
		</ul>
			<?}?>
		<h2>Новый факт <?=$d['person']['genitive']?></h2>
		<div class="trackContainer factsTrack">
			<?if (!$d['person']['no_adding_facts']) {?>
			<script>
				function check_length(message){
					var maxLen = 300;
					if (message.value.length > maxLen){
						message.value = message.value.substring(0, maxLen);
					}
				}
			</script>
			<form action="/" method="POST" class="newFact">
				<input type="hidden" name="type" value="fact">
				<input type="hidden" name="action" value="post">
				<input type="hidden" name="person" value="<?=$d['person']['id']?>">
				<h3>Убедитесь, что данный факт не был опубликован ранее</h3>
				<div class="trackItem">
					<div class="entry">
						<textarea name="content" onkeyup="check_length(this)" title="Максимум 300 символов"></textarea>
					</div>
					<div class="aboutMe">
						<a class="ava" rel="nofollow" href="/profile/<?=$user['id']?>"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
						<span>Вы пишете как</span><br />
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					</div>
				</div>
				<div class="formActions">
					<input type="submit" value="отправить" />
				</div>
			</form>
			<?} else {?>
			<div class="systemMessage">
				<p>К сожалению для этой звезды нельзя присылать факты.</p>
			</div>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
