<?$this->_render('inc_header', array('title' => 'Новый альбом', 'header' => 'Новый альбом', 'top_code' => 'C', 'header_small' => 'Новый альбом'));?>
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
			<?if ($d['isAMember']) {?><li class="active"><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/addPhotos">загрузить фото</a></li><?}?>
			<?if ($d['canModifyGroup']) {?><li><a href="/community/group/<?=$d['group']['id']?>/album/<?=$d['album']['id']?>/deletePhotos">удалить фото</a></li><?}?>
		</ul>
		
		<?if (isset($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		<div class="communityAddAlbumPhoto">
			<form action="" method="POST" enctype="multipart/form-data" class="answer">
				<input type="hidden" name="type" value="community">
				<div class="rules">
					<h3>Правила при загрузке фотографий:</h3>
					<ol>
						<li>Размер фотографий должен быть не менее 350*450 точек.</li>
						<li>Формат фотографий – JPEG/JPG</li>
						<li>На фото не должно быть никаких ссылок на сайты, с которых они были скачаны. Убедительная просьба не грузить сотни фотографий с Кинопоиска и Киномании</li>
						<li>Просьба не загружать анимированные картинки.</li>
					</ol>
				</div>
				<input type="file" name="photo[]" />
				<input type="file" name="photo[]" />
				<input type="file" name="photo[]" />
				
				<input type="submit" value="Отправить" />
			</form>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>