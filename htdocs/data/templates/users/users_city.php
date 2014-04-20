<?
$this->_render('inc_header',array('title'=>'Пользователи','header'=>'Пользователи','top_code'=>'*','header_small'=>'В городе '.($d['city_db']['name']!=''?$d['city_db']['name']:'Другой')));?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
	            <ul class="menu">
	                <li><a href="/users/">пользователи</a></li>
	                <?/*<li><a href="/users_all/">все</a></li>*/?>
	                <li><a href="/users_top/">топ</a></li>
	                <li><a href="/users_online/">on-line</a></li>
	                <li class="active"><?=$d['ucity_id']!=''&&$d['ucity']!=''&&$d['ucity_id']==$d['city_db']['id']?'<a href="/users_city/'.$d['city_db']['id'].'">в твоем городе '.($d['city_db']['name']!=''?'<em>('.$d['city_db']['name'].')</em>':'').'</a>':'<a href="/users_city/'.$d['city_db']['id'].'">в городе <em>('.($d['city_db']['name']!=''?$d['city_db']['name']:'Другой').')</em></a>'?></li>

	            </ul>
					<table class="contentUsersTable">
						<tr>
							<th class="user"><a href="/users_city/<?=$d['city']?>/page/<?=$d['page']?>/sort/nick<?=$d['sort']=='nick' ? '_desc' : ''?>">Пользователь</a></th>
							<th class="starRating">&nbsp;</th>
							<th class="city"><a href="/users_city/<?=$d['city']?>/page/1/sort/city<?=$d['sort']=='city' ? '_desc' : ''?>">Город</a></th>
							<th class="rating"><a href="/users_city/<?=$d['city']?>/page/<?=$d['page']?>/sort/rating<?=$d['sort']=='rating' ? '_desc' : ''?>">Рейтинг</a></th>
						</tr>
						<?
						$limit=50;
						$offset=($d['page']-1)*$limit;
						$num_users=$p['query']->get_num('users',array('city_id'=>$d['city']));
						$pages=ceil($num_users/$limit);
						foreach ($p['query']->get('users',array('city_id'=>$d['city']),array($d['sql_sort']),$offset,$limit) as $i => $user){
						?>
						<tr>
							<td class="user">
								<a rel="nofollow" href="/profile/<?=$user['id']?>">
									<img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" />
									<span><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></span>
								</a>
							</td>
							<td class="starRating">
								<?$rating=$p['rating']->_class($user['rating']);?>
								<div class="userRating <?=$rating['class']?>">
									<div class="rating <?=$rating['stars']?>"></div>
									<span><?=$rating['name']?></span>
								</div>
							</td>
							<td class="city"><a href="/users_city/<?=$user['city_id']?>"><?=($user['city_id']>0) ? $user['city'] : "Другой"?></a></td>
							<td class="rating">
								<span><?=$user['rating']?></span>
							</td>
						</tr>
						<?}?>
					</table>
					<div class="paginator smaller">
						<p class="pages">Страницы:</p>
						<ul>
						<?foreach ($p['pager']->make($d['page'],$pages,50) as $i => $pi) { ?>
							<li>
							<?if (!isset($pi['current'])) {?>
							<a href="/users_city/<?=$d['city']?>/page/<?=$pi['link']?>/sort/<?=$d['sort']?>"><?=$pi['text']?></a>
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
