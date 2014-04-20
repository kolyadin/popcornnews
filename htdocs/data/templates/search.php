<?$this->_render('inc_header',array('title' => 'Поиск в новостях','header'=>$p['date']->unixtime(mktime(),'%F <strong>%Y</strong>'),'top_code'=>$p['date']->unixtime(mktime(),'%d'),'header_small'=>''));?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<h2>Поиск в новостях</h2>
					<div class="trackContainer datesTrack">
						<?
						if (!empty($d['result'])) {
							$date = 0;
							$i = 0;
							foreach ($d['result'] as $new) {
								if ($date != substr($new['cdate'], 0, 6)) {
									$new_date = true;
									$date = substr($new['cdate'], 0, 6);
								} else {
									$new_date = false;
								}

								if ($new_date) {
						?>
						<?if ($i++ != 0) {?>
							</ul>
						</div>
						<?}?>
						<div class="trackItem">
							<h4><?=$p['date']->unixtime(mktime(0, 0, 0, substr($date, 4, 2), 15/*date('j')*/, substr($date, 0, 4)),'%N %Y')?></h4>
							<ul>
						<?
								}
						?>
							<li><a href="/news/<?=$new['id']?>"><?=$new['name']?></a> (<?=RoomFactory::load('news-'.$new['id'])->getCount();?>)</li>
						<?
							}
						} else {
						?>
							<h4>Ничего не найдено</h4>
						<?}?>
						<?if ($i > 0){?></ul></div><?}?>
					</div>
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>