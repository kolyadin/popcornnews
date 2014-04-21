<?
$this->_render('inc_header', array('title'=>$d['user']['nick'], 'header'=>htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false), 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['user']['avatara'], true)) . '" alt="' . htmlspecialchars($d['user']['nick']) . '" class="avaProfile">', 'header_small'=>'Гостевая пользователя'));

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
			<?if ($p['query']->get_num('profile_pix', array('uid'=>$d['user']['id'])) > 0) {?>
			<li><a href="/user/<?=$d['user']['id']?>/photos">фотографии</a></li>
			<?}?>
			<li class="active"><a href="/user/<?=$d['user']['id']?>/guestbook">гостевая</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/gifts">подарки</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/wrote">пишет</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/community/groups">группы</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/sets">your.style</a></li>
		</ul>
		<h2>Гостевая книга <?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?></h2>
		<div class="trackContainer commentsTrack">
			<?
			$limit = 10;
			$offset = ($d['page'] - 1) * $limit;
			$num_msgs = $p['query']->get_num('user_msgs', array('uid'=>$d['user']['id'], 'private'=>0));
			$pages = ceil($num_msgs / $limit);

			foreach ($p['query']->get('user_msgs', array('uid'=>$d['user']['id'], 'private'=>0), array('cdate desc'), $offset, $limit) as $i => $msg) {
				if (!$msg['pid']) {
					$user = array_shift($p['query']->get('users', array('id'=>$msg['aid']), null, 0, 1));
				} else {
					$user = array_shift($p['query']->get('users', array('id'=>$msg['uid']), null, 0, 1));
				}
				?>
			<div class="trackItem">
				<div class="post">
					<div class="entry">
						<p><?=$this->preg_repl($p['nc']->get($msg['content']))?></p>
					</div>
					<a rel="nofollow" href="/profile/<?=$user['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
					<div class="details">
						<a class="pc-user" rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						<span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span>
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
					<a href="/user/<?=$d['user']['id']?>/guestbook/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?} else {?>
					<?=$pi['text']?>
					<?}?>
				</li>
				<?}?>
			</ul>
		</div>
		<div class="trackContainer mailTrack"><a name="form"></a>
            <?php if(!$blackList->isUserExists($d['cuser']['id'])) { ?>
			<form action="/index.php"  class="answer" method="POST" name="fmr">
                        <input type="hidden" name="type" value="guestbook">
                        <input type="hidden" name="action" value="add_comment">
                        <input type="hidden" name="uid" value="<?=$d['user']['id']?>">
				<div class="trackItem answering">
					<div class="entry">
						<?$this->_render('inc_bbcode');?>
						<?$this->_render('inc_smiles');?>
						<textarea name="content"></textarea>
					</div>
					<div class="aboutMe">
						<?
						$img = (empty($d['cuser']['avatara']) ? '/img/no_photo_small.jpg' : '/avatars_small/' . $d['cuser']['avatara']);
						?>
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
						<span>Вы пишете как</span><br />
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					</div>
					<input type="submit" value="отправить" onclick="this.enabled = false;" />
				</div>
			</form>
            <?php } else { ?>
                <h4>настройки приватности не позволяют вам писать сообщения</h4>
            <?php } ?>
		</div>
	</div>
<?$this->_render('inc_right_column');?>
</div>
	<?$this->_render('inc_footer');?>
