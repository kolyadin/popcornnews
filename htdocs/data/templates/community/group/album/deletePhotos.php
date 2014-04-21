<?$this->_render('inc_header', array('title' => 'Альбом ' . htmlspecialchars($d['album']['title']), 'header' => 'Альбом', 'top_code' => 'C', 'header_small' => $d['album']['title']));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/community/group/<?=$d['group']['id']?>">группа</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/topics">обсуждения</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/albums">фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/members">участники</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/newsfeed">обновления</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>">все фото</a></li>
			<li><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/addPhotos">загрузить фото</a></li>
			<li class="active"><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/deletePhotos">удалить фото</a></li>
		</ul>
		
		<?if (isset($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		<div class="communityDeleteAlbumPhoto">
		<?if (count($d['photos']) > 0) {?>
		<form method="POST" action="">
			<input type="hidden" name="type" value="community">
			<div class="groupsContainer equalsContainer gonnaExit undecorated">
				<?foreach ($d['photos'] as $i => $photo) {?>
				<dl>
					<dt><input type="checkbox" name="p[<?=$photo['id']?>]"/><img alt="" src="<?=$this->getStaticPath(Community::getWWWAlbumPhotoPath($photo['aid'], $photo['image'], '80x100a'))?>" /></dt>
				</dl>
				<?if (($i + 1) % 6 == 0) {?><div class="divider"></div><?}?>
				<?}?>
			</div>
			<a href="#" onclick="as.parent(this, 'form').submit(); return false;" class="saveButton">сохранить</a>
		</form>
		<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>