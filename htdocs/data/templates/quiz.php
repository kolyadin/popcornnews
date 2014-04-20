<?
$user=$d['user'];
/*
print $user["nick"];
echo "<br><hr><br>";
*/
$page_id=2;
$quiz_goodsid=17;
$questions_goodsid=18;
$answers_goodsid=19;

$dat=date("Ymd");
$ddd=date("Ymd");



$head="Викторины";

$u=$_SERVER["REDIRECT_URL"];
$URL=explode("/", trim($u, '/'));

$tbl_goods_="popconnews_goods_";

if (intval($URL[1])) // смотрим викторину
{ 
 $id=$qid=intval($URL[1]);
 $quizs = $p['query']->get_query("SELECT * FROM ".TBL_GOODS_." WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND round(pole5)>$ddd and pole6='' and id=$qid ORDER BY id DESC"); 
 foreach($quizs as $i => $s)
 {
  $vikt=$s;
  $s[0]=$s["id"];
  $head=$vikt["name"];

  if (!$user["nick"] && $vikt["pole7"]=="")
   {
    $text='<ul class="quiz" style="border-bottom:2px solid #eee;"><li><div class="quiz-text"><p>Участие в конкурсе доступно  только <a href="/register/">зарегистрированным участникам</a></p></div></li></ul>';
   }
    else
   {

  $text='
<ul class="quiz" style="border-bottom:2px solid #eee;">
<li>
<div class="quiz-pic">'.($s["pole3"] ? '<a href="/quiz/'.$s["id"].'"><img src="' . $this->getStaticPath('/upload/'.$s["pole3"]).'" /></a>' : '').'</div>
<div class="quiz-text"><p>'.$s["pole2"].'</p><p><a href="/quiz/">Вернуться к списку викторин</a></p></div>
</li>
</ul>';

    $konkurs=$s;

    


    if (!$konkurs["pole7"]) $line3 = mysql_query("SELECT * FROM $tbl_goods_ WHERE goods_id=$answers_goodsid and pole1='".$user["id"]."' and pole2='$id'");
     else $line3 = mysql_query("SELECT * FROM $tbl_goods_ WHERE goods_id=$answers_goodsid and pole7='$ip' and pole2='$id'");
    $s3=mysql_fetch_array($line3);

    if ($s3[0]) $text.='<ul class="quiz" style="border-bottom:2px solid #eee;"><li><div class="quiz-text"><p><br><br>Спасибо, Ваш ответ принят. Мы сообщим о подведении итогов.</p></div></li></ul>';

    if ($s[0] && !$s3[0]) // если этот конкурс не удален и еще проводится
     {
  
      
      $text.='
<style>
.q h4 {font-size:18px;font-weight:100;margin:20px 0px 10px 0px;}
.kf .q p {margin:2px 0px;}
.kf .q {margin:20px 0px;}
</style>
      <form name="kf" action="/inc/quiz-answer.php" method="post" ENCTYPE="multipart/form-data">';
      $text.='<input type="hidden" name="id" value="'.$id.'">';
      $text.='<input type="hidden" name="T17595923" value="17595923">';
      $line4 = mysql_query("SELECT * FROM $tbl_goods_ WHERE goods_id=$questions_goodsid and pole1='$id'");
      while($s4=mysql_fetch_array($line4))
       {
        $text.='<div class="q">';
        $text.='<h4>'.$s4["name"].'</h4>';
        if ($s4["pole2"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="1" checked> '.$s4["pole2"].'</p>';
        if ($s4["pole3"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="2"> '.$s4["pole3"].'</p>';
        if ($s4["pole4"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="3"> '.$s4["pole4"].'</p>';
        if ($s4["pole5"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="4"> '.$s4["pole5"].'</p>';
        if ($s4["pole6"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="5"> '.$s4["pole6"].'</p>';
        if ($s4["pole7"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="6"> '.$s4["pole7"].'</p>';
        if ($s4["pole8"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="7"> '.$s4["pole8"].'</p>';
        if ($s4["pole9"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="8"> '.$s4["pole9"].'</p>';
        if ($s4["pole10"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="9"> '.$s4["pole10"].'</p>';
        if ($s4["pole11"]) $text.='<p><input type="radio" name="T'.$s4[0].'" value="10"> '.$s4["pole11"].'</p>';
        $text.='</div>';
       };

      if ($konkurs["pole7"] && !$user_id)
       {

$text.='
<SCRIPT language="JavaScript">
function answer()
{
 if ( !document.kf.TT1.value || !document.kf.TT4.value ) alert("Необходимо оставить свое имя и e-mail"); else document.kf.submit();
}
</script>

<table cellspacing="0">
  <tr>
    <td style="font-size:12px;padding:4px 15px 4px 0px;">Имя <sup>*</sup></td>
    <td style="font-size:12px;padding:4px 15px 4px 0px;"><input type="text" name="TT1" value=""></td>
  </tr>
  <tr>
    <td style="font-size:12px;padding:4px 15px 4px 0px;">e-mail <sup>*</sup></td>
    <td style="font-size:12px;padding:4px 15px 4px 0px;"><input type="text" name="TT4" value=""></td>
  </tr>
  <tr>
    <td style="font-size:12px;padding:4px 15px 4px 0px;">Телефон <sup>*</sup></td>
    <td style="font-size:12px;padding:4px 15px 4px 0px;"><input type="text" name="TT5" value=""></td>
  </tr>
</table>
<p><input type="button" value="ответить" style="width:100px;" onclick="answer();" /></p>

</form>';
       }

      $text.='<div style="border-top:2px solid #eee;padding-top:10px;margin-top:20px;"><p><input type="submit" class="button" value="ответить" style="width:100px;font-size:18px;margin:30px 0px;" onclick="answer()"></p></div>';
     };                           
   };
 };
}
 else // список викторин
{

 foreach($p['query']->get_query("SELECT * FROM ".TBL_GOODS_." WHERE page_id=$page_id AND goods_id=$quiz_goodsid AND round(pole5)>$ddd and pole6='' ORDER BY id DESC") as $i => $s)
 {
      $t.= '
	  	<li>
			<div class="quiz-pic">
				'.($s["pole3"] ? '<a href="/quiz/'.$s["id"].'"><img src="' . $this->getStaticPath('/upload/'.$s["pole3"]).'" /></a>' : '').'
			</div>
			<div class="quiz-text">
				<h2><a href="/quiz/'.$s["id"].'">'.$s['name'].'</a></h2>
				<p>'.$s["pole2"].'</p>				
				<p><a href="/quiz/'.$s["id"].'">Принять участие</a></p>
			</div>
		</li>';

 }


 $text='<ul class="quiz">'.$t.'</ul>';
}
$this->_render('inc_header',array('title'=>'Викторины','header'=>$head,'top_code'=>'','header_small'=>''));
?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
<?=$text;?>
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>