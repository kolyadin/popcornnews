<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><? print $title; ?></title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<meta name="description" content="">
<meta name="keywords" content="">
<LINK rel="stylesheet" type="text/css" href="/styles/index.css">
<link rel="alternate" type="application/rss+xml" href="http://www.popcornnews.ru/rss.php" title="Ї®ЇЄ®а­news" />
<SCRIPT language="JavaScript" type="text/javascript"><!--
//--> </script>
<SCRIPT language="JavaScript" type="text/javascript">
        function selectimage(pic,nam){
                currentpic.src = pic;
                currentpic.alt = nam;
        }


</script>

</head>

<body onClick="self.parent.changeZI(window.name);" >

<div id="Main">
  <div id="PB">
    <div id="Left">
      <div id="Logo"><a href="./delcmt.php">попкорнnews<img src="/i/c1.gif" alt="попкорнnews"></a></div>
  
      <? print $text; ?>
  
      
  
    </div>
  
    <div id="Right">
      
      <? /* ?>
      <div class="search">
        <form method="post" action="/search/">
          <p><input type="text" class="cf" name="search" value='<? print $search; ?>'> <input type="submit" value="Найти" class="button"></p>
        </form>
      </div>
      <? */ ?>

      

      <div class="rb">
      <!--
        <div style="position:absolute;margin-top:0px; margin-left:165"><a href="/rss.php"><img src="/i/feed.gif" width="16" height="16" style="float:left;"></a>&nbsp;<a href="/rss.php" style="color:#F70080"><span>RSS поток</span></a></div>  
        -->
        <div class="top">
          <div><img src="/i/c3.gif" alt="" width="91" height="17"></div>
          <ul><? print $top_corn; ?></ul>
        </div>
      </div>

<?
/*
print '<div class="subscr" style="margin-top:-20px;">
        <img src="/i/c13.gif" alt="подписка">
        <form method="post" action="/popsub.php" name="subscrform">

         Свежие новости из мира кино!<br>'.$errortext.'
         <input type="text" class="cf" name="mail" value=""> <input type="submit" value="Подписаться" class="button" onclick="subscrform.submit()"><br>
         <input id="radio-sub" name="subscribe" value="yes" checked="checked" type="radio">
         <label for="radio-sub"><strong>подписаться</strong></label>
         <input id="radio-unsub" name="subscribe" value="no" type="radio"> 
         <label for="radio-unsub">отписаться</label>
        </form>
      </div>
';
*/
?>
<?
/*
if($ip=='84.204.69.86'){
print'
      <div class="rb" style="margin-bottom:10px;">
        <div id="premier">';

$purum=file_get_contents('/var/www/sites/kino.traf.spb.ru/htdocs/cache/msk/right_releases.html');

$purum=str_replace('href="/','target="_blank" href="http://www.kinoafisha.msk.ru/',$purum);
$purum=str_replace('src="/','src="http://www.kinoafisha.msk.ru/',$purum);
$purum=str_replace('<div class="rh"><h1>Премьеры</h1></div>','<div><img src="/i/c20.gif" alt="Премьеры"></div>',$purum);
$purum=str_replace('<strong>          </strong>','',$purum);
print $purum;

print'
        </div>
      </div>';


}
*/
/*
?>



<?=$birthday;?>
      <div class="rb">
        <div class="person">
          <div><img src="/i/c4.gif" alt="" width="98" height="18"></div>
          <p><? print $clouds; ?></p>
        </div>
      </div>

      <div class="rb">
        <div class="marchive">
          <div><img src="/i/c2.gif" alt="" width="66" height="18"></div>
          <? print $right_arch; ?>
        </div>
      </div>

      <div class="rb">
        <div class="person">
          <div><img src="/i/c19.gif" alt="Мультимедиа" width="153" height="17"></div>
          <p>
                        <a href="/oboi" style="font-size: 15px;">Обои</a> <br>
                        <a href="/puzls" style="font-size: 15px;">Пазлы</a>
           </p>
         </div>
      </div>

      <div class="rb">
        <div class="person">
          <div><img src="/i/c12.gif" alt="" width="143" height="17"></div>
          <p>
            <a href="http://kinoafisha.info/" style="font-size: 15px;" target="_blank">Киноафиша России</a>
            <a href="http://kinoafisha.msk.ru/" style="font-size: 15px;" target="_blank">Киноафиша Москвы</a><br>
            <a href="http://kinoafisha.spb.ru/" style="font-size: 15px;" target="_blank">Киноафиша Петербурга</a>
           </p>
        </div>
      </div>

      <div class="rb">
        <!-- MarketNews Start -->
        <div id="MarketGid1495"><center>
        <a href="http://marketgid.com" target="_blank">Загрузка...</a>
        </center></div>
        <!-- MarketNews End --> 
      </div>
 
 

   </div>
  </div>

  <div id="Footer">
    <div class="logo"><img src="/i/c6.gif" alt="" height="13" width="99"> <span style="margin-left:270px;">Контактный e-mail: <a href="mailto:editor@popcornnews.ru" style="margin-left:0px">editor@popcornnews.ru</a></span></div>
    <div class="links" style="margin-top:20px; margin-right:200px">

<a href="http://top100.rambler.ru/top100/">
<img src="http://counter.rambler.ru/top100.cnt?1113588" alt="" width=1 height=1 border=0></a>

<a href="http://top100.rambler.ru/top100/">
<img src="http://top100-images.rambler.ru/top100/w5.gif" alt="Rambler's Top100" width=88 height=31 border=0></a>


    </div>
  </div>
<? */ ?>  
</div>
<script type="text/javascript" src="http://mg.dt00.net/js/p/o/popcornnews.ru.i1.js"></script> 
</body>
</html>