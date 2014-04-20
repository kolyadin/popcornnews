<form class="questionnaireForm newPhotos" method="POST" action="/index.php" enctype="multipart/form-data">
	<input type="hidden" name="type" value="persons">
	<input type="hidden" name="action" value="add_photo">
	<input type="hidden" name="pid" value="<?=$d['person']['id']?>">
	<div class="rules">
		<h3>Правила при загрузке фотографий:</h3>
		<ol>
			<li>Размер фотографий должен быть не менее 350*450 точек.</li>
			<li>Формат фотографий – JPEG/JPG</li>
			<li>На фото не должно быть никаких ссылок на сайты, с которых они были скачаны. Убедительная просьба не грузить сотни фотографий с Кинопоиска и Киномании</li>
			<li>Обои загружает только администрация.</li>
			<li>Если Вы не увидели своих фото в галерее, не нужно грузить их по несколько раз. Либо они еще не были опубликованы, либо не подошли по формату. От постоянных добавлений быстрее на сайте они не появятся.</li>
			<li>Перед тем, как грузить фотографию, проверьте: возможно данное фото уже присутствует в галерее. </li>
			<li>Просьба не загружать анимированные картинки.</li>
		</ol>
	</div>
	<label>
		<input type="file" name="photo[]" />
		<span>Фото 1</span>
	</label>
	<label>
		<input type="file" name="photo[]" />
		<span>Фото 2</span>
	</label>
	<label>
		<input type="file" name="photo[]" />
		<span>Фото 3</span>
	</label>
	<input type="submit" value="сохранить" />
</form>