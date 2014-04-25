<?
$this->_render('inc_header', array('title'=>'your.style - Сеты с ' . $d['star']['name'], 
		'header' => $d['star']['name'] . '<br /><span>'.$d['star']['eng_name'].'</span>',
		'top_code' => ($d['star']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['star']['main_photo']) . '" alt="' . $d['star']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
    	'css' => 'ys.css?d=26.03.12', 
    	'js' => 'YourStyle.js?d=26.03.12',
        'yourstyleRating' => $d['yourStyleUserRating'],
));
$handler = $d['handler'];
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>">персона</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/news">новости</a></li>
			<?if ($p['query']->get_num('kino_films', array('person'=>$d['star']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/kino">фильмография</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/photo">фото</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/fans">поклонники</a></li>
			<?if ($p['query']->get_num('puzzles', array('person'=>$d['star']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/puzli">пазлы</a></li>
			<?}?>
			<?if ($p['query']->get_num('person_wallpapers', array('id'=>$d['star']['id'], 'name'=>$d['person']['name'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/oboi">обои</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/fanfics">фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/facts">факты</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['star']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['star']['id'])) > 0) {?>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['star']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		
		<h2>Сеты с <?=$d['star']['genitive'];?></h2>
		<ul class="b-sets-roll b-sets-roll_full">
			<?foreach ($d['star']['sets'] as $set) {?>
			<li class="b-sets-roll__set">
				<a class="set-image" href="/yourstyle/set/<?=$set['id']?>"><img src="<?=$set['image'];?>" /></a>
				<h2><a href="/yourstyle/set/<?=$set['id']?>"><?=$set['title']?></a></h2>
				<div class="b-meta">
					<span class="votes-count"><?=$set['votes']?> <?=$p['declension']->get($set['votes'], 'голос', 'голоса', 'голосов')?></span>
					<a class="comments-count" href="/yourstyle/set/<?=$set['id']?>#comments"><?=$set['comments']?> <?=$p['declension']->get($set['comments'], 'комментарий', 'комментария', 'комментариев')?></a>,
					<a class="username" href="/profile/<?=$set['uid']?>"><?=$set['unick']?></a>
					<span class="sub_rating"><?=$set['urating']?></span>
				</div>
			</li>
			<?}?>
		</ul>
		
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>