<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Друзья', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'Твой профиль'));

$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">персоны</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">гостевая</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends/">список друзей</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends/wrote">что пишут</a></li>
		</ul>
		<h2>Список твоих друзей</h2>
		<table class="contentUsersTable">
			<tr>
				<th class="user">Пользователь</th>
				<th class="starRating">&nbsp;</th>
				<th class="city"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends/edit/sort/city<?=($d['sort'] == 'city' ? '_desc' : '')?>">Город</a></th>
				<th class="rating"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends/edit/sort/rating<?=($d['sort'] == 'rating' ? '_desc' : '')?>">Рейтинг</a></th>
				<th class="actions">&nbsp;</th>
			</tr>
			<?foreach ($d['friends'] as $i => $friend) {?>
			<tr id="f_<?=$friend['id']?>">
				<?$o_o = new VPA_online($friend);?>
				<td class="user <?=($friend['confirmed'] != 1 || $o_o->cache_get_online()) ? 'note' : ''?>">
					<a rel="nofollow" href="/profile/<?=$friend['fid']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($friend['avatara']))?>" alt="" /></a>
					<a rel="nofollow" href="/profile/<?=$friend['fid']?>"><span><?=(strlen($friend['nick']) < 20 ? htmlspecialchars($friend['nick'], ENT_IGNORE, 'cp1251', false) : substr(htmlspecialchars($friend['nick'], ENT_IGNORE, 'cp1251', false), 0, 20) . ' ...')?></span></a>
					<?if ($friend['confirmed'] != 1 && $friend['whose'] == 'my') {?>
					<small>[жду подтверждения]</small>
					<?}?>
					<?if ($friend['confirmed'] != 1 && $friend['whose'] == 'her') {?>
					<small>[ждет подтверждения]</small>
					<?}?>
					<?if ($o_o->cache_get_online()) {?>
					<small>[on-line]</small>
					<?}?>
				</td>
				<td class="starRating">
					<?$rating = $p['rating']->_class($friend['rating']);?>
					<div class="userRating <?=$rating['class']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$rating['name']?></span>
					</div>
				</td>
				<td class="city">
					<span><?=$friend['city']?></span>
				</td>
				<td class="rating">
					<span><?=$friend['rating']?></span>
				</td>
				<td class="actions">
					<?if ($friend['confirmed'] != 1 && $friend['whose'] == 'her') {?>
					<a href="#" onclick="c_confirm_friend(<?=$friend['id']?>); return false;">Принять</a>
					<a href="#" onclick="c_reject_friend(<?=$friend['id']?>); return false;">Отказать</a>
					<?} else {?>
					<a href="#" onclick="c_del_friend('<?=htmlspecialchars(str_replace("'", "\'", $friend['nick']), ENT_IGNORE, 'cp1251', false)?>',<?=$friend['id']?>); return false;">Удалить</a>
					<?}?>
				</td>
			</tr>
		<?}?>
		</table>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends/edit/sort/<?=$d['sort']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
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