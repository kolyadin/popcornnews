<?
$this->_render('inc_header', 
    array(
    	'title' => 'your.style - √руппы', 
    	'header' => 'your.style', 
    	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 
    	'header_small' => '', 
    	'css' => 'ys.css?d=26.03.12', 
    	'js' => 'YourStyle.js?d=26.03.12',
        'yourstyleRating' => $d['yourStyleUserRating'],
    )
);

$rootGroup = ucfirst($d['rootGroup']['title']);

$filters = array();
$filters['rootGroup'] = $d['rootGroup']['id'];

?>
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

		<h2><?=$rootGroup;?></h2>
		<?php $this->_render('filterPanel', array('rootGroups' => $d['rootGroups'], 'groups' => $d['groups'], 'brands' => $d['brands'], 'colors' => $d['colors'], 'filters' => $filters)); ?>
		<div class="b-items-roll">
			<ul class="ys-canvas__stuff">
				<?foreach ($d['groups'] as $group) {?>
				<li>
					<a class="image" href="/yourstyle/group/<?=$group['id']?>"><img src="<?=$p['ys']::getWwwUploadTilesPath($group['tile']['gid'], $group['tile']['image'], '100x100')?>" /></a>
					<a href="/yourstyle/group/<?=$group['id']?>"><?=$group['title']?></a>
				</li>
				<?}?>
			</ul>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>