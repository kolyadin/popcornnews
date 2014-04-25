<?
$this->_render('inc_header', array('title'=>$d['person']['name'], 
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
));
$photos = $p['query']->get_num('user_pix', array('uid'=>$d['cuser']['id'], 'cdate'=>date('Ymd')));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="<?=$handler->getBaseLink();?>">персона</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/news">новости</a></li>
			<?if ($p['query']->get_num('kino_films', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="<?=$handler->getBaseLink();?>/kino">фильмография</a></li>
			<?}?>
			<li class="active"><a href="<?=$handler->getBaseLink();?>/photo">фото</a></li>
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
			<li><a href="<?=$handler->getBaseLink();?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<h2>Добавление фотографий <?=$d['person']['genitive']?></h2>
		<?
		if ($photos <= 12) $this->_render('inc_add_photos');
		else $this->_render('inc_add_photos_deny');
		?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
