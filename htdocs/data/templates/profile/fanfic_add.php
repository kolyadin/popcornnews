<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Прислать фанфик', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'Твой профиль'));

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
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>			
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics/all">все фанфики</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics/add">прислать</a></li>
		</ul>
		<div class="trackContainer fanficsTrack">
			<div class="trackContainer mailTrack">
				<div class="trackItem answering">
					<script type="text/javascript">
						function check_fr(frm)
						{
							frm.button.disabled = true;
							str = '';
							if (frm.name.value == '')
							{
								str='Вы не указали тему для фанфика!';
							}
							if (frm.content.length < 4000) {
								str = 'В графе «текст» размещается весь остальной рассказ, не менее 4000 знаков';
							}
							if (str != '')
							{
								alert (str);
								frm.button.disabled = false;
								return false;
							}
							return true;
						}
					</script>
					<p class="rules">
						Правила размещения фанфика:<br />
						1.Укажите название вашего рассказа<br />
						2.В графе «анонс» пишутся первые несколько предложений из всего фанфика.<br />
						3.В графе «текст» размещается весь остальной рассказ, не менее 4000 знаков.<br />
						4.Загружаемое изображение должно соответствовать теме вашего фанфика<br />
						5.Двойной «Enter» означает начало нового абзаца в вашем тексте. На каждый абзац можно поставить закладку.<br />
						6.Закладка нужна для того, чтобы в любой момент вернутся к тому месту, на котором вы остановились.<br />
						7.При размещении чужого творчества, указывайте имя автора и источник. Уважайте авторские права.<br />
						<br />
						Администрация сайта не несет ответственности за размещаемые пользователями материалы.<br />
					</p>
					<form name="fmr" method="POST" class="answer addFanfic" onSubmit="return check_fr(this);" enctype="multipart/form-data">
						<input type="hidden" name="type" value="fanfic">
						<input type="hidden" name="action" value="add">
						<?/*<input type="hidden" name="pid" value="<?=$d['page_action'];?>">*/?>
						<input type="hidden" name="page" value="<?=(isset($d['page']) ? $d['page'] : null);?>">
						<p>О ком:</p>
						<select class="selectReciever" name="pid">
						</select>
						<p>Название:</p><input type="text" name="name" value="<?=(isset($_POST['name']) ? $_POST['name'] : null);?>">
						<p>Анонс:</p><textarea class="announce" title="Максимум 400 символов" name="announce"><?=(isset($_POST['announce']) ? $_POST['announce'] : null);?></textarea>
						<p>Текст:</p>
						<?$this->_render('inc_bbcode');?>
						<textarea class="content" name="content"><?=(isset($_POST['content']) ? $_POST['content'] : null);?></textarea>
						<div class="aboutMe">
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" /></a>
							<span>Вы пишете как</span><br />
							<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
						</div>
						<label>Картинка <input type="file" name="attachment" /></label>
						<input type="submit" name="button" class="submitFanfic" value="отправить" />
					</form>
				</div>
			</div>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>