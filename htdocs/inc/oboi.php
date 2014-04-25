<?

$id=intval($id);
$p=intval($p);
$k=intval($k);
$text="";
$img='';
include "connect.php";

if(!$k) {
	$res=mysql_query("SELECT * FROM $tbl_goods_ WHERE goods_id=9 AND id=$id",$link);
	$s=mysql_fetch_array($res);
	if($p==1 && $s['pole6']) $img="/upload/".$s['pole6'];//1600x1200
	else if($p==2 && $s['pole3']) $img="/upload/".$s['pole3'];//1280x1024
	else if($p==3 && $s['pole4']) $img="/upload/".$s['pole4'];//1024x768
//	if(!$img) $img='/upload/'.($s["pole4"] ? $s['pole4'] : ($s['pole3'] ? $s['pole3'] : $s['pole6']));
} else {
	$kino_goods_="kinoafisha_v2_goods_";
	$kino_hostname=":/tmp/mysql-kino.sock";
	$kino_login="sky";
	$kino_pass="uGrs7u8rN";
	$kino_dbname="kinoafisha";
	@$kino_link = mysql_connect($kino_hostname, $kino_login, $kino_pass); 
	mysql_select_db($kino_dbname,$kino_link);
	$res=mysql_query("SELECT * FROM $kino_goods_ WHERE goods_id=247 AND page_id=2 AND id=".$id, $kino_link);
	$s=mysql_fetch_array($res);
	if($p==1 && $s['pole5']) $img="/kinoupload/".$s['pole5'];//1600x1200
	else if($p==3 && $s['pole4']) $img="/kinoupload/".$s['pole4'];//1280x1024
	else if($p==2 && $s['pole3']) $img="/kinoupload/".$s['pole3'];//1024x768
}
$name=$s['name'];
$text=($img)?
	'<img src="'.$img.'" alt="'.$name.'">'
	:'<p>Обои не найдены</p>';
?>
<HTML>
<HEAD>
<TITLE>Обои <?=$name;?>, скачать обои на рабочий стол <?=$name;?> - попкорнnews</TITLE>
<style>
body {margin:0px;padding:0px;}
img {border:0px;}
</style>
</HEAD>
<BODY bgcolor="#FFFFFF">
<?=$text;?>
</BODY>
</HTML>