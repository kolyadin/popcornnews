<?

/*ini_set('display_errors', 1);
error_reporting(E_ALL);*/

$comments = $d['comments'];

if (!isset($d['goto'])) $d['goto'] = '';
?>
<div class="trackContainer commentsTrack"><a name="comments"></a>
	
	<?foreach ($comments as $i => $comment) {?>
	<div class="trackItem" id="<?=$comment['id']?>">
		<a name="cid_<?=$comment['id']?>"></a>
		<div class="post">
			<div class="entry">
				<p><?php 
				$text = (!$comment['del'] ? $this->preg_repl($p['nc']->get($comment['comment'])) : COMMENTS_DELETE_PHRASE);
				$text = preg_replace('/(\<strong\>Ответ на сообщение .*\<\/strong\>)/is', '<noindex>$1</noindex>', $text);
				print $text;
				?></p>
			</div>
			<?
			if (empty($comment['uid'])) {
				$user = null;
			}
			?>
			<a rel="nofollow" href="/profile/<?=$comment['uid']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($comment['uavatara']))?>" /></a>
			<?if (!empty($d['cuser'])) {?>
			<div class="mark">
				<span class="up"><span><?=$comment['rating_up']?></span></span>
				<span class="down"><span>-<?=$comment['rating_down']?></span></span>
			</div>
			<?}?>
			<div class="details">
				<?if (!empty($comment['uid'])) {?>
				<a class="pc-user" rel="nofollow" href="/profile/<?=$comment['uid']?>"><?=$comment['unick']?></a>
				<noindex><span class="date"><?=$p['date']->unixtime($comment['createtime'], '%d %F %Y, %H:%i')?></span></noindex>
				<nobr>
				<?if ($d['cuser']['id'] == $comment['uid']) {/*?>
				<span class="edit">редактировать</span>
				<?*/} if ($d['cuser']['id'] == $comment['uid'] || $this->isModer()) {?>
				<span class="delete">удалить</span>
				<?} if (!empty($d['cuser'])) {?>
				<span class="reply" onkeydown="return '<?=$p['nc']->replyText($comment['comment'])?>';">ответить</span>
				<span class="complain">! пожаловаться</span>
				<?}			
				$rating = $p['rating']->_class($comment['urating']);
				?>
				</nobr>
				<div class="userRating <?=$rating['class']?>">
					<div class="rating <?=$rating['stars']?>"></div>
					<span><?=$comment['urating']?></span>
				</div>
				<?} else {?>
				<span class="pc-user"><?=$comment['name']?></span>
				<noindex><span class="date"><?=$p['date']->unixtime($comment['cdate'], '%d %F %Y, %H:%i')?></span></noindex>
				
				<nobr>
				<?if (!$comment['del'] && ($d['cuser']['id'] == $comment['uid'] || $this->isModer())) {?>
				<span class="delete">удалить</a>
				<?}?>
				<span class="complain"><nobr>! пожаловаться</nobr></span>
				</nobr>
				<?}?>
			</div>
		</div>
	</div>
	<?}?>
</div>

<?if (!empty($d['cuser'])) {
	if ($d['isAMember']) {
?>
<div class="trackContainer commentsTrack">
	<form class="newComment checkCommentsForm" name="fmr" action="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/postComment" method="POST">
		<input type="hidden" name="type" value="community">
		<input type="hidden" name="re" value="" />
		<a name="write"></a>
		<div class="trackItem">
			<div class="entry">
				<?$this->_render('inc_bbcode');?>
				<?$this->_render('inc_smiles');?>
				<textarea name="content"></textarea>
			</div>
			<fieldset class="loggedOut twoCols">
			<div class="aboutMe">
				<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
				<span>Вы пишете как</span><br />
				<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
			</div>
			</fieldset>
		</div>
		<div class="formActions">
			<input type="submit" name="submit" value="отправить" onclick="this.enabled = false;" />
		</div>
	</form>
</div>
<?
	} else {?>
	  Если Вы хотите оставить комментарий - <a href="/community/group/<?=$d['group']['id']?>">вступите</a> в группу.  
	<?}
} else {?>
		Если Вы хотите оставить комментарий - <a href="/register">зарегистрируйтесь.</a>
<?}?>