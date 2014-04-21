<?$this->_render('inc_header', 
    array(
    	'title' => 'your.style - Сеты пользователя', 
    	'header' => 'your.style', 
    	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['user']['avatara'], true)) . '" alt="' . htmlspecialchars($d['user']['nick']) . '" class="avaProfile">', 
    	'header_small' => '', 
    	'css' => 'ys.css?d=26.03.12', 
    	'js' => 'YourStyle.js?d=26.03.12',
        'yourstyleRating' => $d['yourStyleUserRating'],
    )
);?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li class="active"><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li><a href="/yourstyle/rules">правила</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/yourstyle/sets">популярные</a></li>
			<li><a href="/yourstyle/sets/new">новые</a></li>
			<li class="active"><a href="/user/<?=$d['user']['id']?>/sets">сеты пользователя</a></li>
			<li><a href="/profile/<?=$d['cuser']['id']?>/sets">мои сеты</a></li>
		</ul>

		<h2>Сеты пользователя</h2>
		<?php if(count($d['userSets']) > 0) { ?>
		<ul class="b-sets-roll b-sets-roll_full">
			<?foreach ($d['userSets'] as $set) {?>
			<li class="b-sets-roll__set">
				<a class="set-image" href="/yourstyle/set/<?=$set['id']?>"><img src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '274x274')?>" /></a>
				<h2><a href="/yourstyle/set/<?=$set['id']?>"><?=$set['title']?></a></h2>
				<div class="b-meta">
					<span class="votes-count"><?=$set['votes']?> <?=$p['declension']->get($set['votes'], 'голос', 'голоса', 'голосов')?></span>
					<a class="comments-count" href="/yourstyle/set/<?=$set['id']?>#comments"><?=$set['comments']?> <?=$p['declension']->get($set['comments'], 'комментарий', 'комментария', 'комментариев')?></a>,
					<a class="username" href="/profile/<?=$set['uid']?>"><?=$set['unick']?></a>
					<span class="sub_rating"><?=$set['urating']?></span>
				</div>
			</li>
			<?}?>
		</ul>
		<?php } else { ?>
		<h3>Нет сетов</h3>
		<?php } ?>		
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>