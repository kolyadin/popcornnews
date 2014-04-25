<? if($pageid<100 || $pageid==700): ?>
<html>
<head></head>
<style>
body {font-family:verdana;}
img {border:none;}
a {text-decoration:none;}
form {margin:0;padding:0;}
p {font-size:70%;margin:0px;}
.MainTable {font-size:70%;}
.FormTable {font-size:70%;}
input, select {font-size:90%;}
textarea {font-family:verdana;font-size:100%;}
.MainTable td {border-right:1px solid #fff;:background:#f2f2f2;padding:3px 5px;}
.MainTable .head {font-weight:700;color:#fff;background:#88e;font-size:110%;}
.MainTable .c1, .MainTable .c2, .MainTable .c3 {border-bottom:1px solid #ccc;}
.MainTable .c1, .MainTable .c3 {font-weight:700;}
.MainTable .c2 {font-size:90%;}
.MainTable .c2, .MainTable .c3 {text-align:center;}
.MainTable .c4 {padding:6px 5px;}
</style>
<body onClick="self.parent.changeZI(window.name);">
<?
endif;

include "inc/connect.php";

$page_id=2;
$quiz_goodsid=17;
$questions_goodsid=18;
$answers_goodsid=19;

$dat=date("Ymd");

switch($pageid)
{
  // СПИСОК КОНКУРСОВ
  default: 
?>
<script language="JavaScript"><!--
  function edit_field(field){
        eval("editor.text.value=workform2."+field+".value");
        editor.field.value=field;
        editor.submit();
  };

//--></script>
<form name="editor" target="_blank" method="POST" action="editor.php?rnd=618784462" ENCTYPE="multipart/form-data">
<input type="hidden" name="field" value="">
<input type="hidden" name="text" value="">
</form>

<table class="MainTable" cellspacing="0">
  <tr>
    <td class="head" style="width:30%;"><b>название</b></td>

    <td class="head"><b>начало</b></td>
    <td class="head"><b>окончание</b></td>
    <td class="head"><b>викторина приостановлена</b></td>
    <td class="head">&nbsp;</td>
    <td class="head">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="7" style="border-bottom:1px solid #555;padding-top:15px;font-weight:700;font-size:110%;">Действующие викторины</td>
  </tr>
<?
   $ddd=date("Ymd");
   $line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND round(pole5)>$ddd and pole6='' ORDER BY id DESC",$link);
   while($s=mysql_fetch_array($line))
   {
?>
  <tr>
    <td class="c1"><a href="konkurs2.php?pageid=2&id=<? print $s[0]; ?>"><? print $s["name"]; ?></a></td>

    <td class="c2"><? print dat($s["pole4"], "."); ?></td>
    <td class="c2"><? print dat($s["pole5"], "."); ?></td>
    <td class="c3"><? if ($s["pole6"]) print '<img src="i/Tick.gif" alt="" width="24" height="24">'; ?>&nbsp;</td>
    <td class="c3 c4"><a href="konkurs2.php?pageid=700&id=<? print $s[0]; ?>"><nobr><img src="i/stat.gif" alt="Статистика" width="24" height="24" style="margin:-5px 6px -7px 0">Ответы</nobr></a></td>
    <td class="c3 c4"><a href="konkurs2.php?pageid=100&id=<? print $s[0]; ?>"><img src="i/delete.gif" alt="удалить конкурс" width="24" height="24" style="margin:-5px 6px -7px 0">Удалить</a></td>
  </tr>
<?      
   }
?>
  <tr>              
    <td colspan="7" style="border-bottom:1px solid #555;padding-top:15px;font-weight:700;font-size:110%;">Завершившиеся или приостановленные викторины</td>
  </tr>
<?
   $line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND (round(pole5)<=$ddd or pole6<>'') ORDER BY id DESC",$link);
   while($s=mysql_fetch_array($line))
   {
?>
<tr>
 <td class="c1"><a href="konkurs2.php?pageid=2&id=<? print $s[0]; ?>"><? print $s["name"]; ?></a></td>
 <td class="c2"><? print dat($s["pole4"], "."); ?></td>
 <td class="c2"><? print dat($s["pole5"], "."); ?></td>
 <td class="c3"><? if ($s["pole6"]) print '<img src="i/Tick.gif" alt="" width="24" height="24">'; ?>&nbsp;</td>
 <td class="c3 c4"><a href="konkurs2.php?pageid=700&id=<? print $s[0]; ?>"><nobr><img src="i/stat.gif" alt="Статистика" width="24" height="24" style="margin:-5px 6px -7px 0">Ответы</nobr></a></td>
 <td class="c3 c4"><a href="konkurs2.php?pageid=100&id=<? print $s[0]; ?>"><img src="i/delete.gif" alt="удалить конкурс" width="24" height="24" style="margin:-5px 6px -7px 0">Удалить</a></td>
</tr>
<?      
   }
   $cityselect='';
   reset($cities);
   foreach($cities as $key=>$value) $cityselect.='<option value="'.$key.'">'.$value.'</option>';

?>
</table>

<form name="workform2" action="konkurs2.php" method="post" ENCTYPE="multipart/form-data">
<input type="hidden" name="pageid" value="101">
<h3>Добавить новый конкурс</h3>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#F9F9F8" class="FormTable">
  <tr>
    <td bgcolor="#D6DFF7" width="20%" align="right"><b>Название</b></td>
    <td bgcolor="#D6DFF7" width="80%"><input type="text" class="f" name="name" style="width:100%;" value=''></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Описание</b></td>
    <td bgcolor="#D6DFF7"><textarea class="f" name="pole2" style="width:100%; height:300px;"></textarea><br><input type="button" value="редактировать" class="fb" style="width:100%;" onClick="edit_field('pole2')"></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Картинка</b></td>
    <td bgcolor="#D6DFF7"><input type="file" class="f" name="userfile" style="width:100%;"></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Начало проведения YYYYMMDD</b></td>
    <td bgcolor="#D6DFF7"><input type="text" class="f" name="pole4" style="width:100%;" value='<? print date("Ymd"); ?>'></td>
  </tr>
  <tr>                                                                                                                          
    <td bgcolor="#D6DFF7" align="right"><b>Окончание проведения YYYYMMDD</b></td>
    <td bgcolor="#D6DFF7"><input type="text" class="f" name="pole5" style="width:100%;" value='<? print intval((date("Y")+1)).date("md"); ?>'></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Приостановить викторину</b></td>
    <td bgcolor="#D6DFF7"><input type="checkbox" name="pole6"></td>
  </tr>
<?
/*
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Не требовать регистрацию</b></td>
    <td bgcolor="#D6DFF7"><input type="checkbox" name="pole7"></td>
  </tr>
*/
?>

  <tr>
    <td align="center" bgcolor="#F9F9F8" colspan="2"><input type="submit" value="Добавить" style="width:200px;font-size:110%;font-weight:700;margin-top:10px;"></td>
  </tr>
</table>
</form>
<?
  break;

  // РЕДАКТИРОВАНИЕ КОНКУРСА
  case(2):
?>
<p><b><a href="konkurs2.php">к списку конкурсов</a></b></p>
<?  

   $line=mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);
   $s=mysql_fetch_array($line);

?>
<script language="JavaScript"><!--
  function edit_field(field){
        eval("editor.text.value=workform2."+field+".value");
        editor.field.value=field;
        editor.submit();
  };

//--></script>
<form name="editor" target="_blank" method="POST" action="editor.php?rnd=618784462" ENCTYPE="multipart/form-data">
<input type="hidden" name="field" value="">
<input type="hidden" name="text" value="">
</form>
<style> #MainTable td {padding:3px 10px;} </style>
<form name="workform2" action="konkurs2.php" method="post" ENCTYPE="multipart/form-data">
<input type="hidden" name="pageid" value="102">
<input type="hidden" name="id" value="<? print $id; ?>">
<h3><? print $s["name"]; ?></h3>

<p style="margin:0px 0px 20px 0px;"><a href="konkurs2.php?pageid=700&id=<? print $s[0]; ?>"><nobr><img src="i/stat.gif" alt="Статистика" width="24" height="24" style="margin:0px 6px -7px 0">Посмотреть ответы</nobr></a></p>

<table cellpadding="4" cellspacing="1" border="0" bgcolor="#F9F9F8" class="FormTable">
  <tr>
    <td bgcolor="#D6DFF7" width="20%" align="right"><b>Название</b></td>
    <td bgcolor="#D6DFF7" width="80%"><input type="text" class="f" name="name" style="width:100%;" value='<? print htmlspecialchars($s["name"]); ?>'></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Описание</b></td>
    <td bgcolor="#D6DFF7"><textarea class="f" name="pole2" style="width:100%;  height:300px;"><? print htmlspecialchars($s["pole2"]); ?></textarea><br><input type="button" value="редактировать" class="fb" style="width:100%;" onClick="edit_field('pole2')"></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Картинка</b></td>
    <td bgcolor="#D6DFF7"><input type="file" class="f" name="userfile" style="width:100%;">
<?
if ($s["pole3"]) 
 {
  print '<a href="/upload/'.$s["pole3"].'" target="_blank"><img src="/upload/'.$s["pole3"].'" alt="" border="0" height="50" hspace="5" vspace="5"></a>';
  ?><a href="konkurs2.php?pageid=104&id=<? print $s[0]; ?>">удалить</a><?
 };

?>
    </td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Начало проведения YYYYMMDD</b></td>
    <td bgcolor="#D6DFF7"><input type="text" class="f" name="pole4" style="width:100%;" value='<? print $s["pole4"]; ?>'></td>
  </tr>
  <tr>                                                                                          
    <td bgcolor="#D6DFF7" align="right"><b>Окончание проведения YYYYMMDD</b></td>
    <td bgcolor="#D6DFF7"><input type="text" class="f" name="pole5" style="width:100%;" value='<? print $s["pole5"]; ?>'></td>
  </tr>
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Приостановить викторину</b></td>
    <td bgcolor="#D6DFF7"><input type="checkbox" name="pole6" <? if ($s["pole6"]) print "CHECKED"; ?>></td>
  </tr>
<?
/*
  <tr>
    <td bgcolor="#D6DFF7" align="right"><b>Не требовать регистрацию</b></td>
    <td bgcolor="#D6DFF7"><input type="checkbox" name="pole7" <? if ($s["pole7"]) print "CHECKED"; ?>></td>
  </tr>
  */?>


</table>
<h3>Вопросы конкурса</h3>

<?
   $line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$questions_goodsid AND pole1='$id' ORDER BY id",$link);
   while($s=mysql_fetch_array($line))
   {
?>

<textarea name="q<? print $s[0]; ?>name" class="cfi" rows=2 style="width:90%;height:60px;"><? print $s["name"]; ?></textarea><a href="konkurs2.php?pageid=103&qid=<? print $s[0]; ?>&id=<? print $id; ?>"><img src="i/i12.gif" alt="удалить вопрос" width="16" height="16" style="margin-top:-15px;"></a>
<br><input type="button" value="редактировать"  onClick="edit_field('q<? print $s[0]; ?>name')" style="font-size:10px;margin:4px 0px;">
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#F9F9F8" class="MainTable">
<?
    for ($i=1; $i<=10; $i++)
    {
     $k=$i+1;
?>
  <tr>
    <td bgcolor="#E7E7E7" width="1"><input type="radio" name="q<? print $s[0]; ?>pole15" value="<? print $i; ?>" <? if ($s["pole15"]==$i) print "CHECKED"; ?>></td>
    <td bgcolor="#E7E7E7" width="20%"><b><nobr>вариант ответа <? print $i; ?></nobr></b></td>
    <td bgcolor="#F1F1F1" width="80%"><input type="text" class="f" name="q<? print $s[0]; ?>pole<? print $i+1; ?>" style="width:100%;" value="<? print $s["pole".$k]; ?>"></td>
  </tr>
<?
    }
?>
</table>
<?     
   }
?>

<h3>Добавить вопрос</h3>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#F9F9F8" class="MainTable">
  <tr>
    <td>&nbsp;</td>
    <td bgcolor="#E7E7E7"><b>Вопрос</b></td>
    <td bgcolor="#F1F1F1"><textarea name="qname" class="cfi" rows=2 style="width:90%;height:60px;"></textarea><br><input type="button" value="редактировать"  onClick="edit_field('qname')" style="font-size:10px;margin:4px 0px;"></td>
  </tr>
<?
 for ($i=1; $i<=10; $i++)
  {
?>
  <tr>
    <td bgcolor="#E7E7E7" width="1"><input type="radio" name="qpole15" value="<? print $i; ?>"></td>
    <td bgcolor="#E7E7E7" width="20%"><b><nobr>вариант ответа <? print $i; ?></b></td>
    <td bgcolor="#F1F1F1" width="80%"><input type="text" class="f" name="qpole<? print $i+1; ?>" style="width:100%;"></td>
  </tr>
<?
  };
?>
</table>
<p style="text-align:center;"><input type="submit" value="Сохранить" style="width:200px;font-size:110%;font-weight:700;margin-top:10px;"></p>
</form>
<?
  break;


  // СТАТИСТИКА
  case(700):
?>

<p><b><a href="konkurs2.php">к списку конкурсов</a></b></p>

<?  

   $line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);
   $item=mysql_fetch_array($line);

   $line = mysql_query("SELECT count(id) FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole2='$id' AND pole4='Yes' ORDER BY pole8 DESC",$link);
   $s1=mysql_fetch_array($line);

   $line = mysql_query("SELECT count(id) FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole2='$id' AND pole4='No' ORDER BY pole8 DESC",$link);
   $s2=mysql_fetch_array($line);

?>
<h3><? print $item["name"]; ?></h3>


 <p>Викторина стартует: <b><? print dat_($item["pole4"], " "); ?></b></p>
 <p>Викторина заканчивается: <b><? print dat_($item["pole5"], " "); ?></b></p>

 <p style="margin:5px 0px;">В викторине приняли участие человек: <b><? print ($s1[0]+$s2[0]); ?></b></p>

 <p>Ответили правильно человек: <b><? print ($s1[0]); ?></b></p>
 <p>Ответили неправильно человек: <b><? print ($s2[0]); ?></b></p>

<style>
.MainTable .ans {border-top:1px solid #ddd;margin:10px -10px -4px -10px;}
.MainTable .ans td {font-size:9px;font-family:Verdana,Tahoma;padding:4px 6px;background:#fff;border-bottom:1px solid #ddd;line-height:1.1;}
.MainTable .ans .t2 {}
.MainTable .a0 {margin-bottom:-3px;}
.MainTable .a1 a,.MainTable .a2 a {color:#555;text-decoration:underline;font-weight:100;font-size:11px;}
.MainTable .a2,.MainTable .a3 {display:none;}
.MainTable .a1,.MainTable .a2 {text-align:right;}
</style>
<form name="workform2" action="konkurs2.php" method="post" ENCTYPE="multipart/form-data">
<input type="hidden" name="pageid" value="103">
<input type="hidden" name="id" value="<? print $id; ?>">
<h3>Список правильно ответивших</h3>
<table cellspacing="0" class="MainTable">
  <tr>
    <td class="head" width="20%"><b>Имя</b></td>
    <td class="head" width="20%">E-mail</td>
    <td class="head" width="5%">Телефон</td>
    <td class="head" width="20%">Дата ответа</td>
    <td class="head" width="10%">Ответ</td>
    <td class="head" width="20%">IP</td>
    <td class="head" width="25%">Город</td>
  </tr>
<?
//$line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole2='$id' and pole4='Yes' ORDER BY pole8",$link);
$line = mysql_query(sprintf('SELECT a.*, b.city FROM %s a LEFT JOIN popkorn_users b ON (a.pole1 = b.id) WHERE a.page_id = %u AND a.goods_id = %u AND a.pole2 = %u AND a.pole4 = "Yes" ORDER BY a.pole8', $tbl_goods_, $page_id, $answers_goodsid, $id), $link);
while($s=mysql_fetch_array($line))
{
     if (!$s["pole1"]) // не регился
     {
?>
<tr>
 <td class="c1"><? print $s["name"]; ?>&nbsp;</td>
 <td class="c1"><? print $s["pole5"]; ?>&nbsp;</td>
 <td class="c1"><? print $s["pole6"]; ?>&nbsp;</td>
 <td class="c2"><? print dat_($s["pole8"], " "); ?></td>
 <td class="c3"><? print eregi_replace("-","&nbsp;",$s["pole3"]); ?></td>
 <td class="c3"><? print $s["pole7"]; ?></td>
 <td class="c3">&nbsp;</td>
</tr>
<?
      }
       else
      {
?>
<tr>
 <td class="c1"><a href="/profile/<? print $s["pole1"]; ?>" target="_blank"><? print $s["name"]; ?></a></td>
 <td class="c1"><? print $s["pole5"]; ?></td>
 <td class="c1"><? print $s["pole6"]; ?>&nbsp;</td>
 <td class="c2"><? print dat_($s["pole8"], " "); ?></td>
 <td class="c3"><? print eregi_replace("-","&nbsp;",$s["pole3"]); ?></td>
 <td class="c3"><? print $s["pole7"]; ?></td>
 <td class="c3"><? print $s['city']; ?></td>
</tr>
<?
      };
};
?>
</table>
<h3>Список остальных участников</h3>
<table cellspacing="0" class="MainTable">
  <tr>
    <td class="head" width="20%"><b>Имя</b></td>
    <td class="head" width="20%">E-mail</td>
    <td class="head" width="5%">Телефон</td>
    <td class="head" width="20%">Дата ответа</td>
    <td class="head" width="10%">Ответ</td>
    <td class="head" width="20%">IP</td>
    <td class="head" width="25%">Город</td>

  </tr>
<?
   //$line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole2='$id' AND pole4='No' ORDER BY pole8",$link);
   $line = mysql_query(sprintf('SELECT a.*, b.city FROM %s a LEFT JOIN popkorn_users b ON (a.pole1 = b.id) WHERE a.page_id = %u AND a.goods_id = %u AND a.pole2 = %u AND a.pole4 = "No" ORDER BY a.pole8', $tbl_goods_, $page_id, $answers_goodsid, $id), $link);
   while($s=mysql_fetch_array($line))
    {
     if (!$s["pole1"]) // не регился
      {
?>
<tr>
 <td class="c1"><? print $s["name"]; ?>&nbsp;</td>
 <td class="c1"><? print $s["pole5"]; ?>&nbsp;</td>
 <td class="c1"><? print $s["pole6"]; ?>&nbsp;</td>
 <td class="c2"><? print dat_($s["pole8"], " "); ?></td>
 <td class="c3"><? print eregi_replace("-","&nbsp;",$s["pole3"]); ?></td>
 <td class="c3"><? print $s["pole7"]; ?></td>
 <td class="c3">&nbsp;</td>
</tr>
<?
      }
       else
      {
?>
<tr>
 <td class="c1"><a href="/profile/<? print $s["pole1"]; ?>" target="_blank"><? print $s["name"]; ?></a></td>
 <td class="c1"><? print $s["pole5"]; ?>&nbsp;</td>
 <td class="c1"><? print $s["pole6"]; ?>&nbsp;</td>
 <td class="c2"><? print dat_($s["pole8"], " "); ?></td>
 <td class="c3"><? print eregi_replace("-","&nbsp;",$s["pole3"]); ?></td>
 <td class="c3"><? print $s["pole7"]; ?></td>
 <td class="c3"><? print $s['city']; ?></td>
</tr>
<?
      };

    };
?>  
</table>

<?
  break;


  // ДОБАВИМ КОНКУРС
  case(101):

    if ($userfile) $pole3=save_file($userfile_name,$userfile_size,$userfile,"7","0","0","0","","","","","");
    mysql_query("INSERT INTO $tbl_goods_ (page_id,goods_id,name,pole1,pole2,pole3,pole4,pole5,pole6,pole7,pole8,pole20,pole9) VALUES ($page_id,$quiz_goodsid,'$name','$city','$pole2','$pole3','$pole4','$pole5','$pole6','$pole7','$pole8','$pole20','$pole9')",$link);
    header ("location:konkurs2.php");
    exit(1);
  break;

  // ОБНОВИМ ИНФОРМАЦИЮ О КОНКУРСЕ
  case(102): 

    if ($userfile_size>100)
     {
      // удалим старую картинку
      $line = mysql_query("SELECT pole3 FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);
      $s=mysql_fetch_array($line);
      $pole3=save_file($userfile_name,$userfile_size,$userfile,"7","0","0","0","","","","","");
      $ccc="pole3='$pole3'";
     } else $ccc="pole3=pole3";

    // обновим общую информацию о конкурсе в папке конкурсы
    mysql_query("UPDATE $tbl_goods_ SET name='$name',pole1='$city',pole20='$pole20',pole2='$pole2',pole4='$pole4',pole5='$pole5',pole6='$pole6',pole7='$pole7',pole8='$pole8',pole9='$pole9',$ccc WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);

    // добавим новый вопрос, еси есть
    if (($qpole15)&&($qname))
     {
      $cmd1="page_id,goods_id,name,pole1,pole2,pole3,pole4,pole5,pole6,pole7,pole8,pole9,pole10,pole11,pole15";
      $cmd2="$page_id,$questions_goodsid,'$qname','$id','$qpole2','$qpole3','$qpole4','$qpole5','$qpole6','$qpole7','$qpole8','$qpole9','$qpole10','$qpole11','$qpole15'";
      mysql_query("INSERT INTO $tbl_goods_ ($cmd1) VALUES ($cmd2)",$link);
      $flag=1;
     };

    // обновим информацию о существующих вопросах
    $line = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$questions_goodsid AND pole1='$id'",$link);
    while($s=mysql_fetch_array($line))
    {
      $qid=$s[0];
      $qname=$HTTP_POST_VARS["q".$s[0]."name"];
      $qpole2=$HTTP_POST_VARS["q".$s[0]."pole2"];
      $qpole3=$HTTP_POST_VARS["q".$s[0]."pole3"];
      $qpole4=$HTTP_POST_VARS["q".$s[0]."pole4"];
      $qpole5=$HTTP_POST_VARS["q".$s[0]."pole5"];
      $qpole6=$HTTP_POST_VARS["q".$s[0]."pole6"];
      $qpole7=$HTTP_POST_VARS["q".$s[0]."pole7"];
      $qpole8=$HTTP_POST_VARS["q".$s[0]."pole8"];
      $qpole9=$HTTP_POST_VARS["q".$s[0]."pole9"];
      $qpole10=$HTTP_POST_VARS["q".$s[0]."pole10"];
      $qpole11=$HTTP_POST_VARS["q".$s[0]."pole11"];
      $qpole15=$HTTP_POST_VARS["q".$s[0]."pole15"];
      $sql = sprintf(
         'UPDATE %s SET
          name="%s", pole2="%s", pole3="%s", pole4="%s", pole5="%s", pole6="%s", pole7="%s", pole8="%s", pole9="%s", pole10="%s", pole11="%s", pole15="%s"
          WHERE page_id=%d AND goods_id=%d AND id=%d',
         $tbl_goods_, $qname, $qpole2, $qpole3, $qpole4, $qpole5, $qpole6, $qpole7, $qpole8, $qpole9, $qpole10, $qpole11, $qpole15, $page_id, $questions_goodsid, $qid
      );
      if ($qname) mysql_query($sql, $link);
      $flag=1;
    }

    header ("location:konkurs2.php?id=$id&pageid=2");
    exit(1);
  break;

  // УДАЛИМ КОНКУРС
  case(100):
    mysql_query("DELETE FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);
    mysql_query("DELETE FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$answers_goodsid AND pole2='$id'",$link);
    mysql_query("DELETE FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$questions_goodsid AND pole1='$id'",$link);
    header ("location:konkurs2.php");
    exit(1);
  break;

  // УДАЛИМ ВОПРОС
  case(103):
    // $id конкурс
    // $qid вопрос, его удаляем
    mysql_query("DELETE FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$questions_goodsid AND id='$qid'",$link);
    header ("location:konkurs2.php?id=$id&pageid=2");
    exit(1);
  break;

  // УДАЛИМ КАРТИНКУ
  case(104):
   $line = mysql_query("SELECT pole3 FROM $tbl_goods_ WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);
   $s=mysql_fetch_array($line);
   mysql_query("UPDATE $tbl_goods_ SET pole3='' WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND id=$id",$link);
   header ("location:konkurs2.php?id=$id&pageid=2");
   exit(1);
  break;

 }

?>
</body>
</html>