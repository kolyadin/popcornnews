<?

$column = $d['column'];

$tmp_time=strtotime(date('Y-m-d',TIME).date('H').':'.date('i').':00');
$this->_render('inc_header',array('title'=>$column['title'],'header'=>$column['title'],'top_code'=>$p['date']->unixtime($tmp_time,'%d'),'header_small'=>''));
?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<h2>����� �������� <?=$column['title']?></h2>
					<ul class="headlinesList">
						<?
						for ($i=date('Y');$i>2005;$i--) {
							$news_num=$p['query']->get_num('news', array('year' => $i, 'column' => $column['id']), true);

							if ($news_num > 0)
							{
							?>
							<li><h2><?if ($d['year']==$i){?><?=$i?><?}else{?><a href="/category/<?=$column['alias']?>/news/<?=$i?>"><?=$i?></a><?}?></h2></li>
							<?}
						}
						?>
					</ul>
					<div class="trackContainer datesTrack">
						<?for ($i=12;$i>0;$i--)
						{
							$news=$p['query']->get('news', array('date_ym_like' => sprintf('%04u-%02u', $d['year'], $i), 'column' => $column['id']), array('newsIntDate DESC', 'id DESC'), null, null, null, true);
							if (!empty($news))
							{
								$date=mktime(0,0,0,$i,15,$d['year']);
							?>
							<div class="trackItem">
								<h4><?=$p['date']->unixtime($date,'%N %Y')?></h4>
								<ul>
									<?foreach ($news as $j => $new) { ?>
									<li><a href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=RoomFactory::load('news-'.$new['id'])->getCount();?>)</li>
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