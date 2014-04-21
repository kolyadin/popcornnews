<?php
$page = $_SERVER['SCRIPT_URI'];
$page2 = preg_replace('@(\/page\/\d+)\z@is','',$page);
?>

<noindex>
	<div class="share">
		<style type="text/css">
		.fb_share_count_inner {color:#000 !important;}
		</style>
		<span class="facebook">
			<a title="Опубликовать" rel="nofollow" name="fb_share" type="button_count" share_url="<?=$page2?>" href="http://www.facebook.com/sharer.php">Опубликовать</a>
			<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
		</span>
		<span class="vkontakte">
			<script type="text/javascript" src="http://vkontakte.ru/js/api/share.js?5" charset="windows-1251"></script>
			<script type="text/javascript"><!--
				document.write(VK.Share.button({url: "<?=str_replace('"', '\"', $page2)?>"},{type: "round", text: "Сохранить"}));
			--></script>
		</span>
		<span class="mail">
			<a rel="nofollow" class="mrc__share" type="button_count" href="http://connect.mail.ru/share?share_url=<?=urlencode($page2)?>">В Мой Мир</a>
			<script src="http://cdn.connect.mail.ru/js/share/2/share.js" type="text/javascript"></script>
		</span>
		<?php /* ?><span class="twitter">
			<a rel="nofollow" href="http://twitter.com/home?status=<?=iconv('WINDOWS-1251', 'UTF-8', 'Читаю ') . urlencode($page2)?>" title="Click to share this post on Twitter" target="_blank">
				<img src="http://twitter-badges.s3.amazonaws.com/twitter-b.png" alt="Share on Twitter" class="button_twitter"/>
			</a>
		</span>
		<?php */ ?>
		<span class="twitter">
			<a rel="nofollow" href="http://twitter.com/home?status=<?=iconv('WINDOWS-1251', 'UTF-8', 'Читаю ') . urlencode($page2)?>" title="Click to share this post on Twitter" target="_blank">
				<img src="/i/twitter_logo2.gif" alt="Share on Twitter" class="button_twitter"/>
			</a>
		</span>
		<span class="odnoklassniki">
			<a rel="nofollow" href="http://odnoklassniki.ru/dk?st.cmd=addShare&amp;st._surl=<?=urlencode($page2)?>" target="_blank"><img src="/i/odno.gif" alt="" width="40" height="40" /></a>
		</span>
		<?php /* ?>
		<span class="livejournal">
			<a rel="nofollow" href="#" onclick="try {var el = as.$$('#form_lj_post'); el.action = 'http://www.livejournal.com/update.bml'; el.method='post'; el.target = '_blank'; el.submit(); } catch (e) {} return false;"><img src="/i/lj.gif" alt="" width="53" height="40" /></a>
		</span>
		<?php */ ?>
		<span class="googleplusone" style="position:relative;top:1px;"><g:plusone></g:plusone></span>
	</div>
</noindex>