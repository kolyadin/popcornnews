<?
$this->_render('inc_header', array('title' => $d['cuser']['nick'], 'header' => 'Я пишу', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small' => 'Все твои сообщения оставленные на сайте', 'js' => 'Comments.js?d=13.05.11'));
$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">персоны</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">гостевая</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote/notifications">уведомления</a></li>
		</ul>
		<div class="trackContainer commentsTrack">
			<?
			/*$limit = 50;
			$offset = ($d['page'] - 1) * $limit;
			$num_msgs = $p['query']->get_num('comments', array('user_id' => $d['cuser']['id'], 'private' => 0));
			$pages=ceil($num_msgs/$limit);
			foreach ($p['query']->get('comments', array('user_id' => $d['cuser']['id']), array('id desc'), $offset, $limit) as $i => $msg) {
				$new = array_shift($p['query']->get('comments_parents', array('id' => $msg['new_id']), null, 0, 1));*/
            $limit = 50;
            $offset = ($d['page'] - 1) * $limit;
            $num_msgs = RoomFactory::getAllUserMessageCount($d['cuser']['id']);
            $pages = ceil($num_msgs / $limit);

            $user = &$d['user'];
            $msgs = RoomFactory::getUserMessage($d['cuser']['id'], $offset, $limit);
            foreach ($msgs as $i => $msg) {
                $message = new Message($msg);
                $new = array_shift($p['query']->get('comments_parents', array('id' => $msg['news_id']), null, 0, 1));
                $link = "/news/{$msg['news_id']}/#cid_{$msg['id']}";
			?>
			<div class="trackItem" id="<?=$msg['id']?>">
				<div class="post">
					<div class="entry">
						<p><?=(!$msg['deleted'] ? $this->preg_repl($p['nc']->get($msg['content'])) : COMMENTS_DELETE_PHRASE)?></p>
					</div>
					<a href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" alt="" /></a>
					<?if (!empty($d['cuser']) && !$msg['deleted']) {?>
					<div class="mark">
						<span class="up"><span><?=$msg['rating_up']?></span></span>
						<span class="down"><span>-<?=$msg['rating_down']?></span></span>
					</div>
					<?}?>
					<div class="details">
						<span class="toTheme">К новости <a href="<?=$link;?>">&laquo;<?=$new['name']?>&raquo;</a></span>
						<span class="date"><?=$p['date']->unixtime($msg['date'],"%d %F %Y, %H:%i")?></span>
						<a class="pc-user" rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false)?></a>
						<?if (!$msg['del']) {?>
						<nobr>
						<span class="delete">удалить</span>
						</nobr>
						<?}?>
						<?$rating=$p['rating']->_class($d['cuser']['rating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$d['cuser']['rating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$d['cuser']['rating']?></span>
						</div>
					</div>
				</div>
			</div>
			<?}?>
		</div>
		<div class="noUpperBorder paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'],$pages,10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote/page/<?=$pi['link']?>"><?=$pi['text']?></a>
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