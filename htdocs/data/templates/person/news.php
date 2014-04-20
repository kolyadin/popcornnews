<?
$this->_render('inc_header', array(
		'title'=>'Новости о персоне '.$d['person']['name'], 
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'meta' => array(
			'description' => sprintf('Новости %s - все все последние события с участием %s и их обсуждение на сайте Popcornnews.ru', $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('новости, %s, %s', $d['person']['name'], $d['person']['eng_name']),
		),
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader'
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
		<div class="newsTrack">
			<?php 
			foreach ($p['query']->get('news_with_tags', array('person'=>$d['person']['id'], 'cdate_gt'=>'0000-00-00'), array('newsIntDate DESC', 'id DESC'), 0, 3) as $i => $new) {
			  
                $persons = $p['query']->get('persons', array('ids'=>$new['ids_persons']));
                $events = $p['query']->get('events', array('ids'=>$new['ids_events']));
                
                //$new_link = ((count($persons) == 1) && in_array($d['person'], $persons)) ? "/persons/".$handler->Name2URL($d['person']['eng_name'])."/news/{$new['id']}" : "/news/{$new['id']}";
                $new_link = "/news/{$new['id']}";
			?>
			<div class="trackItem">
				<h3><a href="<?=$new_link;?>"><?=$new['name']?></a></h3>
				<div class="imagesContainer">
					<a href="<?=$new_link;?>"><img src="<?=$this->getStaticPath('/upload/_500_600_80_' . $new['main_photo'])?>" /></a>
				</div>
				<div class="entry">
					<?=$new['anounce']?>
					<a href="<?=$new_link;?>" class="more_new">Читать дальше</a>
				</div>
				<div class="newsMeta">
					<span class="comments"><a href="<?=$new_link;?>#comments">Комментариев: <?=RoomFactory::load('news-'.$new['id'])->getCount();?></a></span>
					<span class="views">Просмотров <?=$new['views']?></span><br />
					<span class="tags">Тэги:
						<?
						foreach ($persons as $i => $person) {?>
						<a href="/persons/<?=$handler->Name2URL($person['eng_name']);?>"><?=$person['name']?></a><?if ($i < count($persons) - 1) {?>, <?}?>
						<?}?>
						<?if (!empty($persons) && !empty($events)) {?>,<?};?>
						<?foreach ($events as $i => $event) {?>
						<a href="/event/<?=$event['id']?>"><?=$event['name']?></a>
						<?if ($i < count($events) - 1) {?>,<?}?>
						<?}?>
					</span>
				</div>
			</div>
			<?}?>
		</div>
		<div class="pastHeadline">
			<h2>ранее</h2>
		</div>
		<div class="trackContainer datesTrack">
			<?
			for ($date = strtotime(date('Y-m-d') . ' ' . date('H') . ':00:00'), $i = 0; $i < 3; $i++, $date = strtotime('-1 month', $date)) {
				$offset = date('mY', strtotime(date('Y-m-d') . ' ' . date('H') . ':00:00')) == date('mY', $date) ? 3 : 0;
				$news = $p['query']->get('news', array('date_ym_like' => date('Y-m', $date), 'person' => $d['person']['id']), array('newsIntDate DESC', 'id DESC'), $offset, 5, null, true);

				if (!empty($news)) {
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
			}?>
			<h4><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news/archive">архив новостей</a></h4>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>