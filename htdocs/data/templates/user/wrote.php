<?
$this->_render('inc_header', array('title' => $d['user']['nick'], 'header' => htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false), 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['user']['avatara'], true)) . '" alt="' . htmlspecialchars($d['user']['nick']) . '" class="avaProfile">', 'header_small' => 'Гостевая пользователя', 'js' => 'Comments.js?d=13.05.11'));
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
			<?if ($p['query']->get_num('profile_pix', array('uid'=>$d['user']['id'])) > 0) {?>
			<li><a href="/user/<?=$d['user']['id']?>/photos">фотографии</a></li>
			<?}?>
			<li><a href="/user/<?=$d['user']['id']?>/guestbook">гостевая</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/gifts">подарки</a></li>
			<li class="active"><a href="/user/<?=$d['user']['id']?>/wrote">пишет</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/community/groups">группы</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/sets">your.style</a></li>
		</ul>
		<h2><?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?> пишет</h2>
		<div class="trackContainer commentsTrack">
			<?
			$limit = 50;
			$offset = ($d['page'] - 1) * $limit;
			$num_msgs = RoomFactory::getAllUserMessageCount($d['user']['id']);
			$pages = ceil($num_msgs / $limit);

			$user = &$d['user'];
            $msgs = RoomFactory::getUserMessage($d['user']['id'], $offset, $limit);
			foreach ($msgs as $i => $msg) {
                $message = new Message($msg);
				$new = array_shift($p['query']->get('comments_parents', array('id' => $msg['news_id']), null, 0, 1));
                $link = "/news/{$msg['news_id']}/#cid_{$msg['id']}";
			?>
			<div class="trackItem" id="<?=$msg['id']?>">
				<div class="post">
					<div class="entry">
						<p><?=(!$msg['deleted'] ? $this->preg_repl($p['nc']->get($message->getContent())) : COMMENTS_DELETE_PHRASE)?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$user['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
					<?if (!empty($d['cuser']) && !$msg['deleled']) {?>
					<div class="mark">
						<span class="up"><span><?=$msg['rating_up']?></span></span>
						<span class="down"><span>-<?=$msg['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<span class="toTheme">К новости <a href="<?=$link;?>">&laquo;<?=$new['name']?>&raquo;</a></span>
						<span class="date"><?=$p['date']->unixtime($msg['date'], "%d %F %Y, %H:%i")?></span>
						<a class="pc-user" rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						<?if (!$msg['deleted'] && $this->isModer()) {?>
						<nobr>
						<span class="delete">удалить</span>
						</nobr>
						<?}?>
						<?$rating = $p['rating']->_class($user['rating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$user['rating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$user['rating']?></span>
						</div>
					</div>
				</div>
			</div>
			<?}?>
		</div>
		<div class="noUpperBorder paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $pages, 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/user/<?=$d['user']['id']?>/wrote/page/<?=$pi['link']?>"><?=$pi['text']?></a>
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
