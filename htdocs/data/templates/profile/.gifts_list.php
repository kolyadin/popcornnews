<?
$img=(empty($d['cuser']['avatara']) ? '/img/no_photo.jpg' : '/avatars/'.$d['cuser']['avatara']);
$img_small=(empty($d['cuser']['avatara']) ? '/img/no_photo_small.jpg' : '/avatars_small/'.$d['cuser']['avatara']);
$this->_render('inc_header',array('title'=>$d['cuser']['nick'],'header'=>'Отправить подарок','top_code'=>'<img src="'.$img.'" alt="'.htmlspecialchars($d['cuser']['nick']).'" class="avaProfile">','header_small'=>'Написать личное сообщение'));
$new_friends=$p['query']->get_num('user_friends',array('uid'=>$d['cuser']['id'],'confirmed'=>0));
?>
                <div id="contentWrapper" class="twoCols">
                <div id="content">
                                        <ul class="menu">
                                                <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
                                                <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/form">анкета</a></li>
                                                <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
                                                <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/groups/all">группы</a></li>
                                                <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">гостевая</a></li>
                                                <li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
						<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
						<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
                                        </ul>
						   <ul class="menu bLevel">
							   <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">принятые</a></li>
							   <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages/sent">отправленные</a></li>
							   <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages/new">написать новое</a></li>
							   <li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages/gift">подарки</a></li>
						   </ul>
					<div class="trackContainer mailTrack">
<!-- ********************************GIFTS***************************** -->
<style type="text/css">@import "/css/gifts.css";</style>
<script type="text/javascript" src="/js/as.new.js"></script>
<script type="text/javascript" src="/js/gifts.js"></script>
<script type="text/javascript" src="/js/AC_RunActiveContent.js"></script>
<div id="gifts_list">
   <div id="free">
	<span class="name">Бесплатные</span>
<?
/*
 * обычные подарки
 * бесплатные
 */
foreach ($d['gifts_free'] as $value){
?>
	<div class="gift" id="gift_div_<?=$i;?>">
	   <img src="/upload/<?=$value['src'];?>" alt="<?=$value['title'];?>" title="<?=$value['title'];?>" />
	   <div onclick="return send_gift(this, <?=$value['id'];?>)" class="send">Отправить</div>
	</div>
<?
}
?>
   </div>
   <div id="for_sales">
	<span class="name">Платные</span>
<?
/*
 * платные флеш подарки, с анимацией
 */
foreach ($d['gifts_for_sales'] as $value){
?>
	<div class="gift" id="swf_gift_div_<?=$i;?>">
	   <div class="amount"><?=$value['amount'];?> руб.</div>
	   <script language="javascript">
		if (AC_FL_RunContent == 0){
		   alert("This page requires AC_RunActiveContent.js.");
		}else{
		   AC_FL_RunContent(
			'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0',
			'width', '250',
			'height', '250',
			'src', '/upload/<?=substr($value['src'], 0, -4);?>',
			'quality', 'high',
			'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
			'align', 'middle',
			'play', 'true',
			'loop', 'true',
			'scale', 'showall',
			'wmode', 'window',
			'devicefont', 'false',
			'id', 'swf_gift_<?=$value['id'];?>',
			'bgcolor', '#ffffff',
			'name', 'swf_gift_<?=$value['id'];?>',
			'menu', 'true',
			'allowFullScreen', 'false',
			'allowScriptAccess','sameDomain',
			'movie', '/upload/<?=substr($value['src'], 0, -4);?>',
			'salign', ''
		   ); //end AC code
		}
	   </script>
	   <noscript>
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="250" height="250" id="swf_gift_<?=$value['id'];?>" align="middle">
		   <param name="allowScriptAccess" value="sameDomain" />
		   <param name="allowFullScreen" value="false" />
		   <param name="movie" value="/upload/<?=$value['src'];?>" />
		   <param name="quality" value="high" />
		   <param name="bgcolor" value="#ffffff" />
		   <embed src="/upload/<?=$value['src'];?>" quality="high" bgcolor="#ffffff" width="250" height="250" name="swf_gift_<?=$value['id'];?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>
	   </noscript>
	   <div onclick="return send_gift(this, <?=$value['id'];?>)" class="send">Отправить</div>
	</div>
<?
}
?>
   </div>
</div>
<!-- /////////////////////////GIFTS/////////////////-->
					</div>
				</div>

                        <?$this->_render('inc_right_column');?>
                        </div>
<?$this->_render('inc_footer');?>