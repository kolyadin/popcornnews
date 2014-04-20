<?
$this->_render('inc_header', array('title' => $d['user']['nick'], 'header' => htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false), 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['user']['avatara'], true)) . '" alt="' . $d['user']['nick'] . '" class="avaProfile">', 'header_small' => 'Друзья пользователя'));
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
			<li class="active"><a href="/user/<?=$d['user']['id']?>/friends">друзья</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/persons">персоны</a></li>
			<?if ($p['query']->get_num('profile_pix', array('uid'=>$d['user']['id'])) > 0) {?>
			<li><a href="/user/<?=$d['user']['id']?>/photos">фотографии</a></li>
			<?}?>
			<li><a href="/user/<?=$d['user']['id']?>/guestbook">гостевая</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/gifts">подарки</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/wrote">пишет</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/community/groups">группы</a></li>
			<li><a href="/profile/<?=$d['user']['id']?>/sets">your.style</a></li>
		</ul>
		<h2>Друзья <?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?></h2>
		<table class="contentUsersTable">
			<tr>
				<th class="user">Пользователь</th>
				<th class="starRating">&nbsp;</th>
				<th class="city"><a href="/user/<?=$d['user']['id']?>/friends/sort/city<?=($d['sort'] == 'city' ? '_desc' : '')?>">Город</a></th>
				<th class="rating"><a href="/user/<?=$d['user']['id']?>/friends/sort/rating<?=($d['sort'] == 'rating' ? '_desc' : '')?>">Рейтинг</a></th>
			</tr>
			<?foreach ($d['friends'] as $i => $friend) {
				$img = (empty($friend['avatara']) ? '/img/no_photo_small.jpg' : '/avatars_small/' . $friend['avatara']);
			?>
			<tr id="f_<?=$friend['fid']?>">
				<?$o_o = new VPA_online($friend['fid']);?>
				<td class="user<?=($o_o->cache_get_online()) ? ' note':''?>">
					<a rel="nofollow" href="/profile/<?=$friend['fid']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($friend['avatara']))?>" alt="" /></a>
					<a rel="nofollow" href="/profile/<?=$friend['fid']?>"><span><?=(strlen($friend['nick']) < 20 ? htmlspecialchars($friend['nick'], ENT_IGNORE, 'cp1251', false) : substr(htmlspecialchars($friend['nick'], ENT_IGNORE, 'cp1251', false), 0, 20) . ' ...')?></span></a>
					<?
					$o_o = new VPA_online($friend);
					if ($o_o->cache_get_online()) {
					?>
					<small>[on-line]</small>
					<?}?>
				</td>
				<td class="starRating">
					<?$rating = $p['rating']->_class($friend['rating']);?>
					<div class="userRating <?=$rating['class']?>" title="<?=$friend['rating']?>">
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
			</tr>
			<?}?>
		</table>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/user/<?=$d['user']['id']?>/friends/sort/<?=$d['sort']?>/page/<?=$pi['link']?>"><?=$pi['text']?></a>
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
