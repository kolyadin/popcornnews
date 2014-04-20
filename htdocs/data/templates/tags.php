<? $this->_render('inc_header',array('title'=>'Персоны','header'=>'Персоны','top_code'=>'*','header_small'=>'Все звезды')); ?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<ul class="menu">
						<li class="active">персоны</li>
						<li><a href="/persons/all">все</a></li>
					</ul>
					<form action="/persons/search" method="post" class="searchbox">
					<input type="hidden" name="type" value="persons">
					<input type="hidden" name="action" value="search">
						<fieldset>
							<label>
								поиск по имени
								<input type="text" name="search" value="" class="suggest" onclick="return {url:'/ajax/person_search/'}"/>
							</label>
							<input type="submit" value="найти" />
						</fieldset>
					</form>
					<div class="irh irhPersonsTop">
						<div class="irhContainer">
							<h3>топ персон<span class="replacer"></span></h3>
						</div>
					</div>
					<ul class="personsTop">
				      <?
				      $ii=1;
				      foreach ($p['query']->get('persons_rating',null,array('rating desc'),null,9) as $i => $person) {
				      ?>
						<li>
							<a href="/persons/<?=$handler->Name2URL($person['eng_name']);?>">
								<img alt="<?=$person['name']?>" src="<?=$this->getStaticPath('/upload/_192_243_80_'.$person['photo'])?>" />
								<span class="stats">
									<strong><?=$ii++;?></strong>
									<span class="name"><?=$person['name']?></span>
									<span class="char">Популярность: <?printf("%.1f",$person['rating']/10)?></span>
								</span>
							</a>
						</li>
					<?}?>
					</ul>
					<div class="personsTopCols">
						<div class="irh irhWeekHeroes">
							<div class="irhContainer">
								<h3>герои недели<span class="replacer"></span></h3>
							</div>
							<ul class="heroesTrack">
								<?
								// persions week heroes
								$tags = new VPA_table_news_tags;
								$tags->add_field('cnt', 'COUNT(id)', 'news_num', array('sql' => INT));
								$tags->get($tags, array('news_regtime>' => date('Y-m-d H:i:s', strtotime('-1 week')), 'type' => 'persons'), array('COUNT(id) DESC'), 0, 3, array('tid'));
								$tags->get($tags);
								
								$tids = array();
								foreach ($tags as &$tag) $tids[] = $tag['tid'];
								
								$tags_info = $p['query']->get_params('persons', array('ids' => join(',', $tids)), null, 0, 3, null, array('id', 'name', 'main_photo', 'eng_name'));
								
								$tags_out = array();
								foreach ($tags as &$tag) {
									$current = &$tags_out[];
									foreach ($tags_info as &$tag_info) {
										if ($tag_info['id'] == $tag['tid']) {
											$current = $tag_info;
										}
									}
									$current['news_num'] = $tag['news_num'];
									$current['id'] = $tag['tid'];
								}
								foreach($tags_out as $i => $heroes) {
								?>
								<li>
									<a href="/persons/<?=$handler->Name2URL($heroes["eng_name"]);?>" class="ava"><img src="/upload/_100_100_80_<?=$heroes["main_photo"]?>" /></a>
									<div class="details">
										<a href="/persons/<?=$handler->Name2URL($heroes["eng_name"]);?>"><?=$heroes['name']?></a>
										<dl>
											<dt><?=$heroes['news_num']?></dt>
											<dd><?=$p['declension']->get($heroes['news_num'],'новость','новости','новостей')?></dd>
										</dl>
									</div>
								</li>
								<?}?>
							</ul>
						</div>
						<div class="irh irhWeekFacts">
							<div class="irhContainer">
								<h3>факты недели<span class="replacer"></span></h3>
							</div>
							<ul class="factsTrack">
                                <?
                                $cmd=
"select a.id, a.name, d.pole1 as eng_name, a.person1, floor(sum(b.vote)/count(b.fid)) trues, floor(sum(c.vote)/count(c.fid)) likes, (count(b.fid)+count(c.fid)) cnt, d.pole15 person_name from popcornnews_facts a
left join popcornnews_fact_votes b on b.fid=a.id
left join popcornnews_fact_votes c on c.fid=a.id
left join ".TBL_GOODS_." d on d.id=a.person1
where a.cdate>=".strtotime("-1 week",strtotime(date('Y-m-d').' 00:00:00'))." and d.goods_id=3 and b.rubric=1 and c.rubric=2
group by b.fid,c.fid
order by cnt desc
limit 3";
								foreach($p['query']->get_query($cmd, true, 60*60*12) as $i=> $facts){?>
								<li>
									<div class="fact">
										<p><a href="/persons/<?=$handler->Name2URL($facts['eng_name']);?>/facts"><?=$facts['name']?></a></p>
									</div>
									<h4><a href="/persons/<?=$handler->Name2URL($facts['eng_name']);?>">Факт о <?=$facts['person_name']?></a></h4>
									<dl>
										<dt><strong><?=$facts['trues']?></strong>%</dt>
										<dd>верят</dd>
									</dl>
									<dl>
										<dt><strong><?=$facts['likes']?></strong>%</dt>
										<dd>нравится</dd>
									</dl>
								</li>
								<?}?>
							</ul>
						</div>
					</div>
					<?$persons=$p['query']->get('persons_rating',null,array($d['sort']),null,null);
                    if(count($persons)>0){
                    ?>
                    <br><br>
					<table class="personsRate">
						<tr>
							<th>&nbsp;</th>
							<th class="person">Персона</th>
							<th><a href="/persons/sort/rating<?=($d['sort']!='rating desc' ? '_desc' : '')?>">Популярность</a></th>
							<th><a href="/persons/sort/talant<?=($d['sort']!='talant desc' ? '_desc' : '')?>">Талант</a></th>
							<th><a href="/persons/sort/style<?=($d['sort']!='style desc' ? '_desc' : '')?>">Стиль</a></th>
							<th><a href="/persons/sort/face<?=($d['sort']!='face desc' ? '_desc' : '')?>">Внешность</a></th>
							<th><a href="/persons/sort/fans<?=($d['sort']!='fans desc' ? '_desc' : '')?>">Поклонники</a></th>
						</tr>
						<?foreach ($persons as $i => $person) {?>
						<tr>
							<td class="num"><?=$i+1?></td>
							<td class="person"><a href="/persons/<?=$handler->Name2URL($person['eng_name']);?>"><?=$person['name']?></a></td>
							<td><strong><?printf("%.1f",$person['rating']/10)?></strong></td>
							<td><?=ceil($person['talant'])/10==10 ? ceil($person['talant'])/10 : sprintf("%.1f",$person['talant']/10)?></td>
							<td><?=ceil($person['style'])/10==10? ceil($person['style'])/10 : sprintf("%.1f",$person['style']/10)?></td>
							<td><?=ceil($person['face'])/10==10 ? ceil($person['face'])/10 : sprintf("%.1f",$person['face']/10)?></td>
							<td><?printf("%d",$person['fans'])?></td>
						</tr>
						<?}?>
					</table>
					<?}?>
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>
