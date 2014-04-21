<?
$this->_render('inc_header',
	array(
		'title' => $d['user']['nick'],
		'header' => htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false),
		'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['user']['avatara'], true)) . '" alt="' . htmlspecialchars($d['user']['nick']) . '" class="avaProfile">',
		'header_small' => 'Фотографии пользователя',
		'js' => 'Comments.js?d=13.05.11',
	)
);
$blackList = BlackListFactory::getBlackListForUser($d['user']['id']);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<?$rating = $p['rating']->_class($d['user']['rating']);?>
			<li class="rating">
				<div class="userRating <?=$rating['class']?>">
					<div class="rating <?=$rating['stars']?>"></div>
					<span><?=$d['user']['rating']?> <?=$rating['name']?> <?=$d['online'] ? 'Онлайн' :''?></span>
				</div>
			</li>
			<li><a rel="nofollow" href="/profile/<?=$d['user']['id']?>">профиль</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/friends">друзья</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/persons">персоны</a></li>
			<?if ($p['query']->get_num('profile_pix', array('uid'=>$d['user']['id'])) > 0) {?><li class="active"><a href="/user/<?=$d['user']['id']?>/photos">фотографии</a></li><?}?>
			<li><a href="/user/<?=$d['user']['id']?>/guestbook">гостевая</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/gifts">подарки</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/wrote">пишет</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/community/groups">группы</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/sets">your.style</a></li>
		</ul>
		<h2>Фотографии <?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?></h2>
		<?
		$img_count = $p['query']->get_num('profile_pix', array('uid' => $d['user']['id']));
		if ($img_count > 0) {
		?>
		<div class="gallery undecorated">
			<div class="largeContainer">
				<div class="scrollLeft imageLeftScroller"></div>
				<div class="scrollRight imageRightScroller"></div>
				<div class="imgContainer"></div>
			</div>
			<div class="previewsWrapper">
				<div class="scrollLeft listLeftScroller"></div>
				<div class="previewsContainer">
					<ul></ul>
				</div>
				<div class="scrollRight listRightScroller"></div>
			</div>
		</div>
		<?$comments = $p['query']->get('profile_pix_comments_vivod', array('gid'=>$d['user']['id']), array('id'), null, null);?>
		<div class="irh irhComments">
			<div class="irhContainer">
				<h3>комментарии<span class="replacer"/></h3>
				<span class="counter"><?=count($comments)?></span>
			</div>
		</div>
		<div class="trackContainer commentsTrack">
			<?foreach ($comments as $i => $comment) {?>
			<div class="trackItem" id="<?=$comment['id'];?>">
				<div class="post">
					<div class="entry">
						<p><?=$this->preg_repl($p['nc']->get($comment['content']));?></p>
					</div>
					
					<a rel="nofollow" href="/profile/<?=$comment['uid']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($comment['uavatara']))?>" /></a>
					<?if (!empty($d['cuser'])) {?>
					<div class="mark">
						<span class="up"><span><?=$comment['rating_up']?></span></span>
						<span class="down"><span>-<?=$comment['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$comment['uid']?>"><?=$comment['unick']?></a>
						<span class="date"><?=$p['date']->unixtime($comment['ctime'], '%d %F %Y, %H:%i')?></span>
						<?$rating = $p['rating']->_class($comment['urating']);?>
						<div class="userRating <?=$rating['class']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$comment['urating']?></span>
						</div>
						<span class="reply" onkeydown="return '<?=$p['nc']->replyText($comment['content'])?>';">ответить</span>
						<?if ($d['cuser']['id'] == $comment['uid']) { ?>
						<span class="delete">удалить</span>
						<?}?>
					</div>
				</div>
			</div>
		<?}?>
		</div>
		
		<div class="trackContainer commentsTrack">
            <?php if (!$blackList->isUserExists($d['cuser']['id'])) { ?>
			<form class="newComment checkCommentsForm" name="fmr" action="/" method="POST">
				<input type="hidden" name="type" value="photos_comment">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="re" value="">
				<input type="hidden" name="new_id" value="<?=$d['user']['id']?>">
				<a name="write"></a>
				<div class="trackItem">
					<div class="entry">
						<?$this->_render('inc_bbcode');?>
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<fieldset class="loggedOut twoCols">
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						</div>
					</fieldset>
				</div>
				<div class="formActions">
					<input type="submit" name="submit" value="отправить" onclick="this.enabled = false;" />
				</div>
			</form>
            <?php } else { ?>
                <h4>настройки приватности не позволяют вам писать сообщения</h4>
            <?php } ?>
		</div>
		<?} else {?>
		<div class="systemMessage">
			<p>Пока фотографий нет</p>
		</div>
	<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>