<?
$this->_render('inc_header', array('title' => 'your.style - ' . $d['brand']['title'], 'header' => 'your.style', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 'header_small' => '', 
        'css' => 'ys.css?d=26.03.12', 'js' => array('YourStyle.js?d=26.03.12', 'rating.js?d=26.03.12'), 'yourstyleRating' => $d['yourStyleUserRating']));

$brand = $d['brand'];

?>
<div id="contentWrapper" class="twoCols">

	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li class="active"><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li><a href="/yourstyle/rules">правила</a></li>
		</ul>
		<ul class="menu bLevel">
			<li><a href="/yourstyle/brands">все бренды</a></li>
			<li><a href="/yourstyle/brands/top">популярные</a></li>
		</ul>

		<?php if(!is_null($brand['logo'])) { ?>
		<div class="left_block1">
			<img src="<?=$brand['logo'];?>" alt="<?=$brand['title'];?>" />
			<br />
			<br />
			<?php /* ?>
			<div class="b-ys-item ver2">
				<!-- Rating -->
				<div id="_7699" class="sub_rating allow_vote">
					<h3>
						рейтинг <span class="num">5</span>
					</h3>
					<span class="vote"> <span class="stars" style="width: 100px;"></span>
						<a class="star _1" href="#">1</a> <a class="star _2" href="#">2</a>
						<a class="star _3" href="#">3</a> <a class="star _4" href="#">4</a>
						<a class="star _5" href="#">5</a>
					</span> <span class="assessment">ваша<br />оценка
					</span>
				</div>
				<script type="text/javascript">new Rating({id:'_7699', ajax:''});</script>
				<!-- / Rating -->
			</div>
            <?php */ ?>
		</div>
		<div class="right_block1">
		<?php } else { ?>
		<div class="right_block1" style="width:100%;">		
		<?php } ?>		
			<h2><?=$brand['title'];?></h2>
			<?=$brand['descr'];?>
			<br />
			<p>
			    <?php if(count($brand['tags']) > 0) { ?>			    
				<span class="grey">Теги: </span>
				<?php $tags = array(); ?>
				<?php foreach($brand['tags'] as $tag) {
				        $link = "/yourstyle/tiles/?rootGroup={$tag['rgid']}&group={$tag['id']}&brand={$brand['id']}&color=0";
				        $tags[] = '<a href="'.$link.'" title="'.$tag['title'].'">'.$tag['title'].'</a>';    
				    }
				    $tags = implode(', ', $tags);
				    echo $tags;
                }
				?> 
			</p>
		</div>
		<div class="clear"></div>
		<br /><br /><br /><br />
		<?php if($brand['tiles_count'] > 0) { ?>
		<div class="div1 ver2">
			<a href="/yourstyle/tiles?brand=<?=$brand['id'];?>" class="title1">вещи бренда</a>
			<span class="counter ver2"><a
				href="/yourstyle/tiles?brand=<?=$brand['id'];?>"><?=$brand['tiles_count'];?></a></span>
			<div class="clear"></div>
			<?php foreach ($brand['tiles'] as $tile) { ?>
			<a class="a1" href="/yourstyle/tile/<?=$tile['id'];?>"><img src="<?=$tile['image'];?>" /></a>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if($brand['sets_count'] > 0) { ?>
		<div class="setRoll irhPopularRelevantSets ver2">
			<a href="/yourstyle/brands/<?=$brand['id'];?>/sets" class="title1">сеты с этим брендом</a>
			<span class="counter ver2"><a href="/yourstyle/brands/<?=$brand['id'];?>/sets"><?=$brand['sets_count'];?></a></span>
			<div class="clear"></div>
			<ul class="setRoll">
			<?php foreach($brand['sets'] as $set) { 
			?>
				<li>
				    <a href="/yourstyle/set/<?=$set['id'];?>" style="width: 138px;">
				    <img src="<?=$set['image'];?>" width="138" /></a>
				</li>
			<?php } ?>
			</ul>
		</div>
		<?php } ?>
		<br />
		<br />
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>