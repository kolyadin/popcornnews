<?
// Викторины - запись ответа

include ("connect.php");




$host = preg_replace('/^[Ww][Ww][Ww]\./', '',preg_replace('/:[0-9]*$/', '', $_SERVER['HTTP_HOST']));
session_set_cookie_params(24*3600,'/', $host);
session_name("SESSID");
session_start();
session_write_close();

$user=$_SESSION["__session_var__sess_user"];
$user_id=$user["id"];
$nick=$user["nick"];



$page_id=2;
$quiz_goodsid=17;
$questions_goodsid=18;
$answers_goodsid=19;


$dat=date("Ymd");
$back="/quiz/".$id."/";

$TT1=htmlspecialchars($_POST["TT1"]);
$TT4=htmlspecialchars($_POST["TT4"]);
$TT5=htmlspecialchars($_POST["TT5"]);

$result=mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND (round(pole4)<=$dat) AND (round(pole5)>=$dat) AND id=$id AND pole6=''",$link);
if(mysql_num_rows($result)>0)
{
 $row=mysql_fetch_object($result);

 if($row->pole7=='') $cmd="SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole1='$user_id' AND pole2='$id'";
  else $cmd="SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole7='$ip' and pole2='$id'";
 $result2=mysql_query($cmd,$link);

 if($row->pole7!='') $nick=$TT1;

 if($nick && mysql_num_rows($result2)==0) // если этот конкурс вообще существует
 {
  $correct="Yes";
  $pole3='';
  $result3=mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$questions_goodsid AND pole1='$id' ORDER BY id",$link);
  while($row2=mysql_fetch_object($result3)) // пройдемся по вопросам конкурса
  {
   if($row2->pole15!=$_POST["T".$row2->id]) $correct="No"; // и посмотрим, правильно ли он ответил.
   $pole3=$pole3.$_POST["T".$row2->id]."-";
  }
  if ($nick) $name=$nick;
  $pole1=$user_id; // пользователь
  $pole2=$id; // id конкурса
  $pole3=trim($pole3," -");
  $pole4=$correct;
  if ($user["email"]) $pole5=$user["email"]; else $pole5=$TT4;
  $pole6=$TT5;
  $pole7=getenv("REMOTE_ADDR");

  mysql_query("INSERT INTO $tbl_goods_ (page_id,goods_id,name,pole1,pole2,pole3,pole4,pole5,pole6,pole7,pole8) VALUES ($page_id,$answers_goodsid,'$name','$pole1','$pole2','$pole3','$pole4','$pole5','$pole6','$pole7','$dat')",$link);


  header("location:$back");
  exit(1);
 }
}
header("location:$back");
exit(1);
?>
