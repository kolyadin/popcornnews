<? $this->_render(
	'inc_header',
	array(
		'title' => 'your.style - Вещи',
		'header' => 'your.style',
		'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">',
		'header_small' => '',
		'css' => 'ys.css?d=26.03.12',
		'js' => array('YourStyle.js?d=26.03.12', 'jquery/jquery.js', 'ys.js?d=26.03.12'),
        'yourstyleRating' => $d['yourStyleUserRating'],
	)
);

$f = $d['filters'];
$filters = array();
if(isset($f['bid'])) $filters['brand'] = $f['bid'];
if(isset($f['rgid'])) $filters['rootGroup'] = $f['rgid'];
if(isset($f['gid'])) $filters['group'] = $f['gid'];
if(isset($f['color'])) $filters['color'] = $f['color'];
//$clr = isset($f['color']) ? $f['color'] : '';

//$withoutColors = $filters;
//if(isset($withoutColors['color'])) unset($withoutColors['color']);

//$filters = http_build_query($filters);
//$withoutColors = http_build_query($withoutColors);

//$groupTitle = ucfirst($d['group']['title']);
$title = 'Вещи';

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

		<?if (!empty($d['error'])) {?><h4><?=$d['error']?></h4><?}?>
		<h2><?=$title;?></h2>
		<?php 
		$this->_render('filterPanel', array('rootGroups' => $d['rootGroups'], 'groups' => $d['groups'], 'brands' => $d['brands'], 'colors' => $d['colors'], 'filters' => $filters));
		if(count($d['tiles']) > 0) {
		    $i = 0;
	    ?>
        <table class="b-items-roll b-items-roll-category"><tbody>
        <?php foreach ($d['tiles'] as $tile) { ?>
            <?php if(($i % 3) == 0) { ?>
            <tr>
            <?php } ?>
                <td style="width:33%;">
                    <a href="/yourstyle/tile/<?=$tile['id']?>" class="item-image"><img src="<?=$p['ys']::getWwwUploadTilesPath($tile['gid'], $tile['image'], '150x150')?>"></a>
                    <h3><a href="/yourstyle/tile/<?=$tile['id']?>"><?=ucfirst($tile['groupTitle']);?> - <?=$tile['brand']?></a></h3>
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
		<?php } else { ?>
		<h4>Ничего не найдено</h4>
		<?php } ?>
		<?php if($d['pages'] > 1) { ?>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
			<?
			$filters = http_build_query($filters);
			foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
				<?if (!isset($pi['current'])) {?>
				<? if($pi['link'] == 1) { ?>
				<a href="/yourstyle/tiles/?<?=$filters;?>"><?=$pi['text']?></a>
				<? } else {?>
				<a href="/yourstyle/tiles/page/<?=$pi['link']?>/?<?=$filters;?>"><?=$pi['text']?></a>				
				<?}} else {?>
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