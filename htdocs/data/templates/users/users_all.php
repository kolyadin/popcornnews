<?
$this->_render('inc_header',array('title'=>'Пользователи','header'=>'Пользователи','top_code'=>'*','header_small'=>'Все пользователи сайта'));?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
	            <ul class="menu">
	                <li><a href="/users/">пользователи</a></li>
	                <li><a href="/users_top/">топ</a></li>
	                <li><a href="/users_online/">on-line</a></li>
	                <?=$d['ucity_id']!=''&&$d['ucity']!=''?'<li><a href="/users_city/'.$d['ucity_id'].'">в твоем городе <em>('.$d['ucity'].')</em></a></li>':''?>
	            </ul>
				<form class="searchbox" action="/users_all/" method="post" name="user_search">
					<input type="hidden" name="type" value="users_all">
					<input type="hidden" name="action" value="search">
						<fieldset>
							<label>
								поиск по нику
								<input type="text" name="user_nick" value="<?=htmlspecialchars($d['user_nick'], ENT_IGNORE, 'cp1251', false);?>"/>
							</label>
							<input type="submit" value="найти"/>
						</fieldset>
					</form>
					<div class="abcContainer">
						<ul class="abc">
							<li><a href="/users_all/page/1/sort/<?=$d['sort'];?>/">Все</a></li>
							<li><a href="/users_all/page/1/sort/<?=$d['sort'];?>/leter/other">#</a></li>
							<li><a href="/users_all/page/1/sort/<?=$d['sort'];?>/leter/dig">0-9</a></li>
							<?foreach ($p['query']->get('users',array('fl_nick'=>'[a-z]'),array('let'),null,null,array('substring(nick,1,1)')) as $i => $let){?>
							<li><a href="/users_all/page/1/sort/<?=$d['sort'];?>/leter/<?=$let["let"];?>"><?=$let["let"];?></a></li>
							<?}?>
                    	</ul>
						<ul class="abc">
						<?foreach ($p['query']->get('users',array('fl_nick'=>'[а-я]'),array('let'),null,null,array('substring(nick,1,1)')) as $i => $let){?>
						<li><a href="/users_all/page/1/sort/<?=$d['sort'];?>/leter/<?=urlencode($let["let"]);?>"><?=$let["let"];?></a></li>
						<?}?>
                    	</ul>
					</div>
						<?
						$let_search=($d['user_nick']!=''?'/unick/'.urlencode($d['user_nick']):($d['leter']!=''?'/leter/'.$d['leter_return']:''));
						$limit=50;
						$offset=($d['page']-1)*$limit;
						$num_users=$p['query']->get_num('users',($d['user_nick']!=''?array('nick'=>$d['user_nick']):($d["leter"]!=''?array('fl_nick'=>$d["leter"]):null)));
                        if($num_users>=1){?>

					<table class="contentUsersTable">
						<tr>
							<th class="user"><a href="/users_all/page/<?=$d['page']?>/sort/nick<?=$d['sort']=='nick' ? '_desc' : ''?><?=$let_search;?>">Пользователь</a></th>
							<th class="starRating">&nbsp;</th>
							<th class="city"><a href="/users_all/page/<?=$d['page']?>/sort/city<?=$d['sort']=='city' ? '_desc' : ''?><?=$let_search;?>">Город</a></th>
							<th class="rating"><a href="/users_all/page/<?=$d['page']?>/sort/rating<?=$d['sort']=='rating' ? '_desc' : ''?><?=$let_search;?>">Рейтинг</a></th>
						</tr>
						<?
						$pages=ceil($num_users/$limit);
						foreach ($p['query']->get('users',($d['user_nick']!=''?array('nick'=>$d['user_nick']):($d["leter"]!=''?array('fl_nick'=>$d["leter"]):null)),array($d['sql_sort']),$offset,$limit) as $i => $user){
						?>
						<tr>
							<td class="user">
								<a rel="nofollow" href="/profile/<?=$user['id']?>">
									<img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" />
									<span><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false)?></span>
								</a>
							</td>
							<td class="starRating">
								<?$rating=$p['rating']->_class($user['rating']);?>
								<div class="userRating <?=$rating['class']?>">
									<div class="rating <?=$rating['stars']?>"></div>
									<span><?=$rating['name']?></span>
								</div>
							</td>
							<td class="city"><a href="/users_city/<?=$user['city_id']?>"><?=($user['city_id']>0) ? $user['city'] : "&nbsp;"?></a></td>
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
							<a href="/users_all/page/<?=$pi['link']?>/sort/<?=$d['sort'].$let_search?>"><?=$pi['text']?></a>
							<?} else {?>
							<?=$pi['text']?>
							<?}?>
							</li>
						<?}?>
						</ul>
					</div>
					<?}else{?>
					<h4>Ничего не найдено</h4>
					<?}?>
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>
