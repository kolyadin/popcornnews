<?

$new = $d['fanfics_data'];

if (!isset($d['goto'])) $d['goto'] = '';
?>
<div class="trackContainer commentsTrack"><a name="comments"></a>
	<?
	$limit = COMMENTS_PER_PAGE;

	$offset = ($d['page'] - 1) * $limit;
	
	$num_comments = $p['query']->get_num('fanfics_comments', array('fid' => $new['id']));
	$comments = $p['query']->get('fanfics_comments', array('fid' => $new['id']), array('cdate asc'), $offset, COMMENTS_PER_PAGE);
	
	//print_r($this->plugins);
	//print_r(array_keys($comments[0]));
	//echo $offset;
	
	if ($num_comments > $limit) {?>
	<div class="paginator smaller firstPaginator">
		<p class="pages">Страницы:</p>
		<ul>
			<?foreach ($p['pager']->make($d['page'], ceil($num_comments / $limit), 25) as $i => $pi) { ?>
			<li>
				<?if (!isset($pi['current'])) {?>
				<a href="/<?=($d['goto'] ? $d['goto'] : 'news')?>/<?=$new['id']?>/<?=$pi['link']?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
			</li>
			<?}?>
		</ul>
	</div>
	<?}?>
	
	<?foreach ($comments as $i => $comment) {?>
	<div class="trackItem" id="<?=$comment['id']?>">
		<a name="cid_<?=$comment['id']?>"></a>
		<div class="post">
			<div class="entry">
				<p><?php 
				$text = (!$comment['del'] ? $this->preg_repl($p['nc']->get($comment['content'])) : COMMENTS_DELETE_PHRASE);
				$text = preg_replace('/(\<strong\>Ответ на сообщение .*\<\/strong\>)/is', '<noindex>$1</noindex>', $text);
				print $text;
				?></p>
			</div>
			<?
			if (empty($comment['uid'])) {
				$user = null;
			}
			?>
			<a rel="nofollow" href="/profile/<?=$comment['uid']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($comment['user_avatara']))?>" /></a>
			<?if (!empty($d['cuser']) && !$comment['del']) {?>
			<div class="mark">
				<span class="up"><span><?=$comment['rating_up']?></span></span>
				<span class="down"><span>-<?=$comment['rating_down']?></span></span>
			</div>
			<?}?>
			<div class="details">
				<?if (!empty($comment['uid'])) {?>
				<a class="pc-user" rel="nofollow" href="/profile/<?=$comment['uid']?>"><?=htmlspecialchars($comment['nick'], ENT_IGNORE, 'cp1251', false);?></a>
				<noindex><span class="date"><?=$p['date']->unixtime($comment['cdate'], '%d %F %Y, %H:%i')?></span></noindex>
				<nobr>
				<?if (!$comment['del']) {?>
				<?if ($d['cuser']['id'] == $comment['uid']) {?>
				<span class="edit">редактировать</span>
				<?} if ($d['cuser']['id'] == $comment['uid'] || $this->isModer()) {?>
				<span class="delete">удалить</span>
				<?} if (!empty($d['cuser'])) {?>
				<span class="reply" onkeydown="return '<?=$p['nc']->replyText($comment['content'])?>';">ответить</span>
				<span class="complain">! пожаловаться</span>
				<?}
				}
				$rating = $p['rating']->_class($comment['user_rating']);
				?>
				</nobr>
				<div class="userRating <?=$rating['class']?>">
					<div class="rating <?=$rating['stars']?>"></div>
					<span><?=$comment['user_rating']?></span>
				</div>
				<?} else {?>
				<span class="pc-user"><?=htmlspecialchars($comment['name'], ENT_IGNORE, 'cp1251', false);?></span>
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

<?if ($num_comments > $limit) {?>
<div class="paginator smaller noUpperBorder">
	<p class="pages">Страницы:</p>
	<ul>
	<?foreach ($p['pager']->make($d['page'], ceil($num_comments / $limit), 25) as $i => $pi) { ?>
		<li>
		<?if (!isset($pi['current'])) {?>
			<a href="/<?=($d['goto'] ? $d['goto'] : 'news')?>/<?=$new['id']?>/<?=$pi['link']?>"><?=$pi['text']?></a>
			<?} else {?>
			<?=$pi['text']?>
			<?}?>
		</li>
		<?}?>
	</ul>
</div>
<?}?>
<?if (!empty($d['cuser'])) {
	$tmp_time = strtotime(date('Y-m-d', TIME) . date('H') . ':' . date('i') . ':00');
	//echo $tmp_time;
	//$true_comments = ($new['cdate'] == '' || (86400 * 365) > ($tmp_time - mktime(0, 0, 0, substr($new['cdate'], 4, 2), substr($new['cdate'], 6, 2), substr($new['cdate'], 0, 4)))) ? true : false;
	$true_comments = true;
	if ($true_comments) {
?>
<div class="trackContainer commentsTrack">
	<form class="newComment checkCommentsForm" name="fmr" action="/" method="POST">
		<input type="hidden" name="type" value="fanfic" />
		<input type="hidden" name="action" value="comment_add" />
		<input type="hidden" name="re" value="" />
		<input type="hidden" name="fid" value="<?=$d['fanfics_data']['id']?>" />
		<input type="hidden" name="pid" value="<?=$d['person']['id']?>" />
		<input type="hidden" name="page" value="<?=(($d['fanfics_data']['num_comments'] % COMMENTS_PER_PAGE) == 0 ? ceil($d['fanfics_data']['num_comments'] / COMMENTS_PER_PAGE) + 1 : ceil($d['fanfics_data']['num_comments'] / COMMENTS_PER_PAGE))?>" />
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
			<!-- label>
				<input type="checkbox" name="subscribe" value="1"<?=($d['subscribed'] ? ' checked="checked"' : null)?> />
				Присылать мне уведомления о новых комментариях
			</label -->
		</div>
	</form>
</div>
<?
	}
} else {?>
		Если Вы хотите оставить комментарий - <a href="/register">зарегистрируйтесь.</a>
<?}?>