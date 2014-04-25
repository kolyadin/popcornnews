<?$this->_render('inc_header', array('title' => 'Альбомы группы - ' . htmlspecialchars($d['group']['title']), 'header' => 'Альбомы группы', 'top_code' => 'C', 'header_small' => $d['group']['title']));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		
		<?if ($d['albumsNum'] > 0) {?>
		<div class="group_photo">
			<ul>
				<?foreach ($d['albums'] as $album) {?>
					<li>
						<a href="/community/group/<?=$d['group']['id']?>/album/<?=$album['id']?>#img<?=$album['lastPhoto']['id']?>">
							<img alt="" src="<?=$this->getStaticPath(Community::getWWWAlbumPhotoPath($album['lastPhoto']['aid'], $album['lastPhoto']['image']))?>" />
							<?=$album['title']?>
							<?if ($d['canModifyGroup']) {?>
							<a href="/community/group/<?=$d['group']['id']?>/album/<?=$album['id']?>/delete" title="Удалить">удалить</a>
							<a href="/community/group/<?=$d['group']['id']?>/album/<?=$album['id']?>/edit">изменить</a>
							<?}?>
						</a>
						<?=$album['photos']?> фото
					</li>
				<?}?>
			</ul>
		</div>
		
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
				<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
					<?if (!isset($pi['current'])) {?>
					<a href="/community/group/<?=$d['group']['id']?>/albums/page/<?=$pi['link']?>"><?=$pi['text']?></a>
					<?} else {?>
					<?=$pi['text']?>
					<?}?>
				</li>
				<?}?>
			</ul>
		</div>
		<?} else {?>
		<h4>В этой группе еще нет ни одного альбома</h4>
		<?}?>
		
		<?if ($d['canModifyGroup']) {?>
		<h4><a href="/community/group/<?=$d['group']['id']?>/album/add">Новый альбом</a></h4>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>