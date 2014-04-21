<?
// страницы
$per_page = 10;

$column = $d['column'];
$news = $d['news'];
$num = $d['news_count'];

setcookie('last_category', $column['id'], 0, '/');

$this->_render('inc_header', array('title' => $column['title'], 'header' => $column['title'], 'top_code' => '*', 'header_small' => ''));

?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<div class="newsTrack">
						<? foreach ($news as $i => $new) {
						    $persons = $p['query']->get('persons', array('ids'=>$new['ids_persons']));
				            $events = $p['query']->get('events', array('ids'=>$new['ids_events']));					
			                $isMashaBlog = in_array($_SERVER['blogID'], explode(',',$new['ids_events']));
			                $blogTag = $p['query']->get('events', array('ids' => $_SERVER['blogID']));
			                $blogTag = $blogTag[0]; ?>
						<div class="trackItem">
							<?php if($isMashaBlog) { ?>
							<div class="tagIcon">
								<div class="tagLink"><?=$blogTag['name'];?></div>
								<h3><a href="/news/<?=$new['id']?>"><?=$new['name']?></a><br />
								<span class="date"><?=$p['date']->parse($new['cdate'], '%d %F %Y');?></span>
								</h3>
							</div>
				            <?php } else { ?>
							<h3><a href="/news/<?=$new['id']?>"><?=$new['name']?></a><br />
							<span class="date"><?=$p['date']->parse($new['cdate'], '%d %F %Y');?></span>
							</h3>
				            <?php } ?>
							<div class="imagesContainer">
							   <?if (!empty($new['main_photo'])) {?>
							   <a href="/news/<?=$new['id']?>"><img src="<?=$this->getStaticPath('/upload/_500_600_80_' . $new['main_photo'])?>" alt="<?=$new['name']?>" /></a>
							   <?}?>
							</div>
							<div class="entry">
								<?=$new['anounce']?>
								<a href="/news/<?=$new['id']?>" class="more_new" rel="nofollow">Читать дальше</a>
							</div>
							<div class="newsMeta">
								<span class="comments"><a href="/news/<?=$new['id']?>#comments">Комментариев: <?=RoomFactory::load('news-'.$new['id'])->getCount();?></a></span>
								<span class="views">Просмотров <?=$new['views']?></span><br />
								<span class="tags">Тэги:<?
								foreach ($persons as $i => $person) {
									$link = $person['eng_name'];
					       	        $link = str_replace('-', '_', $link);
                    		        $link = str_replace('&dash;', '_', $link);
						            $link = '/persons/'.str_replace(' ', '-', $link);
								    ?>
									<a href="<?=$link;?>"><?=$person['name']?></a><?if ($i<count($persons)-1){?>,<?}?>
								<?}?>
								<?if (!empty($persons) && !empty($events)){?>,<?};?>
								<?foreach ($events as $i => $event) {?>
									<a href="/event/<?=$event['id']?>"><?=$event['name']?></a>
									<?if ($i<count($events)-1){?>,<?}?>
								<?}?>
								</span>
							</div>
						</div>
						<?}?>
					</div>
					<div>
					<div class="paginator smaller">

<?
if($num > $per_page){
?>
							<p class="pages">Страницы:</p>
							<ul style="display: block; float: none;">
								<? if ($d['page'] != 1) { ?><li><a href="/category/<?= $column['alias']; ?>/page/<?= ($d['page'] != $num ? $d['page'] - 1 : $num); ?>">Предыдущая</a></li><? } ?>
								<? if ($d['page'] < ($num / $per_page)) { ?><li><a href="/category/<?= $column['alias']; ?>/page/<?= ($d['page'] != 1 ? $d['page'] + 1 : 2); ?>">Следующая</a></li> <? } ?>
							</ul>
							<ul style="display: block; clear: both;">
								<?= ($p['pager']->cat_pages($d['page'], $num, 2, '/category/' . $column['alias'] . '/page/')); ?>
								<li class="arch"><a href="/category/<?=$column['alias'];?>/news">архив новостей</a></li>
							</ul>
<?} else {?>
						<ul style="display: block; clear: both;"><li class="arch"><a href="/category/<?=$column['alias'];?>/news">архив новостей</a></li></ul>
<?}?>
						</div>
					</div>
					<div class="pastHeadline">
						<h2>ранее</h2>
					</div>
					<div class="trackContainer datesTrack">
						<?
						$tmp_time=strtotime(date('Y-m-d',TIME).'00:00:00');
						$date=$tmp_time;
						for ($i=0;$i<3;$i++)
						{
						    $date=strtotime("-1 month",$date);
							$news_older = $p['query']->get('prev_news_columns', array('date_ym_like' => date('Y-m', $date), 'column'=>$column['id']), array('v.num DESC','n.newsIntDate DESC', 'n.id DESC'), 0, 5, null, true);
							if (!empty($news_older)) {
						?>
						<div class="trackItem">
							<h4><?=$p['date']->unixtime($date,'%N %Y')?></h4>
							<ul>
								<?foreach ($news_older as $j => $new) { ?>
								<li><a href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=$new['pole16']?>)</li>
								<?}?>
							</ul>
						</div>
							<?}
						}?>
					</div>					
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>