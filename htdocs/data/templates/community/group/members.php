<?
$this->_render('inc_header', array('title' => 'Участники группы - ' . htmlspecialchars($d['group']['title'], ENT_IGNORE, 'cp1251', false), 'header' => 'Участники группы', 'top_code' => 'C', 'header_small' => $d['group']['title'], 'js' => array('Community.js')));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		
		<?if ($d['members']) {?>
		<table class="contentUsersTable membersUsersTable">
			<tr>
				<th class="user">Пользователь</th>
				<th class="starRating">&nbsp;</th>
				<th class="city">Город</th>
				<th class="rating">Рейтинг</th>
			</tr>
			<?foreach ($d['members'] as $i => $user) {?>
			<tr>
				<td class="user">
					<a rel="nofollow" href="/profile/<?=$user['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" /></a>
					<a rel="nofollow" href="/profile/<?=$user['id']?>"><span><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></span></a>
				</td>
				<td class="starRating">
					<?$rating = $p['rating']->_class($user['rating']);?>
					<div class="userRating <?=$rating['class']?>" title="<?=$user['rating']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$rating['name']?></span>
					</div>
				</td>
				<td class="city">
					<?=($user['city_id'] > 0) ? $user['city'] : 'Другой'?>
				</td>
				<td class="rating">
					<span><?=$user['rating']?></span>
				</td>
			</tr>
			<?}?>
		</table>
		
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/community/group/<?=$d['group']['id']?>/members/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?} else {?>
					<?=$pi['text']?>
					<?}?>
				</li>
				<?}?>
			</ul>
		</div>
		<?} else {?>
		<h2>В этой группе еще нет ни одного участника</h2>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>