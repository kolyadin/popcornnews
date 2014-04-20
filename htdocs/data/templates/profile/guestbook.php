<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Твоя гостевая', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'профиль пользователя'));

$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));
$U_ = explode('/', $_SERVER['REQUEST_URI']);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">персоны</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">гостевая</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<h2>Твоя гостевая</h2>
		<div class="trackContainer commentsTrack">
			<?
			$limit = 10;
			$offset = ($d['page'] - 1) * $limit;
			$num_msgs = $p['query']->get_num('user_msgs', array('uid'=>$d['cuser']['id'], 'private'=>0));
			$pages = ceil($num_msgs / $limit);

			foreach ($p['query']->get('user_msgs', array('uid'=>$d['cuser']['id'], 'private'=>0), array('cdate desc'), $offset, $limit) as $i => $msg) {
				$user = array_shift($p['query']->get('users', array('id' => $msg['aid']), null, 0, 1));
			?>
			<div class="trackItem" id="<?=$msg['id'];?>">
				<div class="post">
					<div class="entry">
						<p><?=$this->preg_repl($p['nc']->get($msg['content']))?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$user['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						<span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span>
						<a class="reply" href="#" onclick="var a=document.getElementById('m_<?=$msg['id']?>'); a.style.display=a.style.display=='block' ? 'none':'block'; return false;">ответить</a>
						<a class="reply" href="#" onclick="delete_msg(<?=$msg['id'];?>, 'wall'); return false;">удалить</a>
						<?$rating = $p['rating']->_class($user['rating']);?>
						<div class="userRating <?=$rating['class']?>" title="<?=$user['rating']?>">
							<div class="rating <?=$rating['stars']?>"></div>
							<span><?=$user['rating']?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="trackContainer mailTrack">
				<div class="trackItem answering" id="m_<?=$msg['id']?>" style="display:none">
					<form class="answer newMessage" action="/index.php" method="POST">
						<input type="hidden" name="type" value="guestbook">
						<input type="hidden" name="action" value="add_comment">
						<input type="hidden" name="uid" value="<?=$msg['aid']?>">
							<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
						<div class="meta">
							<input type="submit" value="отправить" />
						</div>
					</form>
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
					<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook/page/<?=$pi['link']?>"><?=$pi['text']?></a>
							<?} else {?>
							<?=$pi['text']?>
							<?}?>
				</li>
					<?}?>
			</ul>
		</div>
		<div class="trackContainer mailTrack">
			<form action="/"   class="answer" method="POST" name="fmr">
				<input type="hidden" name="type" value="guestbook">
				<input type="hidden" name="action" value="add_comment">
				<input type="hidden" name="uid" value="<?=$d['cuser']['id']?>">
				<div class="trackItem answering">
					<div class="entry">
						<?$this->_render('inc_bbcode');?>
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<div class="aboutMe">
						<?$img = (empty($d['cuser']['avatara']) ? '/img/no_photo_small.jpg' : '/avatars_small/' . $d['cuser']['avatara']);?>
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
						<span>Вы пишете как</span><br />
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					</div>
					<input type="submit" value="отправить" onclick="this.enabled = false;" />
				</div>
			</form>
		</div>
	</div>

	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
