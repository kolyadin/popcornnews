<?
$this->_render('inc_header', 
array(
	'title' => 'your.style - Вещи - '.$d['tile']['brand'], 
	'header' => 'your.style', 
	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 
	'header_small' => '', 
	'css' => 'ys.css?d=26.03.12', 
	'js' => array('YourStyle.js?d=26.03.12', 'rating.js?d=26.03.12'),
    'yourstyleRating' => $d['yourStyleUserRating'],
));

$groupTitle = ucfirst($d['group']['title']);

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
			<li><a href="/yourstyle/groups">все вещи</a></li>
			<li><a href="/yourstyle/tiles/top">популярные</a></li>
		</ul>

		<h2><?=$groupTitle;?> - <?=$d['tile']['brand'];?></h2>

		<?php /* new item info */ ?>
		
		<div class="b-ys-item">
			<div class="b-ys-item__about-item"> 
				<div class="b-info"> 
					<?php if(!empty($d['tile']['price'])) { ?>
					<span class="cost"><?=$d['tile']['price'];?> <span><?=$p['declension']->get(intval($d['tile']['price']), 'рубль', 'рубля', 'рублей');?></span></span>
					<?php } ?> 
					<h3><?=$d['tile']['brand'];?></h3> 
					<?php if(!empty($d['brand']['logo'])) { ?>
					<img class="brand" src="<?=$p['ys']::getWwwUploadBrandsPath($d['brand']['id'], $d['brand']['logo'], '140x140');?>" alt="<?=$d['tile']['brand'];?>" />
					<?php } ?> 
					<div class="color">
					    <?php if(count($d['colors']) > 0) { ?>
					    <div class="color_title">Цвет:</div>
					    <div class="color_content">
					        <?php
					        foreach($d['colors'] as $c) {
					            $filter_link = "/yourstyle/tiles/?rootGroup=0&brand={$d['brand']['id']}&color=".YourStyle_BackEnd::$humanColors[$c['color']]['en'];
					            echo '<a href="'.$filter_link.'" title="'.YourStyle_BackEnd::$humanColors[$c['color']]['ru'].'" class="color_block" style="background: '.$c['color'].';"></a>';
					        }
					        ?>
					    </div>
					    <?php } else { ?>
					    <div class="color_title"> </div>
					    <?php } ?>
					</div>
				</div> 
				<!-- Rating --> 
					<?
					if($d['votesCount'] != 0) {
					    $rate = $d['tile']['rate'] / $d['votesCount'];
					}
					else {
					    $rate = 0;
					}
					$rate = round($rate, 1);					
					$width = $rate * 20;
					$rate = str_replace('.', ',', $rate);
					?>
				<div id="_<?=$d['tile']['id']?>" class="sub_rating<?=($d['isILike'])?'':' allow_vote'?>"> 
					<h3>рейтинг <span class="num"><?=$rate;?></span></h3> 
					<span class="vote"> 
						<span class="stars" style="width:<?=$width;?>px;"></span> 
						<a class="star _1" href="#">1</a> 
						<a class="star _2" href="#">2</a> 
						<a class="star _3" href="#">3</a> 
						<a class="star _4" href="#">4</a> 
						<a class="star _5" href="#">5</a> 
					</span> 
					<span class="assessment">ваша<br  />оценка</span> 
				</div> 
				<script type="text/javascript">new Rating({id:'_<?=$d['tile']['id']?>', ajax:'/yourstyle/tile/<?=$d['tile']['id'];?>/like/'});</script> 	
				<!-- / Rating --> 
				<div class="b-ys-item__uactivity"> 					
					<div class="hr _2"></div> 
					<h3><?=$d['usersAdded'];?> <?=$p['declension']->get($d['usersAdded'], 'пользователь', 'пользователя', 'пользователей');?></h3> 
					<p><?=$p['declension']->get($d['usersAdded'], 'добавил', 'добавили', 'добавили');?> в свои вещи</p>
					<?php if(!$d['isIAdd']) { ?> 
					<a href="/yourstyle/tile/<?=$d['tile']['id'];?>/toMy" onclick="ys.tileToFromMy(event);">добавить</a>
					<?php } else { ?>
					<a href="/yourstyle/tile/<?=$d['tile']['id'];?>/fromMy" onclick="ys.tileToFromMy(event);">убрать</a>
					<?php } ?> 
				</div> 
				<br /> 
				<div class="b-meta"> 
				<?if ($d['user']) {?>
					<div class="b-user_small"> 
						<a href="/profile/<?=$d['user']['id'];?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($d['user']['avatara']));?>" /></a> 
						<div class="right"> 
							<a href="/profile/<?=$d['user']['id'];?>"><?=htmlspecialchars($d['user']['nick'], ENT_IGNORE, 'cp1251', false);?></a>
							<br />
							<span class="sub_rating"><?=$d['userRating']?></span>
						</div>
					</div> 
 				<? } ?>
					<span class="b-date">Создано <?=$p['date']->unixtime($d['tile']['createtime'], '%d %F %Y, %H:%i')?></span> 
				</div> 
			</div> 
			<div class="b-ys-item__image"><img src="<?=$p['ys']::getWwwUploadTilesPath($d['tile']['gid'], $d['tile']['image'], '300x300');?>" /></div> 
			
			
		</div>
				
		<?php if($this->isModerYS()) { ?>
		<script type="text/javascript">
		function moveToBrand() {
			var brand = document.getElementById('edit_brand').value;
			var url = '/yourstyle/tile/<?=$d['tile']['id'];?>/edit?brand=' + brand;
			location.href = url;
		}
		function moveToGroup() {
			var group = document.getElementById('edit_group').value;
			var url = '/yourstyle/tile/<?=$d['tile']['id'];?>/edit?group=' + group;
			location.href = url;
		}
		</script>		
		<div class="setRoll">
		    <h3>Управление</h3>
			<dl style="font-size: 14px;">
			    <dt><a href="/yourstyle/tile/<?=$d['tile']['id'];?>/delete" onclick="return confirm('Удалить?');">удалить вещь</a></dt>
			    <dt><a href="/yourstyle/tile/<?=$d['tile']['id'];?>/hide" onclick="return confirm('Скрыть?');" style="font-size: 14px;">скрыть вещь</a></dt>
			    <dt>Бренд: <select id="edit_brand">
			    <?php foreach ($d['brands'] as $brand) { ?>
			        <option value="<?=$brand['id'];?>"<?=($brand['id'] == $d['tile']['bid']?' selected="selceted"':'');?>><?=$brand['title'];?></option>
			    <?php } ?>
			    </select> <a href="#" onclick="moveToBrand(); return false;">перенести</a></dt>
			    <dt>Группа: <select id="edit_group">
			    <?php foreach($d['groups'] as $rootGroup) { ?>
			        <optgroup label="<?=$rootGroup['title'];?>">
			        <?php foreach ($rootGroup['groups'] as $group) { ?>
			            <option value="<?=$group['id'];?>"<?=($group['id'] == $d['tile']['gid']?' selected="selceted"':'');?>><?=$group['title'];?></option>
			        <?php }?>
			        </optgroup>
			    <?php }?>
			    </select> <a href="#" onclick="moveToGroup(); return false;">перенести</a></dt>
			</dl>
		</div><br />
		<?php } ?>
		
		<?if ($d['topSets']) {?>
		<div class="setRoll irhPopularRelevantSets">
			<div class="h3"><a href="/yourstyle/setsbytile/<?=$d['tile']['id'];?>" rel="nofollow"><img class="h" src="/i/ys/replacer-popular-relevant-sets-h3.png" alt="Сеты с этой вещью" /><span class="num"><?=$d['setsCount'];?></span></a></div>
			<ul class="setRoll">
				<?foreach ($d['topSets'] as $set) {?>
					<li><a href="/yourstyle/set/<?= $set['id'] ?>" style="width:138px;"><img src="<?=$p['ys']::getWwwUploadSetPath($set['id'], $set['image'], '150x150');?>" width="138" /></a></li>
				<?}?>
			</ul>
		</div>
		<?}?>		
		<?
		/*users added*/		
		if(count($d['users']) > 0) { ?>
		<div class="div1">
			<div class="h3"><a href="/yourstyle/usersbytile/<?=$d['tile']['id'];?>" rel="nofollow"><img class="h" src="/i/ys/added-wardrobe-h3.png" alt="Добавили в гардероб" /><span class="num"><?=count($d['users']);?></span></a></div>
			<table class="table1"><tbody>
			<?php $i = 1; ?>
			<?php
			$cnt = 0;
			foreach ($d['users'] as $usr) {
			    if($cnt >= 4) break; $cnt++;?>
				<?php if($i % 4 == 0) { ?>
					<tr>
				<?php } ?>
				<td> 
					<div class="b-user_small"> 
						<a href="/profile/<?=$usr['id'];?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($usr['avatara']));?>"></a> 
						<div class="right"> 
							<a href="/profile/<?=$usr['id'];?>" style="font-size:11px;"><?=$usr['nick'];?></a><br />
							<span class="sub_rating"><?=$usr['rating']?></span>
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
		<?
		/*other tiles*/
		if(count($d['otherTiles']) > 0) {
		?>
		<div class="div1"> 
			<div class="h3"><a href="/yourstyle/tiles/?brand=<?=$d['tile']['bid'];?>"><img class="h" src="/i/ys/goods_brand-h3.png" alt="Товары этого бренда" /><span class="num"><?=count($d['otherTiles']);?></span></a></div> 
			<?
			$cnt = 0;
			foreach ($d['otherTiles'] as $tile) {
			    if($cnt >= 12) break; $cnt++;
			?>
			<a class="a1" href="/yourstyle/tile/<?=$tile['id'];?>"><img src="<?=$p['ys']::getWwwUploadTilesPath($tile['gid'], $tile['image'], '70x70');?>" alt="" /></a>
			<? } ?>
		</div>
		<? } ?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>