<?
$this->_render('inc_header', array('title' => 'Группа - ' . htmlspecialchars($d['group']['title']), 'header' => 'Группа', 'top_code' => 'C', 'header_small' => $d['group']['title'], 'js' => array('Community.js')));
?>
<script type="text/javascript">
	var community = new Community();
</script>

<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>">информация</a></li>
			<?if ($d['canModifyGroup'] || $this->isCommunityModer()) {?>
			<li><a href="/community/group/<?=$d['group']['id']?>/edit">редактировать</a></li>
			<?}?>
			<?if ($d['canModifyGroup']) {?>
			<li><a href="/community/group/<?=$d['group']['id']?>/invites">пригласить</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/editMembers">редактировать состав</a><span class="marked"><?=$d['newMembersNum']?></a></li>
			<?}?>
		</ul>
		
		<div class="group_info">
			<dl class="group">
				<dt>
					<?if ($d['group']['image']) {?>
					<img alt="" src="<?=$this->getStaticPath(Community::getWWWAvatarPath($d['group']['image']))?>" />
					<?}?>
				</dt>
				<dd>
					<p><?=$this->limit_text($d['group']['description'], 600)?></p>
					<p class="tags">
						<span>Тэги:</span>
						<?
						$i = 0; foreach ($d['communityTags'] as $tag) {
							printf('<a href="/%s/%u">%s</a>%s' . "\n", $tag['type'] == 'persons' ? 'tag' : 'event', $tag['id'], $tag['name'], (++$i != count($d['communityTags']) ? ', ' : null));
						}
						?>
					</p>
					<?if ($d['group']['type'] == 'private') {?><p>Группа закрытая</p><?}?>
				</dd>
			</dl>
			<table>
				<tbody>
					<tr>
						<td class="first">
							<?if ($d['isAMember']) {?>
							<a class="leave" href="/community/group/<?=$d['group']['id']?>/member/leave" onclick="community.leave(event);"></a>
							<?} else {?>
							<a class="enter" href="/community/group/<?=$d['group']['id']?>/member/enter" onclick="community.enter(event);"></a>
							<?}?>
							
							<a class="participant" href="/community/group/<?=$d['group']['id']?>/members"><?=$d['membersNum']?> <?=$p['declension']->get($d['membersNum'], 'участник', 'участника', 'участников')?></a>
						</td>
						<td class="second">
							<a href="#"></a>
							<span class="date">Создано <?=$p['date']->unixtime($d['group']['createtime'], '%d %F %Y')?></span>
							<a rel="nofollow" href="/profile/<?=$d['creator']['id']?>"><?=htmlspecialchars($d['creator']['nick'], ENT_IGNORE, 'cp1251', false);?></a><br />
						</td>
						<td class="third">
							<img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['creator']['avatara']))?>" />
						</td>
					</tr>
					
					<?if (is_array($d['assistants']) && count($d['assistants']) > 0) {?>
					<tr class="assistants">
						<td colspan="3">
							Модераторы:
							<?$i = 0; foreach ($d['assistants'] as $assitant) { $i++;?>
							<a rel="nofollow" href="/profile/<?=$assitant['id']?>"><?=htmlspecialchars($assitant['nick'], ENT_IGNORE, 'cp1251', false);?></a><?=($i != count($d['assistants']) ? ', ' : null)?>
							<?}?>
						</td>
					</tr>
					<?}?>
				</tbody>
			</table>
		</div>
		<?if ($d['albumsNum'] > 0) {?>
		<div class="group_photo">
			<div class="irhContainer">
				<h3><a href="/community/group/<?=$d['group']['id']?>/albums" class="replacer"></a>Фотографии</h3>
				<span class="counter"><a href="/community/group/<?=$d['group']['id']?>/albums"><?=$d['albumsNum']?></a></span>
			</div>
			<ul>
				<?foreach ($d['albums'] as $album) {?>
					<li><a href="/community/group/<?=$d['group']['id']?>/album/<?=$album['id']?>#img<?=$album['lastPhoto']['id']?>"><img alt="" src="<?=$this->getStaticPath(Community::getWWWAlbumPhotoPath($album['lastPhoto']['aid'], $album['lastPhoto']['image']))?>" /><?=$album['title']?></a><?=$album['photos']?> фото</li>
				<?}?>
			</ul>
		</div>
		<?}?>

		<?if ($d['topicsNum'] > 0) {?>
		<div class="group_disc">
			<div class="irhContainer">
				<h3><a href="/community/group/<?=$d['group']['id']?>/topics" class="replacer"></a>Обсуждения</h3>
				<span class="counter"><a href="/community/group/<?=$d['group']['id']?>/topics"><?=$d['topicsNum']?></a></span>
			</div>
				<?foreach ($d['topics'] as $topic) {?>
				<dl>
					<dt><a rel="nofollow" href="/profile/<?=$topic['uid']?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($topic['uavatara']))?>" alt="" /></a></dt>
					<dd>
						<a class="h" href="/community/group/<?=$d['group']['id']?>/topic/<?=$topic['id']?>"><?=$topic['title']?><span> (<?=$topic['messagesNum']?>)</span></a>

						<?if ($topic['lastMessage']) {?>
						<div class="last">
							<span>Последний пост:</span>
							<a rel="nofollow" href="/profile/<?=$topic['lastMessage']['uid']?>"><?=$topic['lastMessage']['unick']?></a>
							<?$rating = $p['rating']->_class($topic['lastMessage']['urating']);?>
							<div class="userRating <?=$rating['class']?>">
								<div class="rating <?=$rating['stars']?>"></div>
								<span><?=$topic['lastMessage']['urating']?></span>
							</div>
							<noindex><span class="date"><?=$p['date']->unixtime($topic['lastMessage']['createtime'], '%d %F %Y, %H:%i')?></span></noindex>
						</div>
						<p><?=(!$topic['lastMessage']['deletetime'] ? $this->preg_repl($p['nc']->get($topic['lastMessage']['message'])) : COMMENTS_DELETE_PHRASE)?></p>
						<?}?>
					</dd>
				</dl>
				<?}?>
			
			<?if ($d['canModifyGroup']) {?>
			<a class="new" href="/community/group/<?=$d['group']['id']?>/topic/add">Создай свою тему!</a>
			<?}?>
		</div>
		<?}?>

		<?if ($d['membersNum'] > 0) {?>
		<div class="group_members">
			<div class="irhContainer">
				<h3><a href="/community/group/<?=$d['group']['id']?>/members" class="replacer"></a>участники</h3>
				<span class="counter"><a href="/community/group/<?=$d['group']['id']?>/members"><?=$d['membersNum']?></a></span>
			</div>
			<?
			$i = 0; foreach ($d['members'] as $member) { $i++;
				if ($i % 10 == 1) printf('<ul>' . "\n");
				printf('<li><a rel="nofollow" href="/profile/%u"><img alt="" src="%s" /></a></li>', $member['id'], $this->getStaticPath($this->getUserAvatar($member['avatara'])));
				if ($i % 10 == 0 || $i == count($d['members'])) printf('</ul>' . "\n");
			}
			?>
		</div>
		<?}?>

	</div>

	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>