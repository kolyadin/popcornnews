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
		<?if (isset($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		
		<div class="communityAddAlbum">
			<form action="" method="POST" class="answer">
				<input type="hidden" name="type" value="community">
				<input type="text" name="title" value="" />

				<input type="submit" value="отправить" />
			</form>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>