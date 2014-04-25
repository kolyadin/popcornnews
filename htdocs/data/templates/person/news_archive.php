<?
$this->_render('inc_header', array('title'=>$d['person']['name'].' архив новостей '.($d['year'] == date('Y') ? '' : '('.$d['year'].') '), 
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>">персона</a></li>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news">новости</a></li>
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
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts">факты</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<h2>Архив новостей <?=$d['person']['genitive']?></h2>
		<ul class="headlinesList">
			<?for ($i = date('Y'); $i > 2005;$i--) {
				$news_num = $p['query']->get_num('news', array('year'=>$i, 'person'=>$d['person']['id']), true);
				if (!empty($news_num)) {
			?>
			<li><h2><?if ($d['year'] == $i) {?><?=$i?><?} else {?><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news/archive/<?=$i?>"><?=$i?></a><?}?></h2></li>
			<?}
			}?>
		</ul>
		<div class="trackContainer datesTrack">
			<?for ($i = 12;$i > 0;$i--) {
				$news = $p['query']->get('news', array('date_ym_like'=>sprintf('%04u-%02u', $d['year'], $i), 'person'=>$d['person']['id']), array('newsIntDate DESC', 'id DESC'), null, null, null, false, false, false, 86400);
				if (!empty($news)) {
						$date = mktime(0, 0, 0, $i, 15, $d['year']);
			?>
			<div class="trackItem">
				<h4><?=$p['date']->unixtime($date, '%N %Y')?></h4>
				<ul>
					<?foreach ($news as $j => $new) { ?>
					<li><a href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=RoomFactory::load('news-'.$new['id'])->getCount();?>)</li>
					<?}?>
				</ul>
			</div>
			<?}
			}
			?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>