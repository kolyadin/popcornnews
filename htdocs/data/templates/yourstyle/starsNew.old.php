<?$this->_render('inc_header', 
    array(
    	'title' => 'your.style - Новые сеты со звездами', 
    	'header' => 'your.style', 
    	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 
    	'header_small' => '', 
    	'css' => 'ys.css', 
    	'js' => 'YourStyle.js',
        'yourstyleRating' => $d['yourStyleUserRating'],
    )
);?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li class="active"><a href="/yourstyle/stars">звезды</a></li>
			<li><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li><a href="/yourstyle/rules">правила</a></li>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/yourstyle/stars">новые</a></li>
			<li><a href="/yourstyle/stars/byName">по имени</a></li>
		</ul>
		<h2>Сеты со звездами</h2>

		<div class="b-cols b-cols-4 b-ys-content">
			<?$maxI = count($d['newStars'])-1; foreach ($d['newStars'] as $i => $star) {?>
			<?if ($i % 5 == 0) {?><div class="b-cols__col"><dl class="b-stars-list"><?}?>
					<dd><a href="/persons/<?=str_replace(' ', '-', $star['eng_name']);?>"><?=$star['name']?></a></dd>
			<?if ($i % 5 == 4 || $i == $maxI) {?></dl></div><?}?>
			<?}?>

			<div class="b-cols__col b-cols__col_final">
				<a href="/yourstyle/stars/byName">все звезды</a>
			</div>
		</div>

		<ul class="b-sets-roll b-sets-roll_full">
			<?foreach ($d['topStarsSets'] as $set) {?>
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
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>