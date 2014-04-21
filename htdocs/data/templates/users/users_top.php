<?
$this->_render('inc_header',array('title'=>'Пользователи','header'=>'Пользователи','top_code'=>'*','header_small'=>'Рейтинг пользователей'));?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
	            <ul class="menu">
	                <li><a href="/users/">пользователи</a></li>
	                <?/*<li><a href="/users_all/">все</a></li>*/?>
	                <li class="active"><a href="/users_top/">топ</a></li>
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
					<?if($d['cuser']['id']!=0){?>
					<div class="abcContainer">
						<ul class="abc">
							<li><a href="/users_top/page/1/sort/<?=$d['sort'];?>/">Все</a></li>
							<li><a href="/users_top/page/1/sort/<?=$d['sort'];?>/leter/other">#</a></li>
							<li><a href="/users_top/page/1/sort/<?=$d['sort'];?>/leter/dig">0-9</a></li>
						<?foreach ($p['query']->get('users',array('fl_nick'=>'[a-z]'),array('let'),null,null,array('substring(nick,1,1)')) as $i => $let){?>
							<li><a href="/users_top/page/1/sort/<?=$d['sort'];?>/leter/<?=$let["let"];?>"><?=$let["let"];?></a></li>
						<?}?>
						</ul>
						<ul class="abc">
						<?foreach ($p['query']->get('users',array('fl_nick'=>'[а-я]'),array('let'),null,null,array('substring(nick,1,1)')) as $i => $let){?>
							<li><a href="/users_top/page/1/sort/<?=$d['sort'];?>/leter/<?=urlencode($let["let"]);?>"><?=$let["let"];?></a></li>
						<?}?>
						</ul>
					</div>
					<?}?>

					<?
					$let_search=($d['user_nick']!=''?'/unick/'.urlencode($d['user_nick']):($d['leter']!=''?'/leter/'.$d['leter_return']:''));
					$limit=50;
					$offset=($d['page']-1)*$limit;
					$num_users=$p['query']->get_num('users',($d['user_nick']!=''?array('nick'=>$d['user_nick']):($d["leter"]!=''?array('fl_nick'=>$d["leter"]):null)));
					if($num_users>=1){?>
					<table class="contentUsersTable">
						<tr>
							<th>&nbsp;</th>
							<th class="user"><a href="/users_top/page/<?=$d['page']?>/sort/nick<?=$d['sort']=='nick' ? '_desc' : ''?><?=$let_search;?>">Пользователь</a></th>
							<th class="starRating"><a href="/users_top/page/<?=$d['page']?>/sort/rating<?=$d['sort']!='rating_desc' ? '_desc' : ''?><?=$let_search;?>">Рейтинг</a></th>
						</tr>
						<?
						$number_list=($d["page"]-1)*$limit+1;
						$pages=ceil($num_users/$limit);
						foreach ($p['query']->get('users',($d['user_nick']!=''?array('nick'=>$d['user_nick']):($d["leter"]!=''?array('fl_nick'=>$d["leter"]):null)),array($d['sql_sort']),$offset,$limit) as $i => $user){
						?>
						<tr>
							<td class="num <?=($number_list<=10&&($d['sort']=='rating_desc'||$d['sort']=='friends_desc'||$d['sort']=='groups_desc'))?'':'smallnum'?>"><?=$number_list++;?></td>
							<td class="user">
								<a class="ava" rel="nofollow" href="/profile/<?=$user['id']?>">
									<img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>"/>
								</a>
								<a rel="nofollow" href="/profile/<?=$user['id']?>">
									<span><?=preg_replace(array("/</","/>/"),array("&lt;","&gt;"),$user['nick'])?></span>
								</a>
							</td>
							<td class="starRating">
								<?$rating=$p['rating']->_class($user['rating']);?>
								<div class="userRating <?=$rating['class']?>">
									<div class="rating <?=$rating['stars']?>"></div>
									<span><?=$rating['name']?> <?=$user['rating']?></span>
								<div class="dot ltdot"/><div class="dot rtdot"/><div class="dot rbdot"/><div class="dot lbdot"/></div>
							</td>
						</tr>
						<?}?>
					</table>
					<?if($d['cuser']['id']!=0){?>
					<div class="paginator smaller">
						<p class="pages">Страницы:</p>
						<ul>
						<?foreach ($p['pager']->make($d['page'],$pages) as $i => $pi) { ?>
							<li>
							<?if (!isset($pi['current'])) {?>
							<a href="/users_top/page/<?=$pi['link']?>/sort/<?=$d['sort'].$let_search?>"><?=$pi['text']?></a>
							<?} else {?>
							<?=$pi['text']?>
							<?}?>
							</li>
						<?}?>
						</ul>
					</div>
					<?}?>
					<?}else{?>
					<h4>Ничего не найдено</h4>
					<?}?>
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>
