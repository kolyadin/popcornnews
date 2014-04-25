<?
$this->_render('inc_header', array('title'=>$d['user']['nick'], 'header'=>htmlspecialchars($d['user']['nick']), 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['user']['avatara'], true)) . '" alt="' . htmlspecialchars($d['user']['nick']) . '" class="avaProfile">', 'header_small'=>'Подарки пользователя'));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<?$rating = $p['rating']->_class($d['user']['rating']);?>
			<li class="rating">
				<div class="userRating <?=$rating['class']?>">
					<div class="rating <?=$rating['stars']?>"></div>
					<span><?=$d['user']['rating']?> <?=$rating['name']?> <?=$d['online'] ? 'Онлайн' :''?></span>
				</div>
			</li>
			<li><a rel="nofollow" href="/profile/<?=$d['user']['id']?>">профиль</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/friends">друзья</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/persons">персоны</a></li>
			<?if ($p['query']->get_num('profile_pix', array('uid'=>$d['user']['id'])) > 0) {?>
			<li><a href="/user/<?=$d['user']['id']?>/photos">фотографии</a></li>
			<?}?>
			<li><a href="/user/<?=$d['user']['id']?>/guestbook">гостевая</a></li>
			<li class="active"><a href="/user/<?=$d['user']['id']?>/gifts">подарки</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/wrote">пишет</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/community/groups">группы</a></li>
			<li><a href="/user/<?=$d['user']['id']?>/sets">your.style</a></li>
		</ul>
		<h2>Подарки <?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?></h2>
		<div class="gift-list">
			<script type="text/javascript" src="/js/swfobject.js"></script>
			<?if (!empty($d['gifts'])) {?>
			<table class="send">
				<?foreach ($d['gifts'] as $gift) {?>
				<tr>
					<td class="gift" id="gift_<?=$gift['id']?>">
						<?if (substr($gift['gift_pic'], - 3) == 'jpg') {?>
						<img alt="" src="/gifts/<?=$gift['small_pic']?>" />
						<?} else {?>
						<script type="text/javascript">
							var realEstate = new SWFObject('/gifts/<?=$gift['gift_pic']?>', "gift_<?=$gift['id']?>", "125", "125", "9.0.0");
							realEstate.addParam("wmode","transparent");
							realEstate.write("gift_<?=$gift['id']?>");
						</script>
						<?}?>
					</td>
					<td><a rel="nofollow" href="/profile/<?=$gift['user_id']?>/"><?=htmlspecialchars($gift['nick'], ENT_IGNORE, 'cp1251', false);?></a></td>
					<td><?=$p['date']->unixtime($gift['send_date'], '%d %F %Y, %H:%i')?></td>
					<td><?=($gift['amount'] == 0 ? 'Бесплатно' : $gift['amount'])?></td>
				</tr>
				<?}?>
			</table>
			<?} else {?>
			<h4 class="no-gifts">
					<?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?> все ещё без подарков! Отправь ему подарок прямо сейчас!<br />
				<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts/<?=$d['user']['id']?>">Отправить</a> подарок
			</h4>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>