<?
$img = (empty($user['avatara']) ? '/img/no_photo_small.jpg' : '/avatars_small/' . $user['avatara']);
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
			<li class="active"><a href="<?=$handler->getBaseLink();?>/talks/messages">все комментарии</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/talks/post">создать тему</a></li>
		</ul>
		<h2>Комментарии со всех тем <?=$d['person']['genitive']?></h2>
		<div class="trackContainer commentsTrack difThemes">
			<?
			$limit = 50;
			$offset = ($d['page'] - 1) * $limit;
			$num_records = $p['query']->get_num('all_messages', array('person'=>$d['person']['id']));
			$pages = ceil($num_records / $limit);
			foreach ($p['query']->get('all_messages', array('person'=>$d['person']['id']), array('cdate desc'), $offset, $limit) as $i => $msg) {
			?>
			<div class="trackItem" id="<?=$msg['id']?>">
				<a name="cid_<?=$msg['id']?>"></a>
				<span class="rating"><?=$msg['rating']?></span>
				<div class="post">
					<div class="entry">
						<p><?php 
				        $text = (!$msg['del'] ? $this->preg_repl($p['nc']->get($msg['content'])) : COMMENTS_DELETE_PHRASE);
				        $text = preg_replace('/(\<strong\>Ответ на сообщение .*\<\/strong\>)/is', '<noindex>$1</noindex>', $text);
				        print $text;
				        ?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$msg['author_id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($msg['author_avatara']))?>" /></a>
					<div class="details">
						<span class="toTheme">К теме <a href="<?=$handler->getBaseLink();?>/talks/topic/<?=$msg['tid']?>">«<?=$msg['topic_name']?>»</a></span>
							<?$rating = $p['rating']->_class($msg['author_rating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$msg['author_rating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$msg['author_rating']?></span>
						</div>
						<a class="pc-user" rel="nofollow" href="/profile/<?=$msg['author_id']?>"><?=htmlspecialchars($msg['author_nick'], ENT_IGNORE, 'cp1251', false);?></a>
						<noindex><span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span></noindex>
						
						<? if (!$msg['del'] && ($msg['uid'] == $d['cuser']['id'] || $d['cuser']['id'] == $msg['topic_uid'] || $this->isModer())) {?>
						<a href="#" onclick="delete_msg(<?=$msg['id'];?>, 'topic'); return false;" class="reply">удалить</a>
						<? }?>
					</div>
				</div>
			</div>
			<?}?>
		</div>
		<div class="paginator">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $pages, 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="<?=$handler->getBaseLink();?>/talks/messages/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?} else {?>
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