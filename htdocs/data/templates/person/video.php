<?
$this->_render('inc_header', array(
		'title'=>'Видео с ' . $d['person']['name'], 
		'meta' => array(
			'description' => sprintf('Видео с %s - клипы, трейлеры и тизеры с %s на сайте Popcornnews.ru', $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('видео, %s, %s', $d['person']['name'], $d['person']['eng_name']),
	   	),
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="<?=$handler->getBaseLink();?>">персона</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/news">новости</a></li>
			<?if ($p['query']->get_num('kino_films', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="<?=$handler->getBaseLink();?>/kino">фильмография</a></li>
			<?}?>
			<li><a href="<?=$handler->getBaseLink();?>/photo">фото</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/fans">поклонники</a></li>
			<?if ($p['query']->get_num('puzzles', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="<?=$handler->getBaseLink();?>/puzli">пазлы</a></li>
			<?}?>
			<?if ($p['query']->get_num('person_wallpapers', array('id'=>$d['person']['id'], 'name'=>$d['person']['name'])) > 0) {?>
			<li><a href="<?=$handler->getBaseLink();?>/oboi">обои</a></li>
			<?}?>
			<li><a href="<?=$handler->getBaseLink();?>/fanfics">фанфики</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/facts">факты</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li  class="active"><a href="<?=$handler->getBaseLink();?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<h2>Видео с <?=$d['person']['name']?></h2>
		<div class="VideoContainer">
			<?
			$limit = 10;
			$d['page'] = ($d['page'] > 0)?$d['page']:1;
			$offset = ($d['page'] - 1) * $limit;
			$num_films = $p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1'));
			$pages = ceil($num_films / $limit);
			if ($num_films > 0) {
				foreach ($p['query']->get('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1'), array('id desc'), $offset, $limit) as $i => $film) {
					if ($film["flv"] != '' || $film["embed"] != '') {
			?>
			<h3><span><?=$film['name'];?></span></h3>
			<?if ($film["flv"] != '') {?>
			<div class="videoblock" id="video<?=$film['id'];?>"></div>
			<script type="text/javascript">
				var so = new SWFObject('/swf/v3_flashplayer.swf', '', '580', '308', '9.0.0');
				
				so.addParam('quality', 'high');
				so.addParam('wmode', 'opaque');
				so.addParam('allowFullScreen','true');
				
				so.addVariable('vidinfo', '<?=$this->getStaticPath('/video/' . $film['flv'])?>');
				so.addVariable('menuvisible','1');
				so.addVariable('videosize',<?=(int)filesize(sprintf('%s/video/%s', WWW_DIR, $film['flv']))?>);
				so.addVariable('tr_title','<?=str_replace("'", "\'", str_replace('&quot;', "'", $film['name']))?>');
				so.addVariable('domainz', '<?=$_SERVER['HTTP_HOST']?>');
				
				// so.addParam('allowScriptAccess','sameDomain');
				so.addVariable('tr_image','');
				so.addVariable('videowidth','');
				so.addVariable('videoheight','');
				so.addVariable('truesize','0');
				so.addVariable('playingis','0');

				so.write("video<?=$film['id'];?>");
			</script>
			<?} else {?>
			<div class="videoblock" id="video<?=$film['id'];?>"><?=$film["embed"];?></div>
			<?}
				}
			}?>
			<div class="paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $pages, 10) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="<?=$handler->getBaseLink();?>/video/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
			<?} else {?>
			<strong class="no_info">Видео с <?=$d['person']['name']?> пока нет.</strong>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
