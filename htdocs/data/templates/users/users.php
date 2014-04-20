<? $this->_render('inc_header', array('title' => 'ѕользователи', 'header' => 'ѕользователи', 'top_code' => '*', 'header_small' => '¬се пользователи сайта')); ?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a href="/users/">пользователи</a></li>
			<? /* <li><a href="/users_all/">все</a></li> */ ?>
			<li><a href="/users_top/">топ</a></li>
			<li><a href="/users_online/">on-line</a></li>
			<?=$d['ucity_id'] != '' && $d['ucity'] != '' ? '<li><a href="/users_city/' . $d['ucity_id'] . '">в твоем городе <em>(' . $d['ucity'] . ')</em></a></li>' : ''?>
		</ul>
		<form class="searchbox" action="/users_all/" method="post" name="user_search">
			<input type="hidden" name="type" value="users_all">
			<input type="hidden" name="action" value="search">
			<fieldset>
				<label>
					поиск по нику
					<input type="text" name="user_nick" value="<?= (isset($d['user_nick']) ? htmlspecialchars($d['user_nick'], ENT_IGNORE, 'cp1251', false) : ''); ?>"/>
				</label>
				<input type="submit" value="найти"/>
			</fieldset>
		</form>
		<div class="irh irhMonthActives">
			<div class="irhContainer">
				<h3>активисты мес€ца<span class="replacer"></span></h3>
			</div>
			<ul class="monthBorders">
				<?$i = 1; foreach ($p['query']->get('users', array('activist_now' => 1), array('rating desc'), 0, 4) as $i => $user) {$i++; ?>
				<li class="<?= ($i == 1 ? 'one' : ($i == 2 ? 'two' : ($i == 3 ? 'three' : 'four'))) ?>">
					<a rel="nofollow" href="/profile/<?= $user['id'] ?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara'], true))?>" alt="" /></a>
				</li>
				<?}?>
			</ul>
		</div>
		<div class="irh irhUsersTop">
			<div class="irhContainer">
				<h3>топ пользователей<span class="replacer"></span></h3>
			</div>
			<table class="contentUsersTable">
				<?
				$limit = 10;
				$offset = 0;
				$number_list = 1;
				foreach ($p['query']->get('users', null, array('rating desc'), $offset, $limit) as $i => $user) {
					$rating = $p['rating']->_class($user['rating']);
				?>
				<tr>
					<td class="num"><?= $number_list++; ?></td>
					<td class="user">
						<a class="ava" rel="nofollow" href="/profile/<?= $user['id'] ?>">
							<img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" />
							<span><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false)?></span>
						</a>
					</td>
					<td class="starRating">
						<div class="userRating <?= $rating['class'] ?>">
							<div class="rating <?= $rating['stars'] ?>"></div>
							<span><?= $rating['name'] ?></span>
						</div>
					</td>
					<td class="city">
					<?= (intval($user['city_id']) > 0 && $user['city'] != '') ? $user['city'] : "&nbsp;" ?>
				</td>
				<td class="rating">
					<span><?= $user['rating'] ?></span>
				</td>
			</tr>
			<? } ?>
			</table>
		</div>
		<div class="otherUsersStats">
			<div class="irh irhNewbies">
				<div class="irhContainer">
					<h3>новички<span class="replacer"></span></h3>
				</div>
				<ul>
				<? foreach ($p['query']->get('users', array('enabled' => 1), array('id desc'), 0, 10) as $i => $user) {?>
					<li><a rel="nofollow" href="/profile/<?= $user['id'] ?>"><strong><?= preg_replace(array("/</", "/>/"), array("&lt;", "&gt;"), $user['nick']) ?></strong>, <?= (intval($user['city_id']) > 0 && $user['city'] != '') ? $user['city'] : "ƒругой" ?></a></li>
				<? } ?>
			</ul>
		</div>

		<div class="irh irhWhoWhere">
			<div class="irhContainer">
				<h3>кто где<span class="replacer"></span></h3>
			</div>
			<form action="/users_city/" method="post">
				<input type="hidden" name="type" value="users_city">
				<fieldset>
					<select class="city" name="action">
						<? foreach ($p['query']->get('users_countries', array('rating')) as $i => $countries) { ?>
						<optgroup label="<?=$countries['name']?>">
							<? foreach ($p['query']->get('users_cities', array('country_id' => $countries['id']), array('rating')) as $i => $cities) { ?>
							<option value="<?= $cities['id'] ?>"><?= $cities['name'] ?></option>
							<? } ?>
						</optgroup>
						<? } ?>
					</select>
					<input type="submit" value="Ќайти" />
				</fieldset>
			</form>
		</div>

		<div class="irh irhNowOnline">
			<?
					$u_online = new VPA_online();
					$users_online = $u_online->get_online_users(array('rating desc'));
			?>
					<div class="irhContainer">
						<h3>сейчас на сайте<span class="replacer"></span></h3>
						<span class="counter"><a href="/users_online/"><?= count($users_online) ?></a></span>
					</div>
						<?if (count($users_online) > 1) {?>
						<ul>
						<?for ($i = 0; $i < 8; $i++) {?>
							<li><a rel="nofollow" href="/profile/<?= $users_online[$i]['id'] ?>"><img src="<?=$this->getStaticPath($this->getUserAvatar($users_online[$i]['avatara']))?>" title="<?=htmlspecialchars($users_online[$i]['nick'], ENT_IGNORE, 'cp1251', false);?>" alt="<?=htmlspecialchars($users_online[$i]['nick'], ENT_IGNORE, 'cp1251', false);?>"/></a></li>
						<?}?>
						</ul>
						<?}?>
				</div>
			</div>
		</div>
		<? $this->_render('inc_right_column'); ?>
	</div>
<? $this->_render('inc_footer'); ?>
