<?php 

$page_type = $d['page_type'];

$page_action = $d['page_action'];

if (!in_array($page_type,array('category','news')))
{
	setcookie('last_category', 0, time()-3600, '/');
    unset($_COOKIE['last_category']);    
}

/*GA users returns*/
$doGA = false;
if(!isset($_COOKIE['chret_a'])) {
    setcookie('chret_a', 1, strtotime('+1 month'), '/', $this->domain);
    setcookie('chret_b', true, 0, '/', $this->domain);
} else {
    if(!isset($_COOKIE['chret_b']) && $_COOKIE['chret_a'] == 1) {
        $doGA = true;
        setcookie('chret_a', 2, strtotime('+1 month'), '/', $this->domain);
    }
}

//counter
/*if(false) {
	if(!is_null($d['cuser'])) {
		$rcount = $p['memcache']->get('registered_user_counter');
		if($rcount === FALSE) {
			$rcount = 0;
		}
		$rcount++;
		//$rcount = 0;
		$p['memcache']->set('registered_user_counter', $rcount, 0);		
	}
}*/

//var_dump($_COOKIE);


$show_popitas_game = 0;
$show_popitas_magic = 0;

$show_fullscreen = false;//!isset($_COOKIE['show_fs']);

$isMainPage = $page_type == "default" || $page_type == "page";
$isMainPageWithoutPages = $page_type == "default";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="yandex-verification" content="662fe4b66fd69297" />
		<meta name="yandex-verification" content="59a8cfdaa947c3ea" />
		<meta name="google-site-verification" content="kYTbrmi7LsFmpAFI00SBCZ-iuJrMabNSVY3nslcLHas" />
		
		<title><?=(isset($d['title']) ? htmlspecialchars($d['title'], ENT_IGNORE, 'cp1251', false) : null)?> - popcornnews</title>
		<?if (isset($d['meta']) && is_array($d['meta'])) {?>
		<?if (isset($d['meta']['description'])) {?><meta name="description" content="<?=htmlspecialchars($d['meta']['description'])?>" /><?}?>
		<?if (isset($d['meta']['keywords'])) {?><meta name="keywords" content="<?=htmlspecialchars($d['meta']['keywords'])?>" /><?}?>
		<?}?>
        <?php /*if(isset($d['chrome_next'])) { ?>
        <link rel="prefetch" href="<?=$d['chrome_next'];?>" />
        <link rel="prerender" href="<?=$d['chrome_next'];?>" />
        <?php }*/ ?>

		<link rel="stylesheet" type="text/css" href="<?=$this->getStaticPath('/css/main_new.css?v=1.5.1')?>" />
		<!--[if lte IE 7]>
		<link rel="stylesheet" href="<?=$this->getStaticPath('/css/ie7.css?d=04.08.11')?>" />
		<![endif]-->
		<!--[if lte IE 6]>
		<link rel="stylesheet" href="<?=$this->getStaticPath('/css/ie6.css?d=04.08.11')?>" />
		<![endif]-->
		<?
		if (isset($d['css'])) {
			if (!is_array($d['css'])) $d['css'] = array($d['css']);
			foreach ($d['css'] as $css) {
				printf('<link rel="stylesheet" type="text/css" href="%s" />' . "\n", $this->getStaticPath('/css/' . $css));
			}
		}
		?>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/swfobject.js?d=04.08.11')?>"></script>
		<script type="text/javascript">AC_FL_RunContent = 0;</script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/AC_RunActiveContent.js?d=04.08.11')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/as.js?d=04.08.11')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/aspatch.js?d=04.08.11')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/gallery.js?d=20.02.12')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/main_new.js?3')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/vpa_ajax.js?d=04.08.11')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/vpa_logic.js?d=04.08.11')?>"></script>
		<script type="text/javascript" src="<?=$this->getStaticPath('/js/jquery/jquery.js?d=17.08.12')?>"></script>
		<?php if($show_fullscreen) { ?>
		<link rel="stylesheet" href="<?=$this->getStaticPath('/css/fullscreen.css?d=20.08.12')?>" />
		
		<script type="text/javascript">
		$(document).ready(function() {
			_gaq.push(['_trackEvent', 'global', 'fullscreen_social', 'show']);
			
			$('body').append('<div class="fs_bg">&nbsp;</div>');
			$('body').append('<div class="fs_fg">'+
				    '<a class="fs_close" href="#">&nbsp;</a>'+
				    '<span class="fs_title">Присоединяйтесь к нам в социальных сетях</span>'+
				    '<span class="fs_text">Последние новости и обновления в твоей любимой сети!</span>'+
				    '<div class="fs_share">'+
				    '<a href="http://vk.com/app1881378" onclick="_gaq.push([\'_trackEvent\', \'global\', \'fullscreen_social\', \'click_vk\']);" class="vk" target="_blank">&nbsp;</a>'+
				    '<a href="http://www.facebook.com/pages/%D0%9F%D0%9E%D0%9F%D0%9A%D0%9E%D0%A0%D0%9DNEWS/160837023942172" onclick="_gaq.push([\'_trackEvent\', \'global\', \'fullscreen_social\', \'click_facebook\']);" class="fb" target="_blank">&nbsp;</a>'+
				    '<a href="https://twitter.com/popcornnews_ru" onclick="_gaq.push([\'_trackEvent\', \'global\', \'fullscreen_social\', \'click_twitter\']);" class="tw" target="_blank">&nbsp;</a>'+
				    '</div>'+
				    '<div class="fs_u">&nbsp;</div>'+
				    '</div>');
			$('div.fs_bg').css('opacity', 0.9);

			$('a.fs_close').click(function(){
				$('div.fs_fg').remove();
				$('div.fs_bg').remove();
				return false;
			});

			$('div.fs_share a').click(function(){
				$('div.fs_fg').remove();
				$('div.fs_bg').remove();	
			});
		});
		</script>
		<?php } ?>
		<?
		if (isset($d['js'])) {
			if (!is_array($d['js'])) $d['js'] = array($d['js']);
			foreach ($d['js'] as $js) {
				printf('<script type="text/javascript" src="%s"></script>' . "\n", $this->getStaticPath('/js/' . $js));
			}
		}
		?>		
		<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.popcornnews.ru/rss.php?1" />
		<?
		    if(isset($d['canonical_link'])) 
		        if(!is_null($d['canonical_link'])) {
		            print('<link rel="canonical" href="'.$d['canonical_link'].'" />');
		        }
		?>
		<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
		  {lang: 'ru'}
		</script>
		
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-21667993-7']);
			_gaq.push(['_addOrganic', 'mail.ru', 'q']);
			_gaq.push(['_addOrganic', 'go.mail.ru', 'q']);
			_gaq.push(['_trackPageview']);
			_gaq.push(['_trackPageLoadTime']);

			<?php
			    if($doGA) {
			        echo "//_gaq.push(['_trackEvent','Return','true']);";
			    }
			?>

			
			(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
		<?php /*
		<!-- Брендирование -->
		<link rel="stylesheet" type="text/css" href="http://v1.popcorn-news.ru/branding/mexica/branding.css" />
		<!-- /Брендирование -->
		*/ ?>
	</head>

	<body>
	<?php
/*if($d['cuser']['id'] == 83368) {
	echo $rcount;
}*/
?>
	
<!--AdFox START-->
<!--traffic.spb-->
<!--Площадка: popcornnews.ru / * / *-->
<!--Тип баннера: Fullscreen-->
<!--Расположение: <верх страницы>-->
<script type="text/javascript">
<!--
if (typeof(pr) == 'undefined') { var pr = Math.floor(Math.random() * 1000000); }
if (typeof(document.referrer) != 'undefined') {
  if (typeof(afReferrer) == 'undefined') {
    afReferrer = escape(document.referrer);
  }
} else {
  afReferrer = '';
}
var addate = new Date(); 
document.write('<scr' + 'ipt type="text/javascript" src="http://ads.adfox.ru/4067/prepareCode?pp=g&amp;ps=itw&amp;p2=ejao&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + pr +'&amp;pt=b&amp;pd=' + addate.getDate() + '&amp;pw=' + addate.getDay() + '&amp;pv=' + addate.getHours() + '&amp;prr=' + afReferrer + '"><\/scr' + 'ipt>');
// -->
</script>
<!--AdFox END-->



<?php /*
<!-- Брендирование -->
		<div id="branding" class="branding">
			<img class="_1" src="http://v1.popcorn-news.ru/branding/mexica/f2.gif" alt="" />
		</div>
		<script type="text/javascript">
			document.write('<a onclick="_gaq.push([\'_trackEvent\',\'branding\',\'mexica\',\'click\']);" class="branding_logo" href="http://ad.adriver.ru/cgi-bin/click.cgi?sid=1&bt=21&ad=356631&pid=922589&bid=2001751&bn=2001751&rnd='+new Date().getTime()+'" target="_blank"></a>');
		
			var swf = new SWFObject('http://v1.popcorn-news.ru/branding/mexica/banner.swf', "branding_swf", "1000", "150", "8");
			swf.addParam("quality", "high");
			//swf.addParam("wmode", "transparent");
			swf.addParam("allowScriptAccess", "always");
			swf.addVariable("link1", 'http://ad.adriver.ru/cgi-bin/click.cgi?sid=1&bt=21&ad=356631&pid=922589&bid=2001751&bn=2001751&rnd='+new Date().getTime());
			swf.write("branding");
			
			document.write('<img class="branding_px" src="http://ad.adriver.ru/cgi-bin/rle.cgi?sid=1&bt=21&ad=356631&pid=922589&bid=2001751&bn=2001751&rnd='+new Date().getTime()+'" alt="" /> ');
		</script>		
	<!-- /Брендирование -->
*/ 

if ($show_popitas_game)
{
print <<<EOL
<script type="text/javascript">
var brandingParams={path:'http://v1.popcorn-news.ru/branding/popitas/game.swf', link1:'http://b.traf.spb.ru/b_click2.php?bid=191424265', zeroPx:'http://b.traf.spb.ru/b_show.php?bid=191424265&img=1'};
_gaq.push(['_trackEvent','POPITAS_GAME','true']);
</script>
EOL;
}
else if ($show_popitas_magic)
{
print <<<EOL
<script type="text/javascript">
var brandingParams={path:'http://v1.popcorn-news.ru/branding/popitas/magic.swf', link1:'http://b.traf.spb.ru/b_click2.php?bid=191424264', zeroPx:'http://b.traf.spb.ru/b_show.php?bid=191424264&img=1'};
_gaq.push(['_trackEvent','POPITAS_MAGIC','true']);
</script>
EOL;
}

if ($show_popitas_game || $show_popitas_magic)
{
	print '<script type="text/javascript" src="http://v1.popcorn-news.ru/branding/popitas/branding.js"></script>';
}
?>
	
<?php if ($page_type == 'voting') { ?>

<?php $d['top_code'] = '*'; ?>
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

<div class="popup-bg" id="popup-bg"></div>

<?php } ?>
	
<?=(isset($d['links']) ? $d['links'] : '');?>
		<div style="padding-left:15px;" id="wrapper"<?=(isset($d['cuser']) && !empty($d['cuser'])) ? ' class="registered"' : ''?>>
<?
$u_online = new VPA_online;
$user_online = $u_online->get_count_online_users()+rand(1,2);
?>
			<ul class="menu">
			<?php
                $topMenuNoFollow = $isMainPageWithoutPages ? '' : ' rel="nofollow"';
			    $menu = array(
			    'tags' => '<a href="/persons" title="персоны"'.$topMenuNoFollow.'>персоны</a>',
			    'meet' => '<a href="/meet" title="пары"'.$topMenuNoFollow.'>пары</a>',
			    'kids' => '<a href="/kids" title="дети"'.$topMenuNoFollow.'>дети</a>',
			    'community' => '<a href="/community/groups" title="сообщество"'.$topMenuNoFollow.'>сообщество</a>',
			    'yourstyle' => '<a href="/yourstyle"'.$topMenuNoFollow.'>your.style</a>',
			    'users' => '<a href="/users" title="пользователи"'.$topMenuNoFollow.'>пользователи</a> <span class="online">'.$user_online.'</span>',
			    'rules' => '<a href="/rules" title="правила"'.$topMenuNoFollow.'>правила</a>',
			    'faq' => '<a href="/faq" title="faq"'.$topMenuNoFollow.'>FAQ</a>'
			    );
			    
			    $paths = array(
			    'tags' => array('persons'),
			    'kids' => array('kids', 'kid'),
			    'users' => array('users', 'users_top', 'users_online', 'users_city', 'profile'),			    
			    );

                if(!$isMainPageWithoutPages) {
                    print '<noindex>';
                }

			    foreach ($menu as $type => $item) {
			        $active = ($page_type == $type) ? ' class="active"':'';
			        if(array_key_exists($type, $paths)) {
			            $active = in_array($page_type, $paths[$type]) ?  ' class="active"':'';
			        }
			        if($type == 'yourstyle') {
			            print '<li class="link_promo">'.$item.'</li>';
			        } else {
			            print '<li'.$active.'>'.$item.'</li>';
			        }
			    }
			    $active = '';
                if(!$isMainPageWithoutPages) {
                    print '</noindex>';
                }
			?>			 			
				<li class="rss"><a href="/rss.php?1"><img src="/i/100px-Feed-icon.png" alt="RSS"/></a></li> 
				<li class="social"><a rel="nofollow" href="http://vkontakte.ru/club10361506" target="_blank"><img src="/i/button-vk3.png" alt="popcornnews.ru ВКонтакте"/></a></li> 
				<li class="social"><a rel="nofollow" href="http://twitter.com/popcornnews_ru" target="_blank"><img src="/i/button-twitter.png" alt="popcornnews.ru twitter"/></a></li> 
				<li class="social"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FPOPKORNNEWS%2F160837023942172&amp;layout=button_count&amp;show_faces=false&amp;width=140&amp;action=like&amp;font=trebuchet+ms&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:140px; height:21px;" allowTransparency="true"></iframe></li> 
				<?/*if (!NO_DISPATCHER_FOR_YEAR_RESULTS) {?>
				<li><a href="/voting"><img alt="" src="/i/voting_for_head.jpg" /></a></li>
				<?}*/?>
			</ul> 
			<ul class="menu2">
                <?if (!NO_DISPATCHER_FOR_YEAR_RESULTS) {?>
                <li class="active"><a href="/voting">итоги года</a></li>
                <?}?>
				<!--<li><a style="background:url(http://www.popcornnews.ru/i/mmenu_bg_tmp.gif) top left repeat-x;padding:0 5px 0 5px;" href="/voting">итоги года</a></li>-->
			    <?php
			        $main = $isMainPage ? ' class="active"':'';
                    $mainPageLinkRel = $isMainPageWithoutPages ? 'rel="nofollow"' : '';
                    $columnsLinkRel = !$isMainPageWithoutPages ? 'rel="nofollow"' : '';
			        print '<li'.$main.'><a href="/"'.$mainPageLinkRel.'>главная</a></li>';

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
			            echo '<li'.$active.'><a href="/category/'.$column['alias'].'" '.$columnsLinkRel.' title="Рубрика новостей на тему '.strtolower($column['title']).'">'.strtolower($column['title']).'</a></li>';
			        }
			    ?>
                <?php
                $photoArticleLinkActive = ($page_type == "photo-articles") || ($page_type == "photo-article");
                echo '<li'.($photoArticleLinkActive ? ' class="active"':'').'><a href="/photo-articles" rel="nofollow">фотогалереи</a></li>';
                ?>
				<li class="list_popup">
					<a rel="nofollow" title="Киноафиша">киноафиша</a>
					<div class="shadow"></div>
					<div class="list">

						<noindex>
							<div class="container">
								<ul class="_1">
									<li>
										<a rel="nofollow" target="_blank" href="http://www.kinoafisha.msk.ru/">Москва</a>
										<a rel="nofollow" target="_blank" style="margin-bottom:21px;" href="http://www.kinoafisha.spb.ru/">Санкт-Петербург</a>
										<a rel="nofollow" target="_blank" href="http://www.vl.kinoafisha.info/">Владивосток</a>

										<a rel="nofollow" target="_blank" href="http://www.vlg.kinoafisha.info/">Волгоград</a>
										<a rel="nofollow" target="_blank" href="http://www.voronezh.kinoafisha.info/">Воронеж</a>
										<a rel="nofollow" target="_blank" href="http://www.ekat.kinoafisha.info/">Екатеринбург</a>
										<a rel="nofollow" target="_blank" href="http://www.irk.kinoafisha.info/">Иркутск</a>
										<a rel="nofollow" target="_blank" href="http://www.kazan.kinoafisha.info/">Казань</a>
										<a rel="nofollow" target="_blank" href="http://www.kaliningrad.kinoafisha.info/">Калиниград</a>

									</li>
									<li>
										<a rel="nofollow" target="_blank" href="http://www.kr.kinoafisha.info/">Краснодар</a>
										<a rel="nofollow" target="_blank" href="http://www.krsk.kinoafisha.info/">Красноярск</a>
										<a rel="nofollow" target="_blank" href="http://www.lipetsk.kinoafisha.info/">Липецк</a>
										<a rel="nofollow" target="_blank" href="http://www.murmansk.kinoafisha.info/">Мурманск</a>
										<a rel="nofollow" target="_blank" href="http://www.nn.kinoafisha.info/">Нижний Новгород</a>

										<a rel="nofollow" target="_blank" href="http://www.nsk.kinoafisha.info/">Новосибирск</a>
										<a rel="nofollow" target="_blank" href="http://www.omsk.kinoafisha.info/">Омск</a>
										<a rel="nofollow" target="_blank" href="http://www.perm.kinoafisha.info/">Пермь</a>
										<a rel="nofollow" target="_blank" href="http://www.petrozavodsk.kinoafisha.info/">Петрозаводск</a>
										<a rel="nofollow" target="_blank" href="http://www.rnd.kinoafisha.info/">Ростов-на-Дону</a>
									</li>

									<li>
										<a rel="nofollow" target="_blank" href="http://www.smr.kinoafisha.info/">Самара</a>
										<a rel="nofollow" target="_blank" href="http://www.srt.kinoafisha.info/">Саратов</a>
										<a rel="nofollow" target="_blank" href="http://www.sochi.kinoafisha.info/">Сочи</a>
										<a rel="nofollow" target="_blank" href="http://www.stavropol.kinoafisha.info/">Ставрополь</a>
										<a rel="nofollow" target="_blank" href="http://www.tula.kinoafisha.info/">Тула</a>

										<a rel="nofollow" target="_blank" href="http://www.tmn.kinoafisha.info/">Тюмень</a>
										<a rel="nofollow" target="_blank" href="http://www.ufa.kinoafisha.info/">Уфа</a>
										<a rel="nofollow" target="_blank" href="http://www.chel.kinoafisha.info/">Челябинск</a>
										<a rel="nofollow" target="_blank" href="http://www.yaroslavl.kinoafisha.info/">Ярославль</a>
									</li>
								</ul>
							</div>

						</noindex>
					</div>
				</li>
			</ul>
			
			<div style="padding-top:10px;">
			<!--AdFox START-->
			<!--traffic.spb-->
			<!--Площадка: <разовые размещения> / <МТС_ШПД+ТВ_октябрь-ноябрь_Q3-Q4_2012> / 980x90popcorn-->
			<!--Категория: <не задана>-->
			<!--Рекламная кампания: МТС_ШПД+ТВ_октябрь-ноябрь_Q3-Q4_2012-->
			<!--Тип баннера: 980x90popcorn-->
			<script type="text/javascript">
			<!--
			if (typeof(pr) == 'undefined') { var pr = Math.floor(Math.random() * 1000000); }
			if (typeof(document.referrer) != 'undefined') {
			  if (typeof(afReferrer) == 'undefined') {
				afReferrer = escape(document.referrer);
			  }
			} else {
			  afReferrer = '';
			}
			var addate = new Date(); 
			document.write('<scr' + 'ipt type="text/javascript" src="http://ads.adfox.ru/4067/prepareCode?p1=biyfk&amp;p2=epxl&amp;pct=a&amp;pfc=mtse&amp;pfb=a&amp;pr=' + pr +'&amp;pt=b&amp;pd=' + addate.getDate() + '&amp;pw=' + addate.getDay() + '&amp;pv=' + addate.getHours() + '&amp;prr=' + afReferrer + '"><\/scr' + 'ipt>');
			// -->
			</script>
			<!--AdFox END-->
			</div>
			
			<div id="header" class="twoCols">
				<div class="hhFeatures">
					<div class="logoHeader">
						<a class="logo" href="/">
							<img src="/i/logo.png" alt="" />
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
					<div class="tlContainer<?=($d['isBlog']?' tagIconLarge':'');?>">
						<?=$d['top_code'];?>
					</div>
					<?php if($d['isBlog'] && $page_type == 'news') { ?>
					<div class="topHeadline" style="margin-top: 0;">
						<span>Записки Мидды Пипплтон</span>
						<h1><?=$d['header']?></h1>
					<?php } else if ($page_type == 'voting') { ?>
						<div class="topHeadline" style="margin-top: 20px;">
						<h1><?=$d['header'];?></h1>
					<?php } else { ?>
					<div class="topHeadline<?=(isset($d['header_class']) ? ' '.$d['header_class'] : '');?>">					
						<h1><?=$d['header']?></h1>
					<?php } ?>
						<?if (isset($d['rewrite'][0]) && ($d['rewrite'][0] == 'user' || $d['rewrite'][0] == 'profile')) {?>
						<?=$this->_render('inc_statuses', array('my' => $d['rewrite'][1] == $d['cuser']['id']));?>
						<?} elseif (!empty($d['header_small'])) {?>
						<span><?=$d['header_small']?></span>
						<?}?>
					</div>
					<noindex>
						<form class="search" action="/news/search/">
							<input type="text" class="text" name="word"<?if (isset($d['search_word'])){?> value="<?=$d['search_word']?>"<?}?> />
							<input type="submit" class="submit" value="Найти" />
						</form>
					</noindex>
				</div>
			</div>
