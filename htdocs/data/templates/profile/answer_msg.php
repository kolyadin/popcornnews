<?
$this->_render('inc_header', array('title' => $d['cuser']['nick'], 'header' => 'Личные сообщения', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small' => 'Написать личное сообщение'));
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
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">принятые</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages/sent">отправленные</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages/new">написать новое</a></li>
		</ul>
		<div class="trackContainer mailTrack">
			<div class="trackItem answering">
				<?
				$msg = array_shift($p['query']->get('private_msgs', array('id'=>$d['pid']), 0, 1));
                $blackList = BlackListFactory::getBlackListForUser($msg['aid']);
				?>
				<div class="entry">
					<p><?=nl2br($this->preg_repl($p['nc']->get($msg['content'])))?></p>
				</div>
				<a rel="nofollow" href="/profile/<?=$msg['aid']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($msg['avatara']))?>" /></a>
				<div class="details">
					<a class="pc-user" rel="nofollow" href="/profile/<?=$msg['aid']?>"><?=htmlspecialchars($msg['nick'], ENT_IGNORE, 'cp1251', false);?></a>
					<span class="date"><?=$p['date']->unixtime($msg['cdate'], '%d %F %Y, %H:%i')?></span>
					<div class="rating"></div>
				</div>
                <?php if(!$blackList->isUserExists($d['cuser']['id'])) { ?>
				<form class="answer newMessage" action="/" method="POST" name="fmr">
					<input type="hidden" name="type" value="private_msg">
					<input type="hidden" name="action" value="answer">
					<input type="hidden" name="pid" value="<?=$d['pid']?>">
					<input type="hidden" name="uid" value="<?=$msg['aid']?>">
					<?$this->_render('inc_bbcode');?>
					<?$this->_render('inc_smiles');?>
					<textarea name="content"></textarea>
					<div class="meta">
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						</div>
						<input type="submit" value="отправить" onclick="this.enabled = false;" />
					</div>
				</form>
                <?php } else { ?>
                    <h4 style="clear:both;padding-top: 10px;">настройки приватности не позволяют вам ответить на это сообщение</h4>
                <?php } ?>
			</div>
		</div>
	</div>

	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
