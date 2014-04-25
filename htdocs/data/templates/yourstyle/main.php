<?$this->_render('inc_header', 
    array(
    	'title' => 'your.style', 
    	'header' => 'your.style', 
    	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 
    	'header_small' => '', 
    	'css' => 'ys.css?d=26.03.12', 
    	'js' => 'YourStyle.js?d=26.03.12',
        'yourstyleRating' => $d['yourStyleUserRating'],
    ));    
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a href="/yourstyle">your.style</a></li>
			<li><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
		</ul>

		<div class="twoCols b-sets-intro">
			<div class="left">
				<div class="irh irhContainer irhPopularSets">
					<h3><a class="replacer" href="/yourstyle/sets"></a>Популярные сеты</h3>
				</div>
				<?if (!empty($d['topSets'])) {?>
				<?$theFirstSet = array_shift($d['topSets']);?>
				<ul class="b-sets-roll b-sets-roll_full">
					<li class="b-sets-roll__set">
						<a class="set-image" href="/yourstyle/set/<?=$theFirstSet['id']?>"><img src="<?=$p['ys']::getWwwUploadSetPath($theFirstSet['id'], $theFirstSet['image'], '274x274')?>" /></a>
						<h2><a href="/yourstyle/set/<?=$theFirstSet['id']?>"><?=$theFirstSet['title']?></a></h2>
						<div class="b-meta">
							<span class="votes-count"><?=$theFirstSet['rating']?> <?=$p['declension']->get($theFirstSet['rating'], 'голос', 'голоса', 'голосов')?></span>
							<a class="comments-count" href="/yourstyle/set/<?=$theFirstSet['id']?>#comments"><?=$theFirstSet['comments']?> <?=$p['declension']->get($theFirstSet['comments'], 'комментарий', 'комментария', 'комментариев')?></a>,
							<a class="username" href="/profile/<?=$theFirstSet['uid']?>"><?=$theFirstSet['unick']?></a>
							<span class="sub_rating"><?=$theFirstSet['urating']?></span>
						</div>
					</li>
				</ul>
				<ul class="b-sets-roll ys-canvas__sets">
				<?foreach ($d['topSets'] as $set) {?>
					<li class="ys-canvas__sets_set">
						<a class="set-image" href="/yourstyle/set/<?=$set['id']?>"><img src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '110x110')?>" /></a>
						<h2 class="ys-canvas_sets_set_name"><a href="/yourstyle/set/<?=$set['id']?>"><?=$set['title']?></a></h2>
						<div class="b-meta">
							<span class="votes-count"><?=$set['votes']?> <?=$p['declension']->get($set['votes'], 'голос', 'голоса', 'голосов')?></span>
							<a class="comments-count" href="/yourstyle/set/<?=$set['id']?>#comments"><?=$set['comments']?> <?=$p['declension']->get($set['comments'], 'комментарий', 'комментария', 'комментариев')?></a>,
							<a class="username" href="/profile/<?=$set['uid']?>"><?=$set['unick']?></a>
							<span class="sub_rating"><?=$set['urating']?></span>
						</div>
					</li>
				<?}?>
				</ul>
				<?}?>
			</div>
			<div class="right">
				<?/*
				<div class="irh irhContainer irhMail">
					<h3><a class="replacer" href="#"></a>Личные сообщения</h3>
				</div>
				*/?>
				<div class="b-create-set-promo"> 
					<a href="/yourstyle/editor" class="newWindow" target="_blank">создать сет</a> 
				</div>

				<div class="irh irhContainer irhStarSet">
					<h3><a class="replacer" href="/yourstyle/stars"></a>Звездные сеты</h3>
				</div>
				<div class="b-cols b-cols-2 b-ys-content b-starsets">
					<?foreach ($d['newStarsBy2Coll'] as $stars) {?>
					<div class="b-cols__col">
						<dl class="b-stars-list">
							<?foreach ($stars as $star) {
							    $link = $star['eng_name'];
							    $link = str_replace('-', '_', $link);
							    $link = str_replace('&dash;', '_', $link);
							    $link = '/persons/'.str_replace(' ', '-', $link).'/sets';
							?>
							<dd><a href="<?=$link;?>"><?=$star['name']?></a></dd>
							<?}?>
						</dl>
					</div>
					<?}?>
					<div class="b-cols__col b-cols__col_final">
						<a href="/yourstyle/stars/byName">все звезды</a>
					</div>
				</div>

				<div class="irh irhContainer irhPopularItems">
					<h3><a class="replacer" href="/yourstyle/tiles/top"></a>Популярные вещи</h3>
				</div>
				<ul class="ys-canvas__stuff">
					<?foreach ($d['topTiles'] as $tile) {?>
					<li><a href="/yourstyle/tile/<?=$tile['id']?>"><img src="<?=$p['ys']::getWwwUploadTilesPath($tile['gid'], $tile['image'], '110x110')?>" /></a></li> 
					<?}?>
				</ul>
			</div>
		</div>

		<div class="setRoll irhNewSets">
			<div class="irh irhContainer">
				<h3>новые сеты<a rel="nofollow" href="/yourstyle/sets/new" class="replacer"></a></h3>
			</div>
			<ul class="setRoll">
				<?foreach ($d['newSets'] as $set) {?>
				<li><a href="/yourstyle/set/<?=$set['id']?>"><img src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '110x110')?>" /></a></li>
				<?}?>
			</ul>
		</div>
		<div class="div1" style="border-bottom:none;"> 
			<div class="h3"><a href="#"><img class="h" src="/i/ys/active_users-h3.png" alt="активные пользователи" /></a></div>
			<table class="table1"><tbody>
			<?php $i = 0; ?>
			<?php foreach ($d['activeUsers'] as $user) { ?>
				<?php if($i % 4 == 0) { ?>
					<tr>
				<?php } ?>
				<td> 
					<div class="b-user_small"> 
						<a href="/profile/<?=$user['id'];?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']));?>"></a> 
						<div class="right"> 
							<a href="/profile/<?=$user['id'];?>"><?=$user['nick'];?></a>
							<span class="sub_rating"><?=$user['rating'];?></span>
						</div> 
					</div>					
				</td>
				<?php if($i % 4 == 3) { ?>
					</tr>
				<?php }
				$i++;
				?>
			<?php } ?>
			</tbody></table> 
		</div> 
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>