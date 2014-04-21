<? $this->_render(
	'inc_header',
	array(
		'title' => 'your.style - '.$d['group']['title'],
		'header' => 'your.style',
		'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">',
		'header_small' => '',
		'css' => 'ys.css?d=26.03.12',
		'js' => array('YourStyle.js?d=26.03.12', 'jquery/jquery.js', 'ys.js?d=26.03.12'),
        'yourstyleRating' => $d['yourStyleUserRating'],
	)
);

//var_dump($d['group']);

$groupTitle = ucfirst($d['group']['title']);
$filters = array();
$filters['rootGroup'] = $d['group']['rgid'];
$filters['group'] = $d['group']['id'];

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
		<h2><?=$groupTitle;?></h2>
		<?php $this->_render('filterPanel', array('rootGroups' => $d['rootGroups'], 'groups' => $d['groups'], 'brands' => $d['brands'], 'colors' => $d['colors'], 'filters' => $filters)); ?>
		<!--div class="b-color-chooser"> 
			<span class="b-color-chooser__header"><?=(!empty($d['color']) ? $d['color']['ru'] : 'Любого цвета')?></span> 
			<span class="b-color-chooser__chosen"><i style="background: <?=(!empty($d['color']) ? $d['color']['rgb'] : '#fff')?>;"></i></span> 
			<a class="b-color-chooser__chooser"></a> 
				
			<div class="b-color-chooser__list"> 
				<div class="b-deco"> 
					<ul> 
						<?foreach ($d['colors'] as $rgb => $color) {?>
						<li><a href="/yourstyle/group/<?=$d['group']['id']?>?color=<?=$color['en']?>" style="background-color: <?=$rgb?>;"></a></li> 
						<?}?>
					</ul> 
					<a href="/yourstyle/group/<?=$d['group']['id']?>">все цвета</a> 
				</div> 
			</div> 
		</div-->
				
		<?php $i = 0; ?>
        <table class="b-items-roll b-items-roll-category"><tbody>
        <?php foreach ($d['tiles'] as $tile) { ?>
            <?php if(($i % 3) == 0) { ?>
            <tr>
            <?php } ?>
                <td style="width:33%;">
                    <a href="/yourstyle/tile/<?=$tile['id']?>" class="item-image"><img src="<?=$p['ys']::getWwwUploadTilesPath($tile['gid'], $tile['image'], '150x150')?>"></a>
                    <h3><a href="/yourstyle/tile/<?=$tile['id']?>"><?=$tile['brand']?></a></h3>
                    <div class="controls">
                        <span>Добавить:</span>
                        <a href="/yourstyle/editor/withTile/<?=$tile['id']?>">в сет</a>
                        <span class="border">|</span>
                        <a onclick="ys.tilesToFromMy(event);" href="/yourstyle/tile/<?=$tile['id']?>/toMy">в мои вещи</a>
                    </div>
                </td>
            <?php if(($i % 3) == 2) { ?>
            <tr>
            <?php } $i++; ?>
        <?php } ?>
        </tbody></table>	    
								
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
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>