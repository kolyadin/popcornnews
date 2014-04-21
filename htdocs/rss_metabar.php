<?php
include 'inc/connect.php';
function check_code($text){
//порежем спецсимволы
  $text=str_replace('&',"&amp;",$text);
  $text=str_replace('"',"&quot;",$text);
  $text=str_replace('>',"&gt;",$text);
  $text=str_replace('<',"&lt;",$text);
  $text=str_replace("'","&apos;",$text);

  for($i=0;$i<strlen($text);$i++){
    if(ord($text[$i])<31 && ord($text[$i])!=10 && ord($text[$i])!=13 && ord($text[$i])!=9){
      $text[$i]=' ';
    }
  }
  return $text;
}

header("Content-type: text/xml;charset=windows-1251\n");
echo '<?xml version="1.0" encoding="windows-1251"?>' . "\n";
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>попкорнnews</title>
        <link>http://www.popcornnews.ru/?utm_source=metabar&amp;utm_medium=metabar&amp;utm_campaign=metabar</link>
        <description>popcornnews</description>
        <image>
            <url>http://www.popcornnews.ru/i/c1.low.gif</url>
            <title>попкорнnews</title>
            <link>http://www.popcornnews.ru/?utm_source=metabar&amp;utm_medium=metabar&amp;utm_campaign=metabar</link>
            <width>144</width>
            <height>16</height>
        </image>

        <?
        $cmd = sprintf(
	  'SELECT * FROM %s WHERE goods_id=2 and pole36="Yes" ORDER BY newsIntDate DESC, id DESC LIMIT 20',
	  $tbl_goods_
	);
        $line = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line)){
          $descr=trim(strip_tags($s["pole1"]));
          if (strlen($descr)>350) $descr = substr($descr,0,strpos($descr,' ',300)) . '...';
        ?>

        <item>
            <title><?=check_code($s['name']);?></title>
            <link>http://www.popcornnews.ru/news/<?=$s[0];?>?utm_source=metabar&amp;utm_medium=metabar&amp;utm_campaign=metabar</link>
            <guid>http://www.popcornnews.ru/news/<?=$s[0];?>?utm_source=metabar&amp;utm_medium=metabar&amp;utm_campaign=metabar</guid>
            <description><![CDATA[
            <?=check_code($descr);?>
            ]]></description>
            <pubDate><?=date("D, d M Y H:i:s", strtotime($s['pole3'] . ' ' . sprintf('%06u', $s['pole32'])));?> +0300</pubDate>
            <?if($s['pole5'] != ''){?>
            <media:content url="http://v1.popcorn-news.ru/upload/_80_80_80_<?=$s['pole5'];?>" type="image/jpeg"
                           medium="image" expression="sample" height="80" width="80" />
            <?}?>
        </item>

        <? }?>

    </channel>
</rss>