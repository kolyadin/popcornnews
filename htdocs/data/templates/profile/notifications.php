<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Я пишу', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'Уведомления'));
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
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">пишу</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote/notifications">уведомления</a></li>
		</ul>
		<div class="trackContainer commentsTrack">
			<?
			foreach ($d['notifications'] as $i => $notify) {
				if ($notify['readed'] == 0) $p['query']->set('notifications', array('readed' => 1), $notify['id']);
			?>
			<div class="trackItem" id="<?=$notify['id'];?>"<?=($notify['readed'] == 0 ? ' style="background-color: #E5E5E5;"' : '')?>>
				<div class="post">
					<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true))?>" alt="" /></a>
					<div class="details">
						<span class="toTheme">Пользователь <?=$notify['anick']?> ответил на ваш комментарий в теме <a href="<?=$notify['title_link']?>">&laquo;<?=$notify['title']?>&raquo;</a></span>
						<span class="toTheme"><a href="<?=$notify['link']?>">перейти к комментарию</a></span>
						<span class="date"><?=$p['date']->unixtime(strtotime($notify['regtime']), "%d %F %Y, %H:%i")?></span>
						<a class="reply" href="#" onclick="delete_msg(<?=$notify['id']?>, 'notify'); return false;">удалить</a>
					</div>
				</div>
			</div>
			<?}?>
		</div>
		<div class="noUpperBorder paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote/notifications/<?=$pi['link']?>"><?=$pi['text']?></a>
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
