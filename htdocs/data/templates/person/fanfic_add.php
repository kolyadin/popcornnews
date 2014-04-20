<?
$this->_render('inc_header', array('title'=>'Фанфик - прислать - ' . $d['person']['name'], 
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
		<?php if($d['fcount'] > 0) { ?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/all">все фанфики</a></li>
		<?php } ?>
			<li class="active"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics/add">прислать</a></li>
		</ul>
		<h2>Прислать фанфик</h2>
		<div class="trackContainer fanficsTrack">
			<?if (!empty($d['cuser'])) {?>
			<div class="trackContainer mailTrack">
				<div class="trackItem answering">
					<script type="text/javascript">
						function check_fr(frm)
						{
							frm.button.disabled=true;
							str='';
							if (frm.name.value=='')
							{
								str='Вы не указали тему для фанфика!';
							}
							if (frm.content.value.length < 4000) {
								str = 'В графе «текст» размещается весь остальной рассказ, не менее 4000 знаков';
							}
							if (str!='')
							{
								alert (str);
								frm.button.disabled=false;
								return false;
							}							
							return true;
						}
					</script>
					<p class="rules">
						Правила размещения фанфика:<br />
						1.Укажите название вашего рассказа<br />
						2.В графе «анонс» пишутся первые несколько предложений из всего фанфика.<br />
						3.В графе «текст» размещается весь остальной рассказ, не менее 4000 знаков.<br />
						4.Загружаемое изображение должно соответствовать теме вашего фанфика<br />
						5.Двойной «Enter» означает начало нового абзаца в вашем тексте. На каждый абзац можно поставить закладку.<br />
						6.Закладка нужна для того, чтобы в любой момент вернутся к тому месту, на котором вы остановились.<br />
						7.При размещении чужого творчества, указывайте имя автора и источник. Уважайте авторские права.<br />
						<br />
						Администрация сайта не несет ответственности за размещаемые пользователями материалы.<br />
					</p>
					<form name="fmr" method="POST" class="answer newMessage" onSubmit="return check_fr(this);" enctype="multipart/form-data">
						<input type="hidden" name="type" value="fanfic" />
						<input type="hidden" name="action" value="<?=($d['fanfics_data'] ? 'edit' : 'add')?>" />
						<input type="hidden" name="pid" value="<?=(!isset($_POST['pid']) ? $handler->getID() : $_POST['pid']);?>" />
						<?if ($d['fanfics_data']['id']) {?><input type="hidden" name="id" value="<?=$d['fanfics_data']['id'];?>"><?}?>
						<p>Название:</p><input type="text" name="name" value="<?=htmlspecialchars((isset($_POST['name']) ? $_POST['name'] : $d['fanfics_data']['name']));?>">
						<p>Анонс:</p><textarea class="announce" title="Максимум 400 символов" name="announce"><?=(isset($_POST['announce']) ? $_POST['announce'] : $d['fanfics_data']['announce']);?></textarea>
						<p>Текст:</p>
							<?$this->_render('inc_bbcode');?>
						<textarea class="content" name="content"><?=(isset($_POST['content']) ? $_POST['content'] : $d['fanfics_data']['content']);?></textarea>
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						</div>
						<label>Картинка <input type="file" name="attachment" /></label>
						<input type="submit" name="button" value="отправить" />
					</form>
				</div>
			</div>
			<?} else {?>
			<p>Если Вы хотите оставить фанфик - <a href="/register">зарегистрируйтесь</a>.</p>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>