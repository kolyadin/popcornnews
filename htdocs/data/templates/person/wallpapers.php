<?
$this->_render('inc_header', array(
		'title'=>'ќбои ' . $d['person']['name'] . ', скачать обои на рабочий стол',
		'meta' => array(
			'description' => sprintf('ќбои %s - скачав их вы каждый день сможете видеть %s на своем рабочем столе. –азмеры изображений: 1024, 1280 и 1600 на сайте Popcornnews.ru', $d['person']['name'], $d['person']['eng_name']),
			'keywords' => sprintf('обои, рабочий стол, %s, %s', $d['person']['name'], $d['person']['eng_name']),
		),
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
));
?>
<div id="contentWrapper" class="twoCols">
	<script>
		function open_puz(n){
			var cfg = "height=600,width=700,scrollbars=no,toolbar=no,menubar=no,resizable=yes,location=no,status=no";
			var OpenWindow=window.open("/inc/puzl.php?id="+n, "puznewwin", config=cfg);
		}
	</script>
	<div id="content">
		<ul class="menu">
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>">персона</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/news">новости</a></li>
			<?if ($p['query']->get_num('kino_films', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/kino">фильмографи€</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/photo">фото</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans">поклонники</a></li>
			<?if ($p['query']->get_num('puzzles', array('person'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/puzli">пазлы</a></li>
			<?}?>
			<?if ($p['query']->get_num('person_wallpapers', array('id'=>$d['person']['id'], 'name'=>$d['person']['name'])) > 0 || !empty($d['cuser'])) {?>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/oboi">обои</a></li>
			<?}?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics">фанфики</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts">факты</a></li>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/talks">обсуждени€</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<h2>ќбои <?=$d['person']['genitive']?></h2>
		<ul class="imagesList wpList">
			<?foreach ($p['query']->get('person_wallpapers', array('person'=>$d['person']['id'], 'name'=>$d['person']['name']), array('id'), null, null) as $i => $wall) {
				$img = ($wall['img1024'] ? $wall['img1024'] : ($wall['img1280'] ? $wall['img1280'] : $wall['img1600']));
				$f1 = ($wall['site'] == 'pop' ? '/upload/_80_80_90_' . $wall['img1024'] : '/kinoupload/_80_80_80_' . $wall['img1024']);
				$f2 = ($wall['site'] == 'pop' ? '/upload/_80_80_90_' . $wall['img1280'] : '/kinoupload/_80_80_80_' . $wall['img1280']);
				$f3 = ($wall['site'] == 'pop' ? '/upload/_80_80_90_' . $wall['img1600'] : '/kinoupload/_80_80_80_' . $wall['img1600']);
				$f = ($wall['site'] == 'pop' ? '/upload/_80_80_90_' . $img : '/kinoupload/_80_80_80_' . $img);
				if (file_exists($f1)) {
					$f = $f1;
				}
				if (file_exists($f2)) {
					$f = $f2;
				}
				if (file_exists($f3)) {
					$f = $f3;
				}
				?>
			<li><a class="img"><img src="<?=$this->getStaticPath($f)?>" /></a>
				<?if (!empty($wall['img1024'])) {?><a target="_blank" href="/wallpapers/<?=$wall['site']?>/<?=$wall['id']?>/1024">1024</a><br><?}?>
				<?if (!empty($wall['img1280'])) {?><a target="_blank" href="/wallpapers/<?=$wall['site']?>/<?=$wall['id']?>/1280">1280</a><br><?}?>
				<?if (!empty($wall['img1600'])) {?><a target="_blank" href="/wallpapers/<?=$wall['site']?>/<?=$wall['id']?>/1600">1600</a><br><?}?>
			</li>
			<? if (($i + 1) % 5 == 0 && $i != 0) { ?><li class="divider"></li> <?}?>
			<?}?>
		</ul>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>