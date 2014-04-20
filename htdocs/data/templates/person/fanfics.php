<? 
if(count($d['fanfics_data']) == 0) {
    header('Location: /persons/'.$handler->Name2URL($d['person']['eng_name']).'/fanfics/add');
}

$additional = ($d['page'] > 1) ? " - продолжение (стр.{$d['page']})" : '';

$this->_render('inc_header', array(
		'title'=>'Фанфики - ' . $d['person']['name'].$additional, 
		'meta' => array(
			'description' => sprintf('Фанфики (творчество поклонников) %s - литературные произведения фанов %s, фан-арт на сайте Popcornnews.ru'.$additional, $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('фанфики, фан-арт, поклонники, %s, %s', $d['person']['name'], $d['person']['eng_name']),
		),
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader'
));?>
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
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics">фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts">факты</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/all">все фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/add">прислать</a></li>
		</ul>
		<h2>Фанфики</h2>
		<div class="trackContainer fanficsTrack commentsTrack">
			<?foreach ($d['fanfics_data'] as $value) {?>
			<div class="trackItem">
				<h3><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/<?=$value['id'];?>"><?=$value['name'];?></a></h3>
				<?if ($value['attachment']) {?>
				<div class="picture"><img src="<?=$this->getStaticPath('/upload/'. $value['attachment']);?>" alt="" /></div>
				<?}?>
				<div class="entry">
					<p><?=$value['announce'];?></p>
					<a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/<?=$value['id'];?>" class="more_new">Читать дальше</a>
				</div>
				<div class="newsMeta fanficMeta">
					<span class="comments"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/<?=$value['id'];?>#comments">Комментариев <?=($value['num_comments'] ? $value['num_comments'] : 0);?></a></span>
					<span class="views">Просмотров <?=($value['num_views'] ? $value['num_views'] : 0);?></span><br />
					<span class="date"><?=$value['time_create'];?></span>
					<span class="user">
						<?$rating = $p['rating']->_class($value['user_rating']);?>
						<div class="userRating <?=$rating['class']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$rating['name']?></span>
						</div>
						<h4><a rel="nofollow" href="/profile/<?=$value['user_id']?>"><?=htmlspecialchars($value['user_nick'], ENT_IGNORE, 'cp1251', false);?></a></h4>
					</span>
					<span class="rating">
							       Понравилось?
						<span class="like">Да (<?=$value['num_like'];?>)</span>
						<span class="dislike">Нет (<?=$value['num_dislike'];?>)</span>
					</span>
				</div>
			</div>
			<?}?>
		</div>
		<div class="paginator">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {
					    if($pi['link'] == 1) { ?>
					    	<a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics"><?=$pi['text']?></a>					    
					    <?php } else { ?>					
							<a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					    <?}} else {?>
					<?=$pi['text']?>
					<?}?>
				</li>
				<?}?>
			</ul>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>