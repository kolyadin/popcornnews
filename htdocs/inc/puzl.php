<?
$id='http://v1.popcorn-news.ru/upload/'.$id;

?>
<HTML>
<HEAD>
    <TITLE>Кинопазлы от Попкорнnews</TITLE>
    <script type="text/javascript" src="/js/show_flash.js"></script>
</HEAD>
<BODY bgcolor="#FFFFFF">
<!-- URL's used in the movie-->
<!-- text used in the movie-->
<script> fl('<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" WIDTH=700 HEIGHT=600> <PARAM NAME=movie VALUE="http://v1.popcorn-news.ru/swf/pop.swf?pic=<? print $id; ?>"> <PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=#FFFFFF> <EMBED src="http://v1.popcorn-news.ru/swf/pop.swf?pic=<? print $id; ?>" quality=high bgcolor=#FFFFFF  WIDTH=700 HEIGHT=600 TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></EMBED></OBJECT>');</script>
</BODY>
</HTML>