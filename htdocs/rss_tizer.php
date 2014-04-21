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

header("Content-type: text/xml\n");
echo '<?xml version="1.0" encoding="windows-1251"?>' . "\n";
?>
<rss version="2.0">
    <channel>
        <title>попкорнnews</title>
        <link>http://www.popcornnews.ru</link>
        <description>popcornnews</description>
        <image>
            <url>http://www.popcornnews.ru/i/c1.gif</url>
            <title>попкорнnews</title>
            <link>http://www.popcornnews.ru</link>
            <width>385</width>
            <height>44</height>
        </image>

        <?
        $cmd = "
SELECT
  n.*
FROM popconnews_goods_ AS n
  INNER JOIN new_views AS v
    ON (v.new_id = n.id)
  INNER JOIN (
               SELECT
                 count(id) AS cnt,
                 news_id
               FROM pn_comments_news
               GROUP BY news_id
             ) AS c
    ON (c.news_id = n.id)
WHERE n.goods_id = 2 AND v.num > 2000 AND c.cnt > 30
ORDER BY n.newsIntDate DESC, n.id DESC
LIMIT 20";
        $line = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line)){
          $descr=trim(strip_tags($s["pole1"]));
          if (strlen($descr)>350) $descr = substr($descr,0,strpos($descr,' ',300)) . '...';
        ?>

        <item>
            <title><?=check_code($s['name']);?></title>
            <link>http://www.popcornnews.ru/news/<?=$s[0];?></link>
            <description><![CDATA[
            <?=check_code($descr);?>
            <?if($s['pole5'] != ''){?><br /><img src="http://v1.popcorn-news.ru/upload/_500_600_80_<?=$s['pole5'];?>" /><?}?>
            ]]></description>
            <pubDate><?=date("D, d M Y H:i:s", strtotime($s['pole3'] . ' ' . sprintf('%06u', $s['pole32'])));?> +0300</pubDate>
            <full-text><?=check_code($s['pole1'] . check_code($s['pole2']) . '<br><br><a href="http://www.popcornnews.ru">попкорнnews</a>'); ?></full-text>
        </item>

        <? }?>

    </channel>
</rss>