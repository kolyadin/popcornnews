<?
$this->_render('inc_header', array('title' => $d['cuser']['nick'], 'header' => 'Группы', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small' => 'Твой профиль'));

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
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/newsfeed">обновления</a></li>
		</ul>
		<h2>Группы</h2>

		<div class="groups">
			<?foreach ($d['groups'] as &$group) {?>
			<dl class="group">
				<dt>
					<?if ($group['image']) {?>
					<a href="/community/group/<?=$group['id']?>"><img alt="" src="<?=$this->getStaticPath(Community::getWWWAvatarPath($group['image']))?>" /></a>
					<?} else {?>
					&nbsp;
					<?}?>
				</dt>
				<dd>
					<a class="h3" href="/community/group/<?=$group['id']?>"><?=$group['title']?></a>
					<p><?=$this->limit_text($group['description'], 600)?></p>
					<span class="date">Создано <?=$p['date']->unixtime($group['createtime'], '%d %F %Y')?></span>
				</dd>
			</dl>
			<?}?>
			
			<div class="noUpperBorder paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>