<?
// страницы
if (!is_numeric($d['page'])) $d['page'] = 1;
$per_page = 10;

$event = array_shift($p['query']->get('events', array('id' => $d['event']), null, 0, 1));

$isBlog = ($event['id'] == $_SERVER['blogID']);

$this->_render('inc_header', array('title' => $event['name'], 'header' => $event['name'], 'top_code' => ($isBlog)?'':'*', 'header_small' => '', 'isBlog' => $isBlog));
?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<div class="newsTrack">
						<?
						$event_news = $p['query']->get('news_with_tags', array('event' => $d['event']), array('newsIntDate DESC', 'id DESC'), ($d['page'] - 1) * $per_page, $per_page);
						foreach ($event_news as $i => $new) {
						        $persons = $p['query']->get('persons', array('ids'=>$new['ids_persons']));
				                $events = $p['query']->get('events', array('ids'=>$new['ids_events']));	
			                    $isMashaBlog = in_array($_SERVER['blogID'], explode(',',$new['ids_events']));
			                    $blogTag = $p['query']->get('events', array('ids' => $_SERVER['blogID']));
			                    $blogTag = $blogTag[0];
						     ?>
						<div class="trackItem">
							<h3><a href="/news/<?=$new['id']?>"><?=$new['name']?></a><br />
							<span class="date"><?=$p['date']->parse($new['cdate'], '%d %F %Y');?></span>
							</h3>
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
<?
$nn = new VPA_table_news_tags();
$num = $nn->get_num_tags($d['event'], 'events');

if($num > $per_page){
?>
						<div class="paginator smaller">
							<p class="pages">Страницы:</p>
							<ul style="display: block; float: none;">
								<? if ($d['page'] != 1) { ?><li><a href="/event/<?= $d['event']; ?>/page/<?= ($d['page'] != $num ? $d['page'] - 1 : $num); ?>">Предыдущая</a></li><? } ?>
								<li><a href="/event/<?= $d['event']; ?>/page/<?= ($d['page'] != 1 ? $d['page'] + 1 : 2); ?>">Следующая</a></li>
							</ul>
							<ul style="display: block; clear: both;">
								<?= ($p['pager']->cat_pages($d['page'], $num, 2, '/event/' . $d['event'] . '/page/')); ?>
								<li class="arch"><a href="/event/<?= $d['event'] ?>/news">архив новостей</a></li>
							</ul>
						</div>
<?} else {?>
                        <div class="paginator smaller">
						    <ul style="display: block; clear: both;">
                                <li><a href="/event/<?= $d['event'] ?>/news">архив новостей</a></li></ul>
                        </div>
<?}?>
					</div>
					<div class="pastHeadline">
						<h2>ранее</h2>
					</div>
					<div class="trackContainer datesTrack">
						<?
						$tmp_time=strtotime(date('Y-m-d',TIME).date('H').':'.date('i').':00');
						$date=$tmp_time;
						for ($i=0;$i<3;$i++)
						{
						    $date=strtotime("-1 month",$date);
							$news = $p['query']->get('prev_news_events', array('date_ym_like' => date('Y-m', $date), 'event'=>$d['event']), array('v.num DESC', 'n.newsIntDate DESC', 'n.id DESC'), 0, 5, null, true);
							if (!empty($news)) {
						?>
						<div class="trackItem">
							<h4><?=$p['date']->unixtime($date,'%N %Y')?></h4>
							<ul>
								<?foreach ($news as $j => $new) { ?>
								<li><a href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=RoomFactory::load('news-'.$new['id'])
                                                                                              ->getCount();?>)</li>
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