<?
include "/var/www/sites/popcornnews.ru/htdocs/inc/connect.php";
include "/var/www/sites/popcornnews.ru/htdocs/inc/func.php";
require_once '/var/www/sites/popcornnews.ru/htdocs/data/libs/vpa_mail.lib.php';


/*=============================================*/
/*==========Сгенерим тело письма===============*/
/*=============================================*/
function checklengthtext($text,$id){
	if(strlen($text)>300){
		$dotpos=strpos($text,'.',200);
		$outtext=substr($text,0,$dotpos+1);
		$ret=$outtext.' <a href="http://www.popcornnews.ru/news/'.$id.'">Читать дальше</a>';
	}
	else $ret=$text;
	return $ret;
}


function get_news_line($cmd)
{
 global $link;

 $line = mysql_query($cmd,$link);
 while($s=mysql_fetch_array($line))
  {
   if ($d!=substr($s["dat"],6,2))
    { 
     $d=substr($s["dat"],6,2);
     $text.='<b>'.$d.' '.get_month(substr($s["dat"],4,2)).' '.substr($s["dat"],0,4).'</b>';
    };

   $tags='';

   if ($s["pole7"]) $t1=get_file($s["pole7"]); else $t1='';
   if ($s["pole8"]) $t2=get_file($s["pole8"]); else $t2='';
   if ($s["pole9"]) $t3=get_file($s["pole9"]); else $t3='';
   if ($s["pole10"]) $t4=get_file($s["pole10"]); else $t4='';
   if ($s["pole17"]) $t5=get_file($s["pole17"]); else $t5='';
   if ($s["pole18"]) $t6=get_file($s["pole18"]); else $t6='';
   if ($s["pole19"]) $t7=get_file($s["pole19"]); else $t7='';
   if ($s["pole20"]) $t8=get_file($s["pole20"]); else $t8='';
   if ($s["pole21"]) $t9=get_file($s["pole21"]); else $t9='';
   if ($s["pole22"]) $t10=get_file($s["pole22"]); else $t10='';
   if ($s["pole23"]) $t11=get_file($s["pole23"]); else $t11='';
   if ($s["pole24"]) $t12=get_file($s["pole24"]); else $t12='';
   if ($s["pole25"]) $t13=get_file($s["pole25"]); else $t13='';
   if ($s["pole26"]) $t14=get_file($s["pole26"]); else $t14='';
   if ($s["pole27"]) $t15=get_file($s["pole27"]); else $t15='';
   if ($s["pole28"]) $t16=get_file($s["pole28"]); else $t16='';
   if ($s["pole29"]) $t17=get_file($s["pole29"]); else $t17='';
   if ($s["pole30"]) $t18=get_file($s["pole30"]); else $t18='';
   
   if ($t1[0]) $tags.='<a href="/tag/'.$s["pole7"].'"><strong>'.$t1["name"].'</strong></a>';
   if ($t2[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole8"].'"><strong>'.$t2["name"].'</strong></a>';
   if ($t3[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole9"].'"><strong>'.$t3["name"].'</strong></a>';
   if ($t4[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole10"].'"><strong>'.$t4["name"].'</strong></a>';
   if ($t5[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole17"].'"><strong>'.$t5["name"].'</strong></a>';
   if ($t6[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole18"].'"><strong>'.$t6["name"].'</strong></a>';
   if ($t7[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole19"].'"><strong>'.$t7["name"].'</strong></a>';
   if ($t8[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole20"].'"><strong>'.$t8["name"].'</strong></a>';
   if ($t9[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole21"].'"><strong>'.$t9["name"].'</strong></a>';
   if ($t10[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole22"].'"><strong>'.$t10["name"].'</strong></a>';
   if ($t11[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole23"].'"><strong>'.$t11["name"].'</strong></a>';
   if ($t12[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole24"].'"><strong>'.$t12["name"].'</strong></a>';
   if ($t13[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole25"].'"><strong>'.$t13["name"].'</strong></a>';
   if ($t14[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole26"].'"><strong>'.$t14["name"].'</strong></a>';
   if ($t15[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole27"].'"><strong>'.$t15["name"].'</strong></a>';
   if ($t16[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole28"].'"><strong>'.$t16["name"].'</strong></a>';
   if ($t17[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole29"].'"><strong>'.$t17["name"].'</strong></a>';
   if ($t18[0]) $tags.=($tags ? ', ' : '').'<a href="/tag/'.$s["pole30"].'"><strong>'.$t18["name"].'</strong></a>';

   //if ($s["pole2"]) $head='<a href="/news/'.$s[0].'">'.$s["name"].'</a>'; else  $head=$s["name"];

   $head='<a href="/news/'.$s[0].'">'.$s["name"].'</a>';


   
   $bodytext="";
   $text.='<h2><span>'.$head.'</span></h2><div class="text">'.checklengthtext($s["pole1"],$s[0]).'</div>';
   $text.='<div class="blockfooter">'.($tags ? '<p class="tags">Таги: '.$tags.'</p>' : '').'</div>';
   
   $text.='<br><br><br>';
  };
 return $text;

};

 
$spamd=date("Ymd");
//print $spamd;
//$list=get_news_line("SELECT * FROM $tbl_goods WHERE goods_id='2' AND regtime>'20070615000000'");
  $list=get_news_line("SELECT * FROM $tbl_goods_ WHERE goods_id=2 AND pole31='' ORDER BY dat DESC,id DESC");
  mysql_query("UPDATE $tbl_goods_ SET pole31='Yes', regtime=regtime");
print $list;


$msg=str_replace('href="/','href="http://www.popcornnews.ru/',$list);
$msg=str_replace('<img src="/','<img src="http://www.popcornnews.ru/',$msg);

$htmlmsg = <<<EOT
<html><head>
<title>попкорнnews</title>
<style type="text/css">
body, td {font-family: arial, helvetica, sans-serif; font-size:13px;}
a {color:#F70080;}
</style>
</head>
<body>
<table>
<tr>
<td valign="top" style="padding:10px; width:180px;">
<a href="http://www.popcornnews.ru/" target="_blank">
<img src="http://www.popcornnews.ru/i/c15.gif" alt="Popcornnews - самые последние новости и сплетни в мире кино" border="0" width="149" height="17" style="display:block;" />
</a>

<p style="font-size:12px;"><a style="color:#F70080" href="http://www.popcornnews.ru/" target="_blank">www.popcornnews.ru</a></p>
<br><br><br>
</td>
<td valign="top" style="background-color:#efefef; border: solid 1px #F70080; padding:20px;font-size:13px;">

$msg

</td>
</tr>
<tr><td colspan="2">
<hr>
<p>Чтобы отписаться от рассылки, перейдите по <a href="<#linkout>" target="_blank">ссылке</a></p>
<p>С уважением, коллектив сайта <a style="color:#F70080" href="http://www.popcornnews.ru/">Попкорнnews</a><br />
www.popcornnews.ru</p>
<p>По всем вопросам пишите - <a style="color:#F70080" href="mailto:info@popcornnews.ru">info@popcornnews.ru</a></p> 

</td></tr>

</table>

</body>
</html>
EOT;




/*==============================================*/
/*====Получим список получателей и разошлём===*/
/*==============================================*/
if ($list!=""){
$userlistline=mysql_query("SELECT * FROM $tbl_goods_ WHERE goods_id='7' AND pole1='yes'",$link);
$subj = "Рассылка сайта www.popcornnews.ru";
$mailtitle = "=?windows-1251?B?".base64_encode($subj)."?=";
while ($s=mysql_fetch_array($userlistline)){

	$msg=str_replace('<#linkout>','http://www.popcornnews.ru/popsub.php?pageid=2&amp;id='.$s[0].'&amp;code='.$s["pole2"],$htmlmsg);
	// $st=mail($s["name"],$mailtitle,$msg,$headers);
	$st = html_mime_mail::getInstance()->quick_send($s["name"],$mailtitle,$msg);
	if ($st==1) $st="Ok";
	else $st="Error";
	print $s["name"]."  ".$st."
	";
}
}
else print "empty";
 
?>