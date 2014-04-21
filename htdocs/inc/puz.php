<?
include "connect.php";

$id=mysql_real_escape_string($id);
$text="";
$query=mysql_query("SELECT * FROM $tbl_goods_ WHERE goods_id=8 AND (pole3=$id OR pole4=$id)");
while($s=mysql_fetch_array($query)){
	$text.= '<a href="#" onClick=open_puz(\''.$s["pole1"].'\')><img src="/upload/'.$s["pole2"].'" style="float:left;margin-left:38px;margin-bottom:30px;"></a>';
}

$nq=mysql_query("SELECT name FROM $tbl_goods_ WHERE id=$id AND goods_id=3");
$nrq=mysql_fetch_row($nq);
$name=$nrq[0];
?>
<HTML>
<HEAD>
<TITLE>Кинопазлы - <?=$name;?></TITLE>
<script>
function open_puz(n)
{
 var cfg = "height=600,width=700,scrollbars=no,toolbar=no,menubar=no,resizable=yes,location=no,status=no";
 var OpenWindow=window.open("/inc/puzl.php?id=../upload/"+n, "puznewwin", config=cfg);
}
</script>
<style>
img {border:0px;}
</style>
</HEAD>
<BODY bgcolor="#FFFFFF">
<a href="http://www.popcornnews.ru" target="_blank"><img src="/i/c1.gif" style="border:0px;"></a><br>
<h1><?=$name;?></h1>

<?=$text;?>
</BODY>
</HTML>