<?
$user = array_shift($p['query']->get('users', array('id'=>$d['topic']['uid']), null, 0, 1));
$this->_render('inc_header',
	array(
		'title' => $d['theme']['name'],
		'header' => $d['theme']['name'],
		'top_code' => '<img src="/i/chat_ico.png">',
		'header_small' => 'Общаемся на свободные темы',
		'js' => 'Comments.js?d=13.05.11',
	)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/">все обсуждения</a></li>
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/messages">все комментарии</a></li>
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/post">создать тему</a></li>
		</ul>
		<div class="topic">
			<div class="topicHeadline">
				<a class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
				<div class="details">
					<h2><?=$d['topic']['name']?></h2>
					<a class="pc-user" rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					<?$rating = $p['rating']->_class($user['rating']);?>
					<div class="userRating <?=$rating['class']?>" title="<?=$user['rating']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$user['rating']?></span>
					</div>
					<span class="date"><?=$p['date']->unixtime($d['topic']['cdate'], '%d %F %Y, %H:%i')?></span>
				</div>
			</div>
			<div class="entry">
				<p><?=nl2br($this->preg_repl($d['topic']['content']));?></p>
				<?if ($d['topic']['embed'] != '') echo $d['topic']['embed'];?>
			</div>
			<div class="markTopic">
				<span class="rating" id="t_<?=$d['topic']['id']?>"><?=$d['topic']['rating']?></span>
				<span>Оценить пост: </span>
				<a class="up" href="#" onclick="chat_topic_vote(<?=$d['topic']['id']?>,2); return false;">хороший пост</a>
				<a class="down" href="#" onclick="chat_topic_vote(<?=$d['topic']['id']?>,1); return false;">плохой пост</a>
				
				<div class="actions">
					<?if ($user['id'] == $d['cuser']['id'] || $this->isModer()) {?>
					<a class="delete" href="/chat/theme/<?=$d['theme']['id']?>/topic/<?=$d['topic']['id']?>/delete" onclick="return confirm('Хотите удалить обсуждение?')">Удалить обсуждение</a>
					<?} if ($user['id'] == $d['cuser']['id']) {?>
					<a class="edit" href="/chat/theme/<?=$d['theme']['id']?>/topic/<?=$d['topic']['id']?>/edit">Редактировать</a>
					<?}?>
				</div>
			</div>
		</div>

		<?if ($d['comments']) {?>
		<div class="irh irhComments">
			<div class="irhContainer">
				<h3>комментарии<span class="replacer"></span></h3>
				<span class="counter"><?=$d['topic']['comments']?></span>
			</div>
		</div>
		<div class="trackContainer commentsTrack">
			<div class="paginator smaller firstPaginator">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/chat/theme/<?=$d['theme']['id']?>/topic/<?=$d['topic']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
			<?foreach ($d['comments'] as $i => $msg) {?>
			<div class="trackItem" id="<?=$msg['id'];?>">
				<a name="cid_<?=$msg['id']?>"></a>
				<div class="post">
					<div class="entry">
						<p><?=(!$msg['del'] ? $this->preg_repl($p['nc']->get($msg['content'])) : COMMENTS_DELETE_PHRASE)?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$msg['uid']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($msg['avatara']))?>" /></a>
					<?if (!empty($d['cuser']) && !$msg['del']) {?>
					<div class="mark">
						<span class="up"><span><?=$msg['rating_up']?></span></span>
						<span class="down"><span>-<?=$msg['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$msg['uid']?>"><?=htmlspecialchars($msg['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						<span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span>
						<?if ($msg['edate'] != 0) { ?>
						<span class="date updateDate">исправлено <?=$p['date']->unixtime($msg['edate'], '%d %F %Y, %H:%i')?></span>
						<?}?>
						<?$rating = $p['rating']->_class($msg['user_rating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$msg['user_rating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$msg['user_rating']?></span>
						</div>

						<nobr>
						<?if (!$msg['del'] && $d['cuser']['id'] == $msg['uid']) { ?>
						<span class="edit">редактировать</span>
						<?}?>
						<? if (!$msg['del'] && ($msg['uid'] == $d['cuser']['id'] || $d['cuser']['id'] == $d['topic']['uid'] || $this->isModer())) {?>
						<span class="delete">удалить</span>
						<? }?>
						<?if (!empty($d['cuser']) && !$msg['del']) {?>
						<span class="reply" onkeydown="return '<?=$p['nc']->replyText($msg['content'])?>';">ответить</span>
						<?}?>
						</nobr>
					</div>
				</div>
			</div>
			<?}?>
			<div class="paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/chat/theme/<?=$d['theme']['id']?>/topic/<?=$d['topic']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		
		<?if (!empty($d['cuser'])) {?>
		<div class="irh irhWriteComment">
			<h3>написать комментарий<span class="replacer"></span></h3>
		</div>
		<div class="trackContainer commentsTrack">
			<form action="/" method="POST" class="newComment" name="fmr">
				<input type="hidden" name="re" value="">
				<input type="hidden" name="type" value="chat_message">
				<input type="hidden" name="action" value="post">
				<input type="hidden" name="page" value="<?=(($d['topic']['comments'] % TALKS_TOPIC_COMMENTS_PER_PAGE) == 0 ? ceil($d['topic']['comments'] / TALKS_TOPIC_COMMENTS_PER_PAGE) + 1 : ceil($d['topic']['comments'] / TALKS_TOPIC_COMMENTS_PER_PAGE))?>">
				<input type="hidden" name="tid" value="<?=$d['topic']['id']?>">
				<input type="hidden" name="theme" value="<?=$d['theme']['id']?>">
				<div class="trackItem">
					<div class="entry">
						<?$this->_render('inc_bbcode');?>
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<div class="aboutMe">
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
						<span>Вы пишете как</span><br />
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					</div>
				</div>
				<div class="formActions">
					<input type="submit" value="отправить" onclick="this.enabled = false;" />
				</div>
			</form>
		</div>
			<?} else {?>
					Если Вы хотите оставить комментарий - <a href="/register">зарегистрируйтесь.</a>
			<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>