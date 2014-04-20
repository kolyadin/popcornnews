<?
if (!is_numeric($d['page'])) $d['page'] = 1;
$per_page = 12;

$num = $p['query']->get_num('news_with_tags', array('cdate_gt'=>'0000-00-00'));
$news = $d['news'];
$per_page = $d['per_page'];
$templateParams = array(
		'header' => $p['date']->parse($news[0]['cdate'], '%F <strong>%Y</strong>'),
		'top_code' => $p['date']->parse($news[0]['cdate'], '%d'),
);
$pages = ceil($num / $per_page);

if($d['page'] + 1 <= $pages) {
 	$templateParams['chrome_next'] = '/page/'.($d['page'] + 1);
}

$this->_render(
	'inc_header',
 	$templateParams
);
//var_dump($news[0]);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<div class="newsTrack">
			<?
			$cd = $news[0]['cdate'];
			foreach ($news as $i => $new) {
			    $persons = $p['query']->get('persons', array('ids'=>$new['ids_persons']));
				$events = $p['query']->get('events', array('ids'=>$new['ids_events']));					
			    $isMashaBlog = in_array($_SERVER['blogID'], explode(',',$new['ids_events']));
			    $blogTag = $p['query']->get('events', array('ids' => $_SERVER['blogID']));
			    $blogTag = $blogTag[0];
				if ($new['cdate'] != $cd) {
			?>
			<div class="pastHeadline">

				<h2>
					<span class="date"><?=$p['date']->parse($new['cdate'], '%d')?></span>
					<?=$p['date']->parse($new['cdate'], '%F <strong>%Y</strong>')?>
				</h2>
			</div>
			
			<?
					$cd = $new['cdate'];
				}
			?>
			<div class="trackItem">
				<?php if($isMashaBlog) { ?>
				<div class="tagIcon">
					<div class="tagLink"><?=$blogTag['name'];?></div>
					<h3><a href="/news/<?=$new['id']?>"><?=$new['name']?></a></h3>
				</div>
				<?php } else { ?>
				<h3><a href="/news/<?=$new['id']?>"><?=$new['name']?></a></h3>
				<?php } ?>
				<div class="imagesContainer">
					<?if (!empty($new['main_photo'])) {?>
					<a href="/news/<?=$new['id']?>"><img src="<?=$this->getStaticPath('/upload/_500_600_80_' . $new['main_photo'])?>" alt="<?=$new['name']?>" /></a>
					<?}?>
				</div>
				<div class="entry">
					<?=$new['anounce']?>
					<a href="/news/<?=$new['id']?>" class="more_new">Читать дальше</a>
				</div>
				<div class="newsMeta">
					<span class="comments"><a href="/news/<?=$new['id']?>#comments">Комментариев: <?=RoomFactory::load('news-'.$new['id'])
                                ->getCount();?></a></span>
					<span class="views">Просмотров <? echo intval($new['views']); ?></span><br />
					<span class="tags">Теги:
					<?					
					foreach ($persons as $i => $person) {
					    $link = $person['eng_name'];
					    $link = str_replace('-', '_', $link);
					    $link = str_replace('&dash;', '_', $link);
					    $link = '/persons/'.str_replace(' ', '-', $link);					    	
					?>
					<a href="<?=$link;?>"><?=$person['name']?></a><?if ($i < count($persons) - 1) {?>,<?}?>
					<?}?>
					<?if (!empty($persons) && !empty($events)) {?>,<?};?>
					<?foreach ($events as $i => $event) {?>
					<a href="/event/<?=$event['id']?>"><?=$event['name']?></a><?if ($i < count($events) - 1) {?>,<?}?>
					<?}?>
					</span>
				</div>
			</div>
			<?
			}
			?>
		</div>
	</div>
<?$this->_render('inc_right_column');?>
</div>
<?
// страниц, а не результатов на страницу
if ($num > $per_page) {?>
<div>
	<div class="paginator smaller">
		<p class="pages">Страницы:</p>
		<ul style="display: block; float: none;">
			<?if ($d['page'] != 1) {?><li><a href="/page/<?=($d['page'] != $num ? $d['page'] - 1 : $num);?>">Предыдущая</a></li><?}?>
			<li><a href="/page/<?=($d['page'] != 1 ? $d['page'] + 1 : 2);?>">Следующая</a></li>
		</ul>
		<ul style="display: block; clear: both;">
			<?=($p['pager']->cat_pages($d['page'], $num, 2, '/page/', 12));?>
			<li class="arch"><a href="/archive" class="archive">архив новостей</a></li>
		</ul>
	</div>
</div>
	<?
}
?>
<?$this->_render('inc_footer');?>