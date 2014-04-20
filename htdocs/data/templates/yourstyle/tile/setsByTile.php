<?php $this->_render(
	'inc_header',
	array(
		'title' => 'your.style - '.$d['tile']['brand'],
		'header' => 'your.style',
		'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">',
		'header_small' => '',
		'css' => 'ys.css?d=26.03.12',
		'js' => array('YourStyle.js?d=26.03.12', 'jquery/jquery.js', 'ys.js?d=26.03.12'),
        'yourstyleRating' => $d['yourStyleUserRating'],
	)
);

$groupTitle = ucfirst($d['group']['title']);
$tile = $d['tile'];
?>
<script type="text/javascript">
	ys = new YourStyle();
</script>

<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li><a href="/yourstyle/sets">сеты</a></li>
			<li class="active"><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li><a href="/yourstyle/rules">правила</a></li>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/yourstyle/groups">все вещи</a></li>
			<li><a href="/yourstyle/tiles/top">популярные</a></li>
		</ul>

		<?if (!empty($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		
		<table class="b-items-roll b-items-roll-category"><tbody> 
			<tr> 
				<td> 
					<a href="/yourstyle/tile/<?=$tile['id']?>" class="item-image"><img src="<?=$p['ys']::getWwwUploadTilesPath($tile['gid'], $tile['image'], '150x150')?>" /></a> 
				</td> 
				<td> 
					<h3><?=$groupTitle?> - <a href="/yourstyle/tile/<?=$tile['id']?>"><?=$tile['brand']?></a></h3> 
					<div class="controls"> 
						<span>Добавить:</span> 
						<a href="/yourstyle/editor/withTile/<?=$tile['id']?>">в сет</a> 
						<span class="border">|</span> 
						<?php if(!$d['isIAdd']) { ?> 
						<a href="/yourstyle/tile/<?=$tile['id'];?>/toMy" onclick="ys.tileToFromMy(event);">в мои вещи</a>
					    <?php } else { ?>
						<a href="/yourstyle/tile/<?=$tile['id'];?>/fromMy" onclick="ys.tileToFromMy(event);">убрать из моих вещей</a>
					    <?php } ?> 
					</div> 
					<div class="description">
					    <?=$tile['description']?>
					</div> 
				<?if ($d['user']) {?>
					<div class="b-user_small"> 
						<a href="/profile/<?=$d['user']['id'];?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['user']['avatara']));?>" /></a> 
						<div class="right"> 
							<a href="/profile/<?=$d['user']['id'];?>"><?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?></a> 
							<br /><span class="sub_rating"><?=$d['userRating'];?></span>
						</div>
					</div> 
 				<? } ?>
				</td> 
			</tr>
		</tbody></table>
		
	    <?if ($d['sets']) {?>
		<div class="setRoll irhPopularRelevantSets" style="border: none;">
			<div class="h3"><img class="h" src="/i/ys/replacer-popular-relevant-sets-h3.png" alt="Сеты с этой вещью" /></div>
			<ul class="setRoll">
				<?foreach ($d['sets'] as $set) {?>
					<li><a href="/yourstyle/set/<?= $set['id'] ?>" style="width:138px;"><img src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '150x150');?>" width="138" /></a></li>
				<?}?>
			</ul>
		</div>
		<?}?>		
		
		<?php if($d['pages'] > 1) { ?>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
			<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
				<?if (!isset($pi['current'])) {?>
				<a href="/yourstyle/group/<?=$d['group']['id']?>/page/<?=$pi['link']?>?color=<?=(!empty($d['color']) ? $d['color']['en'] : null)?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
				</li>
			<?}?>
			</ul>
		</div>
		<?php } ?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>