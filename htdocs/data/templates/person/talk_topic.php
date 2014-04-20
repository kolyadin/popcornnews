<?
$user = array_shift($p['query']->get('users', array('id'=>$d['topic']['uid']), null, 0, 1));
$page = $d['page'];
//var_dump($_SERVER['REQUEST_URI']);
if(strpos($_SERVER['REQUEST_URI'], $handler->getBaseLink()."/talks/topic/{$d['topic']['id']}/1") !== FALSE && $page == 1) {
    $location = $handler->getBaseLink()."/talks/topic/{$d['topic']['id']}";
    header('HTTP/1.1 301 Moved Permanently');
    header("Location: {$location}");
}
/*$noindex = ($page > 1) ? '<noindex>' : '';
$noindexEnd = ($page > 1) ? '</noindex>' : '';*/
$noindex = '';
$noindexEnd = '';

$canonical_link = ($page > 1) ? 'http://www.popcornnews.ru/'.$handler->getBaseLink().'/talks/topic/'.$d['topic']['id'] : null;
$title = $d['topic']['name'].(($page > 1) ? ' - комментарии страница '.$page : '');
$this->_render('inc_header',
	array(
		'title' => $title,
		'header' => $d['person']['name'] . '<br /><span>'.$d['person']['eng_name'].'</span>',
		'top_code' => ($d['person']['main_photo'] ? '<img src="' . $this->getStaticPath('/upload/_100_100_80_' . $d['person']['main_photo']) . '" alt="' . $d['person']['name'] . '" class="avaProfile">' : null), 
		'header_small'=>'',
		'header_class' => 'topPersonHeader',
		'js' => 'Comments.js?d=20.02.12',
	    'canonical_link' => $canonical_link,
	)
);
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
			<li><a href="<?=$handler->getBaseLink();?>/talks/messages" rel="nofollow">все комментарии</a></li>
			<li><a href="<?=$handler->getBaseLink();?>/talks/post" rel="nofollow">создать тему</a></li>
		</ul>
		<div class="topic">
			<div class="topicHeadline">
				<a class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']));?>" /></a>
				<div class="details">
					<h2><?=$d['topic']['name']?></h2>
					<a class="pc-user" rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					<?$rating = $p['rating']->_class($user['rating']);?>
					<div class="userRating <?=$rating['class']?>" title="<?=$user['rating']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$user['rating']?></span>
					</div><noindex>
					<span class="date"><?=$p['date']->unixtime($d['topic']['cdate'], '%d %F %Y, %H:%i')?></span>
					</noindex>
				</div>
			</div>
			<?=$noindex;?>
			<div class="entry">
				<p><?=$this->preg_repl($d['topic']['content']);?></p>
				<?if ($d['topic']['embed'] != '')echo $d['topic']['embed'];?>
			</div>
			<?=$noindexEnd;?>
			<div class="markTopic">
				<span class="rating" id="t_<?=$d['topic']['id']?>"><?=$d['topic']['rating']?></span>
				<span>Оценить пост: </span>
				<a class="up" href="#" onclick="topic_vote(<?=$d['topic']['id']?>,2); return false;">хороший пост</a>
				<a class="down" href="#" onclick="topic_vote(<?=$d['topic']['id']?>,1); return false;">плохой пост</a>
				
				<div class="actions">
					<?if ($user['id'] == $d['cuser']['id'] || $this->isModer()) {?>
					<a class="delete" href="<?=$handler->getBaseLink();?>/talks/delete/<?=$d['topic']['id']?>" onclick="return confirm('Хотите удалить обсуждение?')">Удалить обсуждение</a>
					<?} if ($user['id'] == $d['cuser']['id']) {?>
					<a class="edit" href="<?=$handler->getBaseLink();?>/talks/topic_edit/<?=$d['topic']['id']?>">Редактировать</a>
					<?}?>
				</div>
			</div>
		</div>

		<?if (!empty($d['comments'])) {?>
		<div class="irh irhComments">
			<div class="irhContainer">
				<h3>комментарии<span class="replacer"></span></h3>
				<span class="counter"><?=$d['comments_num']?></span>
			</div>
		</div>
		<div class="trackContainer commentsTrack">
			<div class="paginator smaller firstPaginator">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) { $pi['link'] = ($i==0)?'':$pi['link'];?>
						<a href="<?=$handler->getBaseLink();?>/talks/topic/<?=$d['topic']['id']?>/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
			<?foreach ($d['comments'] as $i => $msg) {?>
			<div class="trackItem" id="<?=$msg['id']?>">
				<a name="cid_<?=$msg['id']?>"></a>
				<div class="post">
					<div class="entry">
						<p><?php 
				        $text = (!$msg['del'] ? $this->preg_repl($p['nc']->get($msg['content'])) : COMMENTS_DELETE_PHRASE);
				        $text = preg_replace('/(\<strong\>Ответ на сообщение .*\<\/strong\>)/is', '<noindex>$1</noindex>', $text);
				        print $text;
				        ?></p>
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
						<noindex><span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span></noindex>
						<?if ($msg['edate'] != 0) { ?>
						<noindex><span class="date updateDate">исправлено <?=$p['date']->unixtime($msg['edate'], '%d %F %Y, %H:%i')?></span></noindex>
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
			
			<div class="noUpperBorder paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10, true) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) { $pi['link'] = ($i==0)?'':$pi['link'];?>						
						<a href="<?=$handler->getBaseLink();?>/talks/topic/<?=$d['topic']['id']?>/<?=$pi['link']?>"><?=$pi['text']?></a>
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
				<input type="hidden" name="type" value="message">
				<input type="hidden" name="action" value="post">
				<input type="hidden" name="page" value="<?=(($d['comments_num'] % TALKS_TOPIC_COMMENTS_PER_PAGE) == 0 ? ceil($d['comments_num'] / TALKS_TOPIC_COMMENTS_PER_PAGE) + 1 : ceil($d['comments_num'] / TALKS_TOPIC_COMMENTS_PER_PAGE))?>">
				<input type="hidden" name="tid" value="<?=$d['topic']['id']?>">
				<input type="hidden" name="person" value="<?=$d['person']['id']?>">
				<div class="trackItem">
					<div class="entry">
						<?$this->_render('inc_bbcode');?>
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<div class="aboutMe">
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
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