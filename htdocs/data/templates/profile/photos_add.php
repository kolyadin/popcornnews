<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Анкета', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'Редактирование твоих данных'));
$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
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
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/form">анкета</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/form">редактировать</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos">фотографии</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos/del">удаление фото</a></li>
		</ul>
		<h2>Добавление фотографий</h2>
		<form class="questionnaireForm newPhotos" method="POST" action="/index.php" enctype="multipart/form-data">
			<input type="hidden" name="type" value="profile">
			<input type="hidden" name="action" value="add_photo">
			<fieldset>
				<span>Фото 1</span>
				<input type="file" name="photo[]" />
				<span class="desc">Описание фотографии:</span>
				<input type="text" name="descr[]" maxlength="160" value="" />
			</fieldset>
			<fieldset>
				<span>Фото 2</span>
				<input type="file" name="photo[]" />
				<span class="desc">Описание фотографии:</span>
				<input type="text" name="descr[]" maxlength="160" value="" />
			</fieldset>
			<fieldset>
				<span>Фото 3</span>
				<input type="file" name="photo[]" />
				<span class="desc">Описание фотографии:</span>
				<input type="text" name="descr[]" maxlength="160" value="" />
			</fieldset>
			<input type="submit" value="сохранить" />
		</form>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>