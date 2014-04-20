<?$this->_render('inc_header.twilight',array('title'=>'¬икторины','header'=>$head,'top_code'=>'','header_small'=>''));?>
			<div id="contentWrapper" class="twoCols">

				<div id="content">
					<div class="h-no-shadow">
						<div class="h">
							<h1>
								<span class="logo"><a href="/"></a></span>
								<span class="h1">сага</span>
							</h1>
						</div>
					</div>
                                        <div class="victorina">
                                             <div class="questions">
<?
$page_id =                                2;
/*
 * ответ в процентах
 */
$quiz_goodsid_percents =                  22;
$questions_goodsid_percents =             29;
// статистика $answers_goodsid_percents=30;
/*
 * словестный ответ
 */
$quiz_goodsid_words =                     26;
$questions_goodsid_words =                31;

$user=$d['cuser'];


foreach ($d['data'] as $i => $s){
  $id =        $s['id'];
  $vikt =      $s;
  $konkurs =   $s;
  $s[0] =      $s['id'];
  $head =      $vikt['name'];
  $anwser_in_percents = ($s['goods_id'] == $quiz_goodsid_percents ? 1 : 0); // смотрим тип опроса
  $questions_goodsid = ($s['goods_id'] == $quiz_goodsid_percents ? $questions_goodsid_percents : $questions_goodsid_words);

  if (!$user['nick'] && $vikt['pole7'] == ''){
?>
     <ul class="quiz" style="border-bottom:2px solid #eee; color: #ffffff;"><li><div class="quiz-text"><p>”частие в конкурсе доступно  только <a href="/register/">зарегистрированным участникам</a></p></div></li></ul>
<?
  }else{
?>
                                                         <ul class="quiz" style="color: #ffffff;">
                                                         <li>
                                                         <div class="quiz-pic">
										   <?if ($s['pole3']) {?>
										   <a href="/quiz/<?=$s['id']?>"><img src="<?=$this->getStaticPath('/upload/' . $s['pole3'])?>" alt="" /></a>
										   <?}?>
                                                         </div>
                                                         <div class="quiz-text">
                                                            <p><?=$s['pole2'];?></p>
                                                            <p><a href="/challenge/">¬ернутьс€ к списку опросов</a></p>
                                                         </div>
                                                         </li>
                                                         </ul>
                                                         <div class="decor">&nbsp;</div>
<?
      /*
       * просмотр статистики
      $sql1 = sprintf(
         'SELECT * FROM %s WHERE goods_id = %d and pole1 = %d and pole2= %d',
         TBL_GOODS_, $answers_goodsid, $user['id'], $id
      );
      $sql2 = sprintf(
         'SELECT * FROM %s WHERE goods_id = %d and pole7 = %s and pole2= %d',
         TBL_GOODS_, $answers_goodsid, $ip, $id
      );
      
      if (!$konkurs['pole7']) $line3 = mysql_query($sql1);
      else $line3 = mysql_query($sql2);
      $s3=mysql_fetch_array($line3);

      if ($s3[0]){
?>
                                                            <ul class="quiz" style="border-bottom:2px solid #eee; color: #ffffff;"><li><div class="quiz-text"><p><br><br>—пасибо, ¬аш ответ прин€т. ћы сообщим о подведении итогов.</p></div></li></ul>
<?
      }
      */

      // если этот опрос не удален и еще проводитс€
      if ($s[0] && !$s3[0]){
?>
<form method="post">
   <input type="hidden" name="type" value="challenge" />
   <input type="hidden" name="id" value="<?=$id;?>" />
   <input type="hidden" name="action" value="submit" />
   <div class="decor">
<?
         $sql = sprintf(
            'SELECT * FROM %s WHERE goods_id = %d and pole1 = %d',
            TBL_GOODS_, $questions_goodsid, $id
         );

         $line4 = mysql_query($sql);
         $text = '';
         while($s4=mysql_fetch_array($line4)){
            /*
             * если ответ не в процентах то берем id дл€ ключевых значений
             */
            if (!$anwser_in_percents) $anwsers = split(';', $s4['pole15']);
            $text.='<dl>';
            $text.='<dd>'.$s4['name'].'</dd>';
            if ($s4['pole2']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '1' : $anwsers[0]) . '" checked> '.$s4['pole2'].'</label></dd>';
            if ($s4['pole3']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '2' : $anwsers[1]) . '"> '.$s4['pole3'].'</label></dd>';
            if ($s4['pole4']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '3' : $anwsers[2]) . '"> '.$s4['pole4'].'</label></dd>';
            if ($s4['pole5']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '4' : $anwsers[3]) . '"> '.$s4['pole5'].'</label></dd>';
            if ($s4['pole6']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '5' : $anwsers[4]) . '"> '.$s4['pole6'].'</label></dd>';
            if ($s4['pole7']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '6' : $anwsers[5]) . '"> '.$s4['pole7'].'</label></dd>';
            if ($s4['pole8']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '7' : $anwsers[6]) . '"> '.$s4['pole8'].'</label></dd>';
            if ($s4['pole9']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '8' : $anwsers[7]) . '"> '.$s4['pole9'].'</label></dd>';
            if ($s4['pole10']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '9' : $anwsers[8]) . '"> '.$s4['pole10'].'</label></dd>';
            if ($s4['pole11']) $text.='<dd><label><input type="radio" name="T'.$s4[0].'" value="' . ($anwser_in_percents ? '10' : $anwsers[9]) . '"> '.$s4['pole11'].'</label></dd>';
            $text.='</dl>';
         }
         echo $text;
?>
   </div>
   <input type="submit" value="" class="submit" />
<?
      }
   }
}

/*
foreach ($d['data'] as $key => $value){
   $int_pole = substr($key, 4);
   if (!empty($value) && substr($key, 4) > 0 && substr($key, 4) < 30){
      if (($int_pole-1)%5 == 0 || $int_pole == 1){
         $id = $int_pole;
?>
                                                    <?if ($int_pole != 1){?></dl><?}?>
                                                    <dl>
                                                    <dd><?=$value?></dd>
<?
      }else{
?>
                                                    <dt><label><input type="radio" value="<?=$value;?>" name="question_<?=$id;?>" /><?=$value;?></label></dt>
<? 
      }
   }
}
?>
                                                    </dl>
*/
?>
</form>
                                             </div>
                                         </div>
                                </div>
				<?$this->_render('inc_right_column.twilight');?>
			</div>
<?$this->_render('inc_footer.twilight');?>