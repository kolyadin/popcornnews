<?
require_once 'data/libs/ui/YourStyle/YourStyle_Factory.php';
$this->_render('inc_header', array('title' => $d['cuser']['nick'], 'header' => 'Твоя страница', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small' => 'твой профиль'));
$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed' => 0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid' => $d['cuser']['id'], 'confirmed' => 0));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
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
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">главная</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/form">редактировать</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos">фотографии</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos/del">удаление фото</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/blacklist">черный список</a></li>
		</ul>
		<div class="content">
			<div class="irh irhPM pMessages">
				<div class="irhContainer">
					<h3>личные сообщения<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/messages" class="replacer"></a></h3>
					<span class="counter"><a rel="nofollow" href="/profile/<?=$d['user']['id']?>/messages"><?=$p['query']->get_num('user_msgs', array('private' => 1, 'uid' => $d['user']['id'], 'pid' => 0, 'del_uid' => 0))?></a></span>
				</div>
				<div class="trackContainer pMessagesTrack">
					<?foreach ($p['query']->get('private_msgs', array('uid'=>$d['user']['id'], 'pid' => 0, 'del_uid' => 0), array('id desc'), 0, 5) as $i => $msg) {?>
					<div class="trackItem" id="<?=$msg['id']?>">
						<div class="entry">
							<p><?=$this->preg_repl($p['nc']->get($msg['content']))?></p>
						</div>
						<a rel="nofollow" href="/profile/<?=$msg['aid'];?>" class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($msg['avatara']))?>" /></a>
						<div class="details">
							<a class="pc-user" rel="nofollow" href="/profile/<?=$msg['aid']?>"><?=htmlspecialchars($msg['nick'], ENT_IGNORE, 'cp1251', false);?></a>
							<span class="date"><?=$p['date']->unixtime($msg['cdate'], "%d %F %Y, %H:%i")?></span>
							<a class="reply" href="#" onclick="delete_msg(<?=$msg['id'];?>, 'private'); return false;">удалить</a>
							<?
							// id пользователя администрации
							if ($msg['aid'] != 57) {
							?>
							<a class="reply" rel="nofollow" href="/profile/<?=$d['user']['id']?>/messages/answer/<?=$msg['id']?>">ответить</a>
							<?}?>
						</div>
					</div>
					<?}?>
					<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/messages/new" class="new">Написать новое личное сообщение</a>
				</div>
			</div>

            <?php
            $this->_render('inc_guestbook');
            ?>

		</div>
		<div class="contentSidebar">
			<?
			$gift = $p['query']->get('user_gifts_send', array('uid' => $d['cuser']['id']), array('gifts.id DESC'), 0, 1);
			if (!empty($gift)) $gift = $gift[0];
			if (!empty($gift)) {
			?>
			<div class="irh irhMyGifts csbDiv">
				<div class="irhContainer">
					<h3>мои подарки<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts/recieved" class="replacer"></a></h3>
					<span class="counter"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts/recieved"><?=$p['query']->get_num('user_gifts_send', array('uid'=>$d['cuser']['id']))?></a></span>
				</div>
				<div id="gift">
					<?if ($gift['amount'] == 0) {?>
					<img src="/gifts/<?=$gift['small_pic']?>" alt="">
					<?} else {?>
					<script type="text/javascript" src="/js/swfobject.js"></script>
					<script type="text/javascript">
						var realEstate = new SWFObject('/gifts/<?=$gift['gift_pic']?>', "gift", "125", "125", "9.0.0");
						realEstate.addParam("wmode","transparent");
						realEstate.write("gift");
					</script>
					<?}?>
				</div>
			</div>
			<?
			}

			$num_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed' => 1));
			$num_facts = $p['query']->get_num('facts', array('uid'=>$d['cuser']['id']));
			$num_comments = $p['query']->get_num('comments', array('user_id'=>$d['cuser']['id']));
			$num_cups = $p['query']->get_num('winners', array('uid'=>$d['cuser']['id']));
			$num_photos = $p['query']->get_num('user_pix', array('uid'=>$d['cuser']['id'], 'moderated'=>1));
			
			$awards = '';
			if ($d['user']['activist'] > 0) $awards .= '<li class="active"><strong>' . ($d['cuser']['activist'] > 1?'x ' . $d['cuser']['activist']:'&nbsp;') . '</strong><span>Активист месяца</span></li>';
			if ($num_comments >= 1000) $awards .= '<li class="thousand"><strong>' . (floor($num_comments / 1000) > 1?'x ' . floor($num_comments / 1000):'&nbsp;') . '</strong><span>1000 комментов</span></li>';
			if ($num_photos >= 100) $awards .= '<li class="photo"><strong>' . (floor($num_photos / 100) > 1?'x ' . floor($num_photos / 100):'&nbsp;') . '</strong><span>добавил 100 фото</span></li>';
			if ($num_cups >= 10) $awards .= '<li class="cup"><strong>' . (floor($num_cups / 10) > 1?'x ' . floor($num_cups / 10):'&nbsp;') . '</strong><span>10 побед в конкурсе</span></li>';
			if ($num_facts >= 20) $awards .= '<li class="facts"><strong>' . (floor($num_facts / 20) > 1?'x ' . floor($num_facts / 20):'&nbsp;') . '</strong><span>написал 20 фактов</span></li>';
			if ($num_friends >= 50) $awards .= '<li class="friends"><strong>' . (floor($num_friends / 50) > 1?'x ' . floor($num_friends / 50):'&nbsp;') . '</strong><span>имеет 50 друзей</span></li>';

			if ($awards != '') {?>
			<div class="irh irhAwards csbDiv">
				<div class="irhContainer">
					<h3>награды<span class="replacer"></span></h3>
				</div>
				<ul class="awards">
					<?=$awards;?>
				</ul>
			</div>
			<?}?>

			<div class="irh irhMyGroups irhCustom csbDiv">
				<div class="irhContainer">
					<h3>мои группы<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups" class="replacer"></a></h3>
					<span class="counter"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups"><?=$p['query']->get_num('community_groups_members', array('muid' => $d['cuser']['id'], 'confirm' => 'y'))?></a></span>
				</div>
				<ul class="groups">
					<?foreach ($p['query']->get('community_users_groups', array('muid' => $d['cuser']['id']), array('b.createtime desc'), 0, 10, array('a.id')) as $i => $user_group) {?>
					<li>
						<div class="details">
							<a href="/community/group/<?=$user_group['id']?>"><?=$user_group['title']?></a>
							<span><?=$user_group['membersNum']?> <?=$p['declension']->get($user_group['membersNum'], 'участник', 'участника', 'участников')?></span>
						</div>
					</li>
					<?}?>
				</ul>
			</div>

			<div class="irh irhMyFriends csbDiv">
				<div class="irhContainer">
					<h3>мои друзья<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/friends" class="replacer"></a></h3>
					<span class="counter"><a rel="nofollow" href="/profile/<?=$d['user']['id']?>/friends"><?=$p['query']->get_num('user_friends_optimized', array('uid'=>$d['user']['id'], 'confirmed'=>1))?></a></span>
				</div>
				<ul class="friends equalsContainer">
					<?
					$friends = $p['query']->get('user_friends_optimized', array('uid'=>$d['user']['id'], 'confirmed'=>1), null, 0, 9);					
					$cf = count($friends);
					if ($cf > 3 && $cf < 6) $friends = array_slice($friends, 0, 3);
					if ($cf > 6 && $cf < 9) $friends = array_slice($friends, 0, 6);
					foreach ($friends as $i => $person) {
					?>
					<li><a rel="nofollow" href="/profile/<?=$person['fid']?>"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($person['avatara']))?>" /></a></li>
					<?}?>
				</ul>
				<div class="edit">
					<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends/edit">Редактировать</a>
				</div>
			</div>
			<div class="irh irhMyPhotos scbDiv">
				<div class="irhContainer">
					<?$photos = $p['query']->get_num('profile_pix', array('uid'=>$d['cuser']['id']));
					if ($photos > 0) {
						$imgi = array_shift($p['query']->get('profile_pix', array('uid'=>$d['cuser']['id']), array('cdate desc'), 0, 1));
					} else {
						$imgi['filename'] = null;
					}
					?>
					<h3>мои фотки<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/photos" class="replacer"></a></h3>
					<span class="counter"><a rel="nofollow" href="/profile/<?=$d['user']['id']?>/photos"><?=$photos?></a></span>
				</div>
				<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/photos" class="myPhoto"><img alt="" src="<?=$this->getStaticPath($this->getUserPhoto($imgi['filename']))?>" /></a>
				<div class="edit noBorder">
					<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/photos/add">Прислать фотографию</a>
				</div>
			</div>
			<div class="irh irhMyStyles scbDiv">
				<div class="irhContainer">
				<?php
				    $sets_num = $p['query']->get_num('yourstyle_sets', array('uid' => $d['cuser']['id'], 'isDraft' => 'n'));				    
				    if($sets_num > 0) {
				        $set = $p['query']->get('yourstyle_sets', array('uid'=>$d['cuser']['id'], 'isDraft' => 'n'), array('createtime desc'), 0, 1);
				    } else {
				        $set['image'] = null;
				    }
				    $set = $set[0];
				?>
				<h3>мои сеты<a rel="nofollow" href="/user/<?=$d['cuser']['id']?>/sets" class="replacer"></a></h3>
					<span class="counter"><a rel="nofollow" href="/user/<?=$d['cuser']['id']?>/sets"><?=$sets_num?></a></span>
				</div>
				<?php if($sets_num > 0) { ?>
				<a rel="nofollow" href="/yourstyle/set/<?=$set['id'];?>/" class="myPhoto"><img alt="" src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '150x150')?>" /></a>
				<?php } ?>
				<div class="edit noBorder">
					<a rel="nofollow" href="/yourstyle/editor/" target="_blank">Создать сет</a>
				</div>
			</div>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>