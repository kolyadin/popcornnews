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
			<li><a href="/yourstyle/tiles/top">попул€рные</a></li>
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
						<span>ƒобавить:</span> 
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
		
		<?php if(count($d['users']) > 0) { ?>
		<div class="div1">
			<div class="h3"><img class="h" src="/i/ys/added-wardrobe-h3.png" alt="ƒобавили в гардероб" /></div>
			<table class="table1"><tbody>
			<?php $i = 1; ?>
			<?php
			foreach ($d['users'] as $usr) { ?>
				<?php if($i % 4 == 0) { ?>
					<tr>
				<?php } ?>
				<td> 
					<div class="b-user_small"> 
						<a href="/profile/<?=$usr['id'];?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($usr['avatara'], true));?>" width="60"></a> 
						<div class="right"> 
							<a href="/profile/<?=$usr['id'];?>" style="font-size:11px;"><?=$usr['nick'];?></a>
							<br /><span class="sub_rating"><?=$usr['rating'];?></span>
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
		<? } ?>
		
		<?php if($d['pages'] > 1) { ?>
		<div class="paginator smaller">
			<p class="pages">—траницы:</p>
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