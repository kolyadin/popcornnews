<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Друзья', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'Твой профиль'));

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
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">главная</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/form">редактировать</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos">фотографии</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos/del">удаление фото</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/blacklist">черный список</a></li>
		</ul>
		<h2>Удалить фото</h2>
		<?
		$photos = $p['query']->get('profile_pix', array('uid'=>$d['cuser']['id']), array('cdate desc'), null, null);
		if (count($photos) > 0) {
		?>
		<form method="POST" action="/" name="fr">
			<input type="hidden" name="type" value="photo">
			<input type="hidden" name="action" value="del">
			<div class="groupsContainer equalsContainer gonnaExit undecorated">
				<?foreach ($photos as $i => $img) {?>
				<dl>
					<dt><input type="checkbox" name="p[<?=$img['id']?>]"/><img alt="" src="<?=$this->getStaticPath($this->getUserPhoto($img['filename'], 'del'))?>" /></dt>
				</dl>
				<?if (($i + 1) % 6 == 0) {?><div class="divider"></div><?}?>
				<?}?>
			</div>
			<a href="#" onclick="document.forms['fr'].submit(); return false;" class="saveButton">сохранить</a>
		</form>
		<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>