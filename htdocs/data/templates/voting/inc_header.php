<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="yandex-verification" content="662fe4b66fd69297" />
		<meta name="yandex-verification" content="59a8cfdaa947c3ea" />
		<meta name="google-site-verification" content="kYTbrmi7LsFmpAFI00SBCZ-iuJrMabNSVY3nslcLHas" />

		<?if (isset($d['meta']) && is_array($d['meta'])) {?>
		<?if (isset($d['meta']['description'])) {?><meta name="description" content="<?=htmlspecialchars($d['meta']['description'])?>" /><?}?>
		<?if (isset($d['meta']['keywords'])) {?><meta name="keywords" content="<?=htmlspecialchars($d['meta']['keywords'])?>" /><?}?>
		<?}?>

		<title><?=(isset($d['title']) ? htmlspecialchars($d['title']) : null)?> - popcornnews</title>
		<link rel="stylesheet" type="text/css" href="<?=$this->getStaticPath('/css/main.css?d=29.04.11')?>" />
		<!--[if lte IE 7]>
		<link rel="stylesheet" href="<?=$this->getStaticPath('/css/ie7.css')?>" />
		<![endif]-->
		<!--[if lte IE 6]>
		<link rel="stylesheet" href="<?=$this->getStaticPath('/css/ie6.css')?>" />
		<![endif]-->
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/swfobject.js')?>"></script>
		<script type="text/javascript">AC_FL_RunContent = 0;</script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/AC_RunActiveContent.js')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/as.js')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/aspatch.js')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/gallery.js')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/main.js?d=28.04.11')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/vpa_ajax.js')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/vpa_logic.js')?>"></script>
		<?
		if (isset($d['js']) && is_array($d['js']) && count($d['js']) > 0) {
			foreach ($d['js'] as $js) {
				printf('<script type="text/javascript" src="%s"></script>' . "\n", $this->getStaticPath('/js/' . $js));
			}
		}
		?>
		<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.popcornnews.ru/rss.php" />
		
		<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-21667993-7']);
		_gaq.push(['_trackPageview']);
			_gaq.push(['_trackPageLoadTime']);

		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		</script>
	</head>
	<body>
<?=(isset($d['links']) ? $d['links'] : '');?>
		<div class="popup-voty" id="popup">
			<div class="c">
				<a class="close" id="close" href="#">закрыть</a>
				<div class="photo">
					<img id="photo" class="photo" src="/i/0.gif" />
				</div>
				<p>
					<a class="l" id="linkL" href="#"><img src="/i/str-active-l.gif" /></a>
					<span class="l" id="l"><img src="/i/str-noactive-l.gif" /></span>
					<span id="name">&nbsp;</span>
					<a class="r" id="linkR" href="#"><img src="/i/str-active-r.gif" /></a>
					<span class="r" id="r" ><img src="/i/str-noactive-r.gif" /></span>
				</p>
			</div>
			<b class="tl"></b>
			<b class="tr"></b>
			<b class="bl"></b>
			<b class="br"></b>
			<div class="b"></div>
			<div class="t"></div>
		</div>
		<div id="wrapper"<?=(isset($d['cuser']) && !empty($d['cuser'])) ? ' class="registered"' : ''?>>
<?php
   $u_online=new VPA_online;
   $user_online=$u_online->get_online_users();
   $user_online=count($user_online)+rand(1,2);
?>
			<ul class="menu">
			<?php 
			    $menu = array(
			    'tags' => '<a href="/tags" title="персоны">персоны</a>',
			    'meet' => '<a href="/meet" title="пары">пары</a>',
			    'kids' => '<a href="/kids" title="дети">дети</a>',
			    'community' => '<a href="/community/groups" title="сообщество">сообщество</a>',
			    'users' => '<a href="/users" title="пользователи">пользователи</a> <span class="online">'.$user_online.'</span>',
			    'rules' => '<a href="/rules" title="правила">правила</a>',
			    'faq' => '<a href="/faq" title="faq">FAQ</a>'
			    );
			    
			    $paths = array(
			    'tags' => array('tags', 'tag', 'artist'),
			    'kids' => array('kids', 'kid'),
			    'users' => array('users', 'users_top', 'users_online', 'users_city', 'profile'),			    
			    );
			    
			    foreach ($menu as $type => $item) {
			        $active = ($page_type == $type) ? ' class="active"':'';
			        if(array_key_exists($type, $paths)) {
			            $active = in_array($page_type, $paths[$type]) ?  ' class="active"':'';
			        }
			        print '<li'.$active.'>'.$item.'</li>';
			    }
			    $active = '';
			?>			 			
				<li class="rss"><a href="/rss.php"><img src="/i/100px-Feed-icon.png" alt="RSS"/></a></li> 
				<li class="social"><a rel="nofollow" href="http://vkontakte.ru/club10361506" target="_blank"><img src="/i/button-vk.png" alt="popcornnews.ru ВКонтакте"/></a></li> 
				<li class="social"><a rel="nofollow" href="http://twitter.com/popcornnews_ru" target="_blank"><img src="/i/button-twitter.png" alt="popcornnews.ru twitter"/></a></li> 
				<li class="social"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FPOPKORNNEWS%2F160837023942172&amp;layout=button_count&amp;show_faces=false&amp;width=140&amp;action=like&amp;font=trebuchet+ms&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:140px; height:21px;" allowTransparency="true"></iframe></li>
				<?if (!NO_DISPATCHER_FOR_YEAR_RESULTS) {?>
				<li><a href="/voting"><img alt="" src="/i/voting_for_head.jpg" /></a></li>
				<?}?>
			</ul> 
			<ul class="menu _news"> 
			    <?php
			        $main = ($page_type == "default" || $page_type == "page") ? ' class="active"':'';
			        print '<li'.$main.'><a href="/">главная</a></li>';

			        $oc = new VPA_table_news_columns();
			        $oc->get($columns);

			        foreach ($columns->results as $column) {
			            if(empty($main)) {
							
							if ($page_type == 'category')
							{
								$active = ($page_action == $column['alias']) ? ' class="active"' : '';
							}
							else
							{
								if (isset($_COOKIE['last_category'])) $active = ($_COOKIE['last_category'] == $column['id']) ? ' class="active"' : '';
							}
							
			            } else {
			                $active = '';
			            }
			            print '<li'.$active.'><a href="/category/'.$column['alias'].'" rel="nofollow" title="Рубрика новостей на тему '.strtolower($column['title']).'">'.strtolower($column['title']).'</a></li>';
			        }
			    ?>
				<li><noindex><a rel="nofollow" href="http://www.fittrends.ru/" title="Фитнес" target="_blank">фитнес</a></noindex></li> 
			</ul>
			<div id="header" class="twoCols">
				<div class="hhFeatures">
					<div class="logoHeader">
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
									<input type="hidden" name="back" value="<?=base64_encode($_SERVER['REQUEST_URI'])?>" />
									
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
								<img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['cuser']['avatara']))?>" />
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
									<input type="hidden" name="back" value="<?=base64_encode($_SERVER['REQUEST_URI'])?>" />
								</form>
								<a class="exit" href="#" onclick="as.$$('#logout').submit(); return false;">выход</a>
							</div>
						</div>
						<?}?>
					</div>
				</div>

				<div class="hbFeatures">
					<div class="tlContainer">
					   *
					</div>
					<div class="topHeadline">
						<h1>Итоги 2012 года попкорнnews</h1>
					</div>
					<noindex>
						<form class="search" action="/index.php" method="POST">
							<input type="hidden" name="type" value="news" />
							<input type="hidden" name="action" value="search" />
							<input type="text" class="text" name="word" />
							<input type="submit" class="submit" value="Найти" />
						</form>
					</noindex>
				</div>
			</div>
