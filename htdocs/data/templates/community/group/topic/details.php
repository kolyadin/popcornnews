<?
$this->_render('inc_header',
	array(
		'title' => 'Обсуждения группы - ' . htmlspecialchars($d['group']['title']),
		'header' => 'Обсуждения группы',
		'top_code' => 'C',
		'header_small' => $d['group']['title'],
		'js' => array('Community.js', 'Comments.js?d=13.05.11'),
	)
);
?>
<script type="text/javascript">
	var community = new Community();
</script>

<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">все темы</a></li>
			<?if ($d['isAMember']) {?>
			<li><a href="/community/group/<?=$d['group']['id']?>/topic/add">создать тему</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topic/addPoll">создать опрос</a></li>
			<?}?>
		</ul>
		
		<div class="topic">
			<div class="topicHeadline">
				<a class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['topic']['uavatara']));?>" /></a>
				<div class="details">
					<h2><?=$d['topic']['title']?></h2>
					<a class="pc-user" rel="nofollow" href="/profile/<?=$d['topic']['uid']?>"><?=$d['topic']['unick']?></a>
					<?$rating = $p['rating']->_class($d['topic']['urating']);?>
					<div class="userRating <?=$rating['class']?>" title="<?=$d['topic']['urating']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$d['topic']['urating']?></span>
					</div>
					<span class="date"><?=$p['date']->unixtime($d['topic']['createtime'], '%d %F %Y, %H:%i')?></span>
				</div>
			</div>
			<div class="entry">
				<p><?=nl2br($this->preg_repl($d['topic']['description']));?></p>
			</div>
			<div class="poll">
				<h4>опрос</h4>
				<div id="options">
					<?if ($d['isAMember'] && !$d['userVote']) {?>
					<form class="poll" name="poll" onsubmit="return community.pollSubmit(this);">
						<ul class="poll">
							<? foreach ($d['pollOptions'] as $option) { ?>
								<li><label><input type="radio" name="option" value="<?= $option['id'] ?>" /><?= $option['title'] ?></label></li>
							<? } ?>
						</ul>
						<input type="submit" class="submit" />
					</form>
					<?} else {?>
					<ul class="poll">
						<? foreach ($d['pollOptions'] as $option) { ?>
						<li>
							<span class="name"><?=$option['title']?></span><span class="count"><?=$option['rating']?></span>
							<span class="percent"><span style="width: <?=$option['percent']?>%"></span></span>
						</li>
						<? } ?>
					</ul>
					<?}?>
				</div>
			</div>
			<div class="markTopic">
				<span class="rating" id="t_<?=$d['topic']['id']?>"><?=$d['topic']['rating']?></span>
				<span>Оценить пост: </span>
				<a class="up" href="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/rating/1" onclick="community.topicVote(event);">хороший пост</a>
				<a class="down" href="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/rating/-1" onclick="community.topicVote(event);">плохой пост</a>
				<b>
					<?if ($d['canModifyGroup']) {?>
					<a class="actions" href="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/delete" onclick="return confirm('Вы дейстиветельно хотите удалить это оюсуждение?');">Удалить обсуждение</a>
					<?if (!$d['topic']['poll']) {?><a class="actions" href="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/edit">Редактировать</a><?}?>
					<?}?>
				</b>
			</div>
		</div>

		<div class="irh irhComments">
			<div class="irhContainer">
				<h3>комментарии<span class="replacer"></span></h3>
				<span class="counter"><?=$d['messagesNum']?></span>
			</div>
		</div>
		
		<?if ($d['messagesNum'] > 0) {?>
		<div class="trackContainer commentsTrack"><a name="comments"></a>
			<div class="paginator smaller firstPaginator">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) {?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
			
			<?foreach ($d['messages'] as $i => $msg) {?>
			<div class="trackItem" id="<?=$msg['id'];?>">
				<a name="mid_<?=$msg['id']?>"></a>
				<div class="post">
					<div class="entry">
						<p><?=(!$msg['deletetime'] ? $this->preg_repl($p['nc']->get($msg['message'])) : COMMENTS_DELETE_PHRASE)?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$msg['uid']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($msg['uavatara']))?>" /></a>
					<?if (!empty($d['cuser']) && !$msg['deletetime'] && $d['isAMember']) {?>
					<div class="mark">
						<span class="up"><span><?=$msg['rating_up']?></span></span>
						<span class="down"><span>-<?=$msg['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$msg['uid']?>"><?=$msg['unick']?></a>
						<span class="date"><?=$p['date']->unixtime($msg['createtime'], '%d %F %Y, %H:%i')?></span>
						<?if ($msg['edittime'] != 0) { ?>
						<span class="date updateDate">исправлено <?=$p['date']->unixtime($msg['edittime'], '%d %F %Y, %H:%i')?></span>
						<?}?>
						<?$rating = $p['rating']->_class($msg['urating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$msg['urating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$msg['urating']?></span>
						</div>
						
						<nobr>
						<?if (!$msg['deletetime'] && $d['cuser']['id'] == $msg['uid']) { ?>
						<span class="edit">редактировать</span>
						<?}?>
						<?if (!$msg['deletetime'] && ($d['canModifyGroup'] || ($msg['uid'] == $d['cuser']['id']))) {?>
						<span class="delete">удалить</span>
						<?}?>
						<?if ($d['isAMember'] && (!empty($d['cuser']) && !$msg['deletetime'])) {?>
						<span class="reply" onkeydown="return '<?=$p['nc']->replyText($msg['message'])?>';">ответить</span>
						<?}?>
						</nobr>
					</div>
				</div>
			</div>
			<?}?>
			
			<div class="noUpperBorder paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		
		<?if ($d['isAMember']) {?>
		<div class="irh irhWriteComment">
			<h3>написать комментарий<span class="replacer"></span></h3>
		</div>
		<div class="trackContainer commentsTrack">
			<form action="/community/group/<?=$d['group']['id']?>/topic/<?=$d['topic']['id']?>/postMessage" method="POST" class="newComment checkCommentsForm" name="fmr">
				<input type="hidden" name="re" value="">
				<input type="hidden" name="type" value="community">

				<input type="hidden" name="page" value="<?=(($d['messagesNum'] % $d['perPage']) == 0 ? ceil($d['messagesNum'] / $d['perPage']) + 1 : ceil($d['messagesNum'] / $d['perPage']))?>">
				
				<a name="write"></a>
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
			Если Вы хотите оставить комментарий - <a href="/community/group/<?=$d['group']['id']?>">вступите</a> в группу.
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>