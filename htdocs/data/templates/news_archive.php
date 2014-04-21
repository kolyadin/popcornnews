<?

$title = 'Новости за ';
$date = ($d['day'] == -1) ? mktime(0, 0, 0, $d['month'], 1, $d['year']) : mktime(0, 0, 0, $d['month'], $d['day'], $d['year']);
if($d['day'] != -1) {
    $title .= $p['date']->unixtime($date, '%j %F %Y');
} else {
    $title .= $p['date']->unixtime($date, '%N %Y');
}

$title .= ' года';

$this->_render('inc_header',array('title'=>$title,'header'=>'Архив новостей','top_code'=>null));

if($d['year'] == date('Y') && ((int)$d['month']) > date('n')) {
    header('Location: /archive/'.$d['year'].'/'.date('n'));
}

$news = $p['query']->get('news', array('date_ym_like' => sprintf('%04u-%02u', $d['year'], $d['month'])), array('newsIntDate DESC', 'id DESC'), null, null, null, false);

$months = array();

$sql = 'SELECT MONTH(`newsIntDate`) as month FROM `popconnews_goods_` WHERE `goods_id` = 2 AND YEAR(`newsIntDate`) = '.$d['year'].' GROUP BY MONTH(`newsIntDate`) ORDER BY MONTH(`newsIntDate`) DESC';

$mc = $p['memcache']::getInstance();
if($mc->is($sql)) {
    $months = $mc->get($sql);
} 
else {
    $hr = mysql_query($sql);
    if($hr !== false) {
        while (false != ($r = mysql_fetch_assoc($hr))) {
            $months[] = $r['month'];
        }
        mysql_free_result($hr);
    }
    $mc->set($sql, $months);
}

if(!empty($news)) {
    foreach ($news as $id => $new) {
        $dt = date_parse($new['newsIntDate']);
        $days[] = $dt['day'];
        if($d['day'] != -1 && $d['day'] != $dt['day']) {
            unset($news[$id]);
        }
    }
    $days = array_values(array_unique($days));
}

if(array_search($d['month'], $months) === false) {
    header('Location: /archive/'.$d['year'].'/'.$months[count($months)-1]);
}
if($d['day'] != -1 && array_search($d['day'], $days) === false) {
    header('Location: /archive/'.$d['year'].'/'.$d['month']);
}

?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<ul class="menu">
						<li class="active"><a href="/archive/">архив новостей</a></li>
						<li><a href="/tags/">персоны</a></li>
						<li><a href="/puzli/">пазлы</a></li>
						<li><a href="/oboi/">обои</a></li>
					</ul>
					<h2>Архив новостей</h2>
					<ul class="headlinesList">
						<?for ($i = date('Y'); $i > 2006; $i--) {?>
						<li><h2><?if ($d['year']==$i){?><?=$i?><?}else{?><a href="/archive/<?=$i?>/<?=$d['month']?>"><?=$i?></a><?}?></h2></li>
						<?}?>
					</ul>
					<ul class="headlinesList">
						<?
						$cl = (date('n')<12 && $d['year'] == date('Y')) ? ' class="nonfull"' : '';
						foreach ($months as $i) {
						    $cl1 = ($d['month'] == $i && $d['day'] != -1) ? ' class="current"' : '';
						    ?>
						    	<li><h3<?=$cl?>><?if ($d['month'] == $i && $d['day'] == -1){?><?=$p['date']->nominative[$i]?><?}else{?><a href="/archive/<?=$d['year']?>/<?=$i?>"<?=$cl1?>><?=$p['date']->nominative[$i]?></a><?}?></h3></li>
						    <?
						}						
						?>
					</ul>
					<!-- days out -->
					<ul class="headlinesList">
					    <?
					    $dayStart = mktime(null, null, null, $d['month'], 1, $d['year']);
					    $dayStart = date('j', $dayStart);
					    $dayEnd = mktime(null, null, null, $d['month']+1, 0, $d['year']);
					    $dayEnd = date('j', $dayEnd);
					    if(date('n') == $d['month']) {
					        $dayEnd = date('j');
					    }
					    for($i=$dayEnd; $i>=$dayStart; $i--) {
					        if(array_search($i, $days) !== false) {
	    				        if($d['day'] == $i) {
    					            echo '<li><h4>'.$i.'</h4></li>';
					            } 
					            else {
					                echo '<li><h4><a href="/archive/'.$d['year'].'/'.$d['month'].'/'.$i.'">'.$i.'</a></h4></li>';
					            }
					        }
					    }
					    ?>
					</ul>
					<!-- end days out -->
					<div class="trackContainer datesTrack">
						<?
						if (!empty($news)) {
							$date = ($d['day'] == -1) ? mktime(0, 0, 0, $d['month'], 1, $d['year']) : mktime(0, 0, 0, $d['month'], $d['day'], $d['year']);
						?>
							<div class="trackItem">
								<h4><?=($d['day'] == -1)?$p['date']->unixtime($date,'%N %Y'):$p['date']->unixtime($date, '%j %F %Y')?></h4>
								<ul>
									<?foreach ($news as $i => $new) { ?>
									<li><a href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=RoomFactory::load('news-'.$new['id'])->getCount();?>)</li>
									<?}?>
								</ul>
							</div>
						<?
						}
						?>
					</div>
				</div>
			<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>