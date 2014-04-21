<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="yandex-verification" content="66dc98a2d08a62a7" />
		<title><?=$d['title']?> - popcornnews</title>
		<link rel="stylesheet" type="text/css" href="/css/main.css?d=28.04.11" />
		<!--[if lte IE 7]>
		<link rel="stylesheet" href="/css/ie7.css" />
		<![endif]-->
		<!--[if lte IE 6]>
		<link rel="stylesheet" href="/css/ie6.css?r=28042010" />
		<![endif]-->
		<script type="text/javascript" src="/swfobject/swfobject.js"></script>
		<script language="javascript">AC_FL_RunContent = 0;</script>
		<script type="text/javascript" src="/js/AC_RunActiveContent.js"></script>
		<script type="text/javascript" src="/js/as.js?r=17.06.10"></script>
		<script type="text/javascript" src="/js/aspatch.js?r=25.06.10"></script>
		<script type="text/javascript" src="/js/gallery.js"></script>
		<script type="text/javascript" src="/js/main.js?d=28.04.11"></script>
		<script type="text/javascript" src="/js/vpa_ajax.js"></script>
		<script type="text/javascript" src="/js/vpa_logic.js?r=04.07.10"></script>
		<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.popcornnews.ru/rss.php" />
	</head>
	<body>
		<div class="big-contest">
			<div id="header" class="twoCols contest-header-top">
				<div class="hhFeatures">
					<div class="logoHeader contest-header">
						<a class="logo" href="/">
							<img src="/img/logo.png" alt="" />
						</a>
					</div>
					<div class="sideBar">
						<? if (empty($d['cuser'])) { ?>
						<noindex>
							<div class="enterSite">
								<form method="POST" action="/">
									<input type="hidden" name="type" value="login" />
									<fieldset>
										<label>E-mail<input type="text" name="email" /></label>
									</fieldset>
									<fieldset>
										<label>пароль<input type="password" name="pass" /></label>
									</fieldset>
									<fieldset>
										<input type="submit" />
									</fieldset>
								</form>
								<div class="actions">
									<a rel="nofollow" href="/remind">Забыли пароль?</a> | <a rel="nofollow" href="/register"><strong>Регистрация</strong></a>
								</div>
							</div>
						</noindex>
						<? } else { ?>
						<div class="logged">
							<a class="avaSmall" rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">
								<? if (!empty($d['cuser']['avatara'])) { ?>
									<img src="/avatars_small/<?=$d['cuser']['avatara']?>" />
								<?} else {?>
								<img src="/img/no_photo_small.jpg" />
								<?}?>
							</a>
								<div class="details">
									<h4><span style="margin: 0px; padding: 0px; margin-right: 10px;" id="check_mail"></span><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE, 'cp1251', false);?></a></h4>
									<?$rating=$p['rating']->_class($d['cuser']['rating']);?>
									<div class="userRating <?=$rating['class']?>">
										<div class="rating <?=$rating['stars']?>"></div>
										<span><?=$d['cuser']['rating']?> <?=$rating['name']?></span>
								</div>
								<form name="logout" id="logout" method="post">
									<input type="hidden" name="type" value="logout" />
								</form>
								<a class="exit" href="#" onclick="as.$$('#logout').submit(); return false;">выход</a>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>
		</div>
		<div id="wrapper"<?=(isset($d['cuser']) && !empty($d['cuser'])) ? ' class="registered"' : ''?>>
<?
$u_online = new VPA_online;
$user_online = $u_online->get_count_online_users()+rand(1,2);
?>
			<ul class="menu">
				<li><a href="/">главная</a></li>
				<!--li><a href="#">новости</a></li-->
				<li><a href="/tags">персоны</a></li>
				<li><a href="/meet">пары</a></li>
				<li><a href="/kids">дети</a></li>
				<li><a href="/chat">болталка</a></li>
				<li><a href="/users">пользователи</a> <span class="online"><?=$user_online?></span></li>
				<li><a href="/rules">правила</a></li>
				<li><a href="/faq">FAQ</a></li>
				<?/*<li><a href="/voting"><img alt="" src="/i/voting_for_head.jpg" /></a></li>*/?>
				<li><a href="/rss.php"><img src="/i/100px-Feed-icon.png" alt="RSS"/></a></li>
				<li><a href="http://vkontakte.ru/club10361506" target="_blank"><img src="/i/vkontakte_logo.png" alt="popcornnews.ru ВКонтакте"/></a></li>
			</ul>
