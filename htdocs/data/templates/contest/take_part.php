<?
$this->_render('inc_header',
	array('title'=>'Выиграй встречу с Анджелиной Джоли - Принять участие')
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content" class="content-contest">
		<h4><?=(isset($d['error']) ? $d['error'] : null)?></h4>

		<form class="questionnaireForm" method="post" enctype="multipart/form-data">
			<input type="hidden" name="type" value="contest" />
			<input type="hidden" name="action" value="take_part" />

			<label>
				<strong style="width: auto;">Расскажите, почему именно вы должны выиграть</strong>
				<textarea name="description" style="height: 150px;"></textarea>
			</label>

			<label>
				<strong>
					Коллаж (раширение jpg, png, gif)
					<input
						id="imageCheckbox"
						type="checkbox"
						name="imageCheckbox"
						value="1"
						onclick="hide_display(this.checked, 'input#imageFile'); if (this.checked) {as.$$('input#imageFile').focus(); as.$$('input#videoCheckbox').checked = false; as.w('.videoInfo').each(function() {this.style.display = 'none';});}"
					/>
				</strong>
			</label>
			<input id="imageFile" type="file" name="image" style="display: none;" />

			<label>
				<strong>
					Видео
					<input
						id="videoCheckbox"
						type="checkbox"
						name="videoCheckbox"
						value="1"
						onclick="hide_display(this.checked, '.videoInfo'); if (this.checked) {as.$$('.videoInfo').focus(); as.$$('input#imageCheckbox').checked = false; as.$$('input#imageFile').style.display = 'none';}"
					/>
				</strong>
			</label>
			<label class="videoInfo" style="display: none;"><strong>Код</strong><textarea style="height: 75px;" class="videoInfo" id="videoTextarea" name="video"></textarea></label>
			<label class="videoInfo" style="display: none;"><strong>Превью (любое изображение с Анджелиной Джоли, расширение jpg, png, gif)</strong><input type="file" name="videoPreview" /></label>

			<input type="submit" value="сохранить">
		</form>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>