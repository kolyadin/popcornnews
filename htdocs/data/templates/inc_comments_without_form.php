<?
$new = $d['new'];
if (!isset($d['goto'])) $d['goto'] = '';
?>
<div class="trackContainer commentsTrack"><a name="comments"></a>
	<?
	$limit = COMMENTS_PER_PAGE;
	$offset = ($d['page'] - 1) * $limit;

	$num_comments = $p['query']->get_num('comments', array('new_id'=>$new['id']));
	$comments = $p['query']->get('comments_users', array('new_id'=>$new['id']), array('id'), $offset, $limit);

	if ($num_comments > $limit) {?>
	<div class="paginator smaller firstPaginator">
		<p class="pages">Страницы:</p>
		<ul>
			<?foreach ($p['pager']->make($d['page'], ceil($num_comments / $limit), 25) as $i => $pi) { ?>
			<li>
				<?if (!isset($pi['current'])) { $pi['link'] = ($i==0)? '' : '/page/'.$pi['link'];?>
				<a href="/<?=($d['goto'] ? $d['goto'] : 'news')?>/<?=$new['id']?><?=$pi['link']?>"><?=$pi['text']?></a>
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
			<a rel="nofollow" href="/profile/<?=$comment['uid']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($comment['uavatara']))?>" alt="" /></a>
			<?if (!empty($d['cuser']) && !$comment['del']) {?>
			<div class="mark">
				<span class="up"><span><?=$comment['rating_up']?></span></span>
				<span class="down"><span>-<?=$comment['rating_down']?></span></span>
			</div>
			<?}?>
			<div class="details">
				<?if (!empty($comment['uid'])) {?>
				<a class="pc-user" rel="nofollow" href="/profile/<?=$comment['user_id']?>"><?=$comment['unick']?></a>
				<noindex><span class="date"><?=$p['date']->dmyhi($comment['ctime'], '%d %F %Y, %H:%i')?></span></noindex>
				<?$rating = $p['rating']->_class($comment['urating']);?>
				<div class="userRating <?=$rating['class']?>">
					<div class="rating <?=$rating['stars']?>"></div>
					<span><?=$comment['urating']?></span>
				</div>
				<?} else {?>
				<span class="pc-user"><?=$comment['name']?></span>
				<noindex><span class="date"><?=$p['date']->dmyhi($comment['ctime'], '%d %F %Y, %H:%i')?></span></noindex>
				<?}?>
				
				<nobr>
				<?if (!empty($d['cuser']) && !$comment['del']) {?>
				<span class="complain">! пожаловаться</span>
				<?}?>
				</nobr>
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
			<?if (!isset($pi['current'])) { $pi['link'] = ($i==0)? '' : '/page/'.$pi['link'];?>
			<a href="/<?=($d['goto'] ? $d['goto'] : 'news')?>/<?=$new['id']?><?=$pi['link']?>"><?=$pi['text']?></a>
			<?} else {?>
			<?=$pi['text']?>
			<?}?>
		</li>
		<?}?>
	</ul>
</div>
<?}?>