<?
$this->_render('inc_header',
	array(
	    'title'=>$d['theme']['name'],
	    'header'=>$d['theme']['name'],
	    'top_code'=>'<img src="/i/chat_ico.png">',
	    'header_small'=>'Общаемся на свободные темы'
	)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/">все обсуждения</a></li>
			<li class="active"><a href="/chat/theme/<?=$d['theme']['id']?>/messages">все комментарии</a></li>
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/post">создать тему</a></li>
		</ul>
		<h2>Комментарии со всех тем</h2>
		<div class="trackContainer commentsTrack difThemes">
			<?foreach ($d['comments'] as $i => $msg) {?>
			<div class="trackItem" id="<?=$msg['id']?>">
				<a name="cid_<?=$msg['id']?>"></a>
				<span class="rating"><?=$msg['rating']?></span>
				<div class="post">
					<div class="entry">
						<p><?=(!$msg['del'] ? $this->preg_repl($p['nc']->get($msg['content'])) : COMMENTS_DELETE_PHRASE)?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$mgs['author_id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($msg['author_avatara']))?>" /></a>
					<div class="details">
						<span class="toTheme">К теме <a href="/chat/theme/<?=$d['theme']['id']?>/topic/<?=$msg['tid']?>">«<?=$msg['topic_name']?>»</a></span>
							<?$rating = $p['rating']->_class($msg['author_rating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$msg['author_rating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$msg['author_rating']?></span>
						</div>
						<a class="pc-user" rel="nofollow" href="/profile/<?=$msg['author_id']?>"><?=$msg['author_nick']?></a>
						<span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span>
						
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
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/chat/theme/<?=$d['theme']['id']?>/messages/page/<?=$pi['link']?>"><?=$pi['text']?></a>
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