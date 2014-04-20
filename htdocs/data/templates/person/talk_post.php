<?
$this->_render('inc_header', array('title'=>$d['person']['name'], 
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
			<li class="active"><a href="<?=$handler->getBaseLink();?>/talks">обсуждения</a></li>
			<?if ($p['query']->get_num('video', array('pole1'=>$d['person']['id'], 'pole11'=>'1')) > 0) {?>
			<li><a href="<?=$handler->getBaseLink();?>/video">видео</a></li>
			<?}?>
			<?if ($p['query']->get_num('yourstyle_sets_tags', array('tid'=>$d['person']['id'])) > 0) {?>
			<li><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/sets">сеты</a></li>
			<? } ?>
		</ul>
		<ul class="menu bLevel">
			<li><a href="<?=$handler->getBaseLink();?>/talks">все темы</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/talks/messages">все комментарии</a></li>
			<li class="active"><a href="<?=$handler->getBaseLink();?>/talks/post">создать тему</a></li>
		</ul>
		<h2><?=(!empty($d['edit_topic']) ? 'Редактирование обсуждения ' : 'Обсуждение ') . $d['person']['genitive']?></h2>
		<?if ($d['cuser']['rating'] < 100) {?>
		<div class="systemMessage">
			<p>Для создания обсуждений необходимо иметь рейтинг больше 100.</p>
		</div>
		<?} elseif($d['person']['id'] == 1) { ?>
		<p>
			Извините, создание новых обсуждений временно приостановлено из-за нарушений правил.
			<br><a href="javascript:window.history.back();">Назад</a>
		</p>
		<?} elseif (!$d['is_fan']) { ?>
		<div class="systemMessage">
			<p>К сожалению, вы не можете создавать новую тему, так как не являетесь поклонником данной звезды.</p>
		</div>
		<?} else {?>
		<div class="trackContainer mailTrack">
			<div class="trackItem answering">
				<script type="text/javascript">
					function check_fr(frm)
					{
						frm.submit.disabled=true;
						str='';
						if (frm.name.value=='') {
							str='Вы не указали тему для обсуждения!';
						}
						if (str!='') {
							alert (str);
							frm.submit.disabled=false;
							return false;
						}
						return true;
					}
					function check_length(message){
						var maxLen = 500;
						if (message.value.length > maxLen){
							message.value = message.value.substring(0, maxLen);
						}
					}
				</script>

				<form action="/" name="frm" method="POST" class="answer newMessage" onSubmit="return check_fr(this);">
					<input type="hidden" name="type" value="topic">
					<?if (!empty($d['edit_topic'])) {?>
					<input type="hidden" name="action" value="edit">
					<input type="hidden" name="topic_id" value="<?=(!empty($d['topic_id']) ? $d['topic_id'] : null)?>">
					<?} else {?>
					<input type="hidden" name="action" value="post">
					<?}?>
					<input type="hidden" name="person" value="<?=(!empty($d['person']['id']) ? $d['person']['id'] : null)?>">
					<input type="text" name="name" value="<?=(!empty($d['edit_topic']['name']) ? $d['edit_topic']['name'] : null)?>">
					<textarea name="content" onkeyup="check_length(this);" title="Максимум 500 символов"><?=(!empty($d['edit_topic']['content']) ? $d['edit_topic']['content'] : null)?></textarea>
					Код видео:<textarea name="embed" style="height:40px"><?=(!empty($d['edit_topic']['embed']) ? $d['edit_topic']['embed'] : null)?></textarea>
					<div class="aboutMe">
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
						<span>Вы пишете как</span><br />
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					</div>
					<input type="submit" value="отправить" />
				</form>
			</div>
		</div>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
