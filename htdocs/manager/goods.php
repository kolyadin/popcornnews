<?
/*
print "=".$HTTP_POST_VARS["pageid1"]."=($rand)";
print "=".$HTTP_POST_VARS["pageid"]."=($rand)";
print "=".$HTTP_POST_VARS["pageid2"]."=($rand)";

print "=$pageid/$subpageid/$page_id/$fold_id/$good_id=<br>";

print "=".$pole1."=";
exit(1);
*/
//новая админка, пишется с нуля :)
//print "-$page_id-($pageid)-$fold_id-$good_id--";
//print "=".$HTTP_POST_VARS["pageid"]."=($rand)";
//print "=".$HTTP_POST_VARS["page_id"]."=($rand)";
//print "=".$HTTP_POST_VARS["fold_id"]."=($rand)";

//exit(1);

include "inc/connect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/data/libs/compat.lib.php';

//if (isset($_SERVER['IS_AZAT_SERVER']) && $_SERVER['IS_AZAT_SERVER']) {
	header('Content-type: text/html; charset=windows-1251;');
	mysql_query('SET NAMES cp1251', $link);
//}

/*
$tbl_goods=$tbl_news;
$tbl_goods_=$tbl_news_;
$tbl_pages=$tbl_pages_news;
$tbl_pix="news_".$project."_pix";
*/
//print "=".$_SERVER["SERVER_SOFTWARE"]."=";
//print "-$server_os-$PHP_AUTH_USER-$PHP_AUTH_PW-";
if (!isset($PHP_AUTH_USER)) $PHP_AUTH_USER = '';
if($server_os==0 && $PHP_AUTH_USER!=""){
//Внимание! Если сервер Windows отключим разделение на пользователей
//print "2";
        //$PHP_AUTH_USER=$admin_login;
        //$PHP_AUTH_PW=$admin_pass;

        $s=secure();

        $user_id=intval($s[0]);//id,login,name,status
        $nick=$s[1];
        $user_name=$s[2];
        $admin=$s[3];
        unset($s);

//print "-$user_id-$nick-$admin-";

} else { // сервер вин или не обрабатываются $PHP_AUTH_USER - отключим разделение на пользователей
//print "3";
        $admin=3;
        $user_id=0;
};


$showhead=(isset($showhead) ? intval($showhead) : null); // =1 - показываем навигацию сверху для папки - выпадающую

$page_id=intval($page_id); // тома
$fold_id=intval($fold_id); // папки
$good_id=intval($good_id); // файлы
$goods_id=intval($goods_id); // то, на кого ссылается файл или папка

$mpage_=200;//файлов на страницу

$search=(isset($search) ? trim($search) : null);



function check_fold($user_id,$page_id,$fold_id){
//ф-я возвращает false если пользователь не имеет доступ на папку и true если имеет
        global $link;
        global $tbl_gus;
        global $tbl_goods_users;
        global $tbl_goods;

        //проверим уровень пользователя - 3-админ - все можно 
        $cmd="SELECT status FROM $tbl_goods_users WHERE id=$user_id";
        $line = mysql_query($cmd,$link);
        $s=mysql_fetch_array($line);

        if($s[0]>=3) return true; // админ

        //проверим есть ли хоть какието запреты на папку - если нет то возвратим true
        $cmd="SELECT * FROM $tbl_gus WHERE fold_id=$fold_id and page_id=$page_id";
        $line = mysql_query($cmd,$link);
        if(!($s=mysql_fetch_array($line))) {// нет запретов
                
                if($fold_id==0) return true; // это самая верхняя папка
                
                //проверим родительскую папку - может доступа нет уже ранее?
                $cmd="SELECT goods_id FROM $tbl_goods WHERE id=$fold_id";
                $line = mysql_query($cmd,$link);
                $s=mysql_fetch_array($line);

                return check_fold($user_id,$page_id,$s[0]); 

        };
        
        //есть запреты на папку, посомтрим есть ли на пользователя
        $cmd="SELECT * FROM $tbl_gus WHERE user_id=$user_id and fold_id=$fold_id and page_id=$page_id";
        $line = mysql_query($cmd,$link);
        if($s=mysql_fetch_array($line)) return true;
          else return false;

};

function secure(){ // autorization function

        //print "=$PHP_AUTH_USER=$PHP_AUTH_PW=";
        global $PHP_AUTH_USER;
        global $PHP_AUTH_PW;
        global $tbl_goods_users;
        global $link;
        global $ip;

        $cmd="SELECT id,login,name,status FROM $tbl_goods_users WHERE login='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW'";
        $line = mysql_query($cmd,$link);

        //print $cmd;

        if(!($string=mysql_fetch_array($line))){
                norules();
        } else return $string;

};


function norules(){
// ф-я выводит сообщение о нехватке прав пользователем на просмотр страницы
        global $PHP_AUTH_USER;
        
        generate_users();

        $showhead=-10;

include "inc/header.php";

?>
<a name="new"><span class="cg"><b>Ваш уровень доступа недостаточен для перехода на эту страницу</b></span></a><br><br>
Действия, которые вы хотите совершить, могут произвести только администраторы.
<br><br>
<b>Пожалуйста выберите действие:  </b>
<li>введите логин/пароль администратора;
<li>свяжитесь со своим администратором;
<li>напишите письмо <a href="mailto:sky@traffic.spb.ru?subj=AccessDenied_<? print $project; ?>">производителям</a>;
<table cellpadding="2" cellspacing="0" border="0" width="100%" height="100%"><tr><td>
<hr>
<br><br><br><br>
</td></tr></table>
<? 
        exit(1);
};


function get_cur_admin_name(){
//ф-я возвращает имя текущего админа
        global $link;
        global $tbl_goods_users;
        global $PHP_AUTH_USER;
        global $PHP_AUTH_PW;

        $cmd="SELECT name FROM $tbl_goods_users WHERE login='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW'";
        $line10 = mysql_query($cmd,$link);

        //print $cmd.mysql_error();

        if($s=mysql_fetch_array($line10)){

          return $s[0];

        } else return "АДМИН";

};


function delete_attach($id){
//ф-я удаляет вложение id
        global $pictures;
        global $tbl_pix;
        global $link;

     $line10 = mysql_query("SELECT * FROM $tbl_pix WHERE id=$id",$link);
     if($s=mysql_fetch_array($line10)){
       
       if($pictures=="base"){
         
         $cmd="DELETE FROM $tbl_pix WHERE id=".$id;
         $line10 = mysql_query($cmd,$link);

         addlog("$cmd","Удалим картинку - вложение",mysql_error());

       } else {

         ///@unlink("../upload/".$s["diskname"]);
         
         $cmd="DELETE FROM $tbl_pix WHERE id=".$id;
         $line10 = mysql_query($cmd,$link);

         addlog("$cmd","Удалим картинку - вложение",mysql_error());

       };
     };
};

function move_fold($fold_id,$f_id){
//ф-я переносит папку $fold_id со всем содержимым в папку fold_id
        global $tbl_goods;
        global $tbl_goods_;
        global $link;

        global $p_id;//id тома куда переносим

        $line10 = mysql_query("SELECT page_id FROM $tbl_goods WHERE id=$f_id",$link);
        $s=mysql_fetch_array($line10);

        if($p_id!=0)$s[0]=$p_id;
       
        $cmd2="UPDATE $tbl_goods SET goods_id=$f_id,page_id=$s[0] WHERE id=$fold_id";
        $line = mysql_query($cmd2,$link);

        addlog("$cmd2","Перенос папки",mysql_error());

        $cmd2="UPDATE $tbl_goods_ SET page_id=$s[0] WHERE goods_id=$fold_id";
        $line = mysql_query($cmd2,$link);

        addlog("$cmd2","Перенос содержимого папки",mysql_error());

        $cmd="SELECT * FROM $tbl_goods WHERE goods_id=$fold_id";
        $line = mysql_query($cmd,$link);
        while($ss=mysql_fetch_array($line)){
          
          move_fold($ss[0],$fold_id);

        };


};

function move_fold_trash($fold_id,$f_id){
//ф-я переносит папку $fold_id со всем содержимым в том 1 (корзина)
        global $tbl_goods;
        global $tbl_goods_;
        global $link;

        $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $s=mysql_fetch_array($line10);
       
        $cmd2="UPDATE $tbl_goods SET goods_id=$f_id,page_id=1,goods_id_=".$s["goods_id"]." WHERE id=$fold_id";
        $line = mysql_query($cmd2,$link);

        addlog("$cmd2","Перенос папки в корзину",mysql_error());

        $cmd2="UPDATE $tbl_goods_ SET page_id=1 WHERE goods_id=$fold_id";
        $line = mysql_query($cmd2,$link);

        addlog("$cmd2","Перенос содержимого папки в корзину",mysql_error());

        $cmd="SELECT * FROM $tbl_goods WHERE goods_id=$fold_id";
        $line = mysql_query($cmd,$link);
        while($ss=mysql_fetch_array($line)){
          
          move_fold_trash($ss[0],$fold_id);

        };


};


function copy_fold_($fold_id,$f_id,$name,$p_id){
// копирование самой папки
      global $link;
      global $tbl_goods;  

      
      $cmd="SELECT * FROM $tbl_goods WHERE id=$fold_id";
      $line = mysql_query($cmd,$link);
      $ss=mysql_fetch_array($line);
      
      $c1="";
      $c2="";
      $cmd="SHOW COLUMNS FROM $tbl_goods";
      $line10 = mysql_query($cmd,$link);
      while($sss=mysql_fetch_array($line10)){
        
        $f="";
        if($sss[0]!="id" && $sss[0]!="page_id" && $sss[0]!="goods_id" && $sss[0]!="name"){
          
          $c1.=",";
          $c2.=",";
          $c1.=$sss[0];
          /*
          if(strpos(" ".$sss[0],"pole")){ // это поле
            if($s[$sss[0]."_"]>5 && $ss[$sss[0]]!=""){ // этот pole - файл
              $f=copy_file_pole($good_id,$sss[0]);
              $c2.="'$f'";
            } else {
              $c2.="'".$ss[$sss[0]]."'";
            };
          } else {
            if($sss[1]=="text" || $sss[1]=="longblob")$c2.="'".$ss[$sss[0]]."'";
              else $c2.=intval($ss[$sss[0]]);
          };
          */

          //print "$sss[1]<br>";

          if($sss[1]=="text" || $sss[1]=="longblob")$c2.="'".$ss[$sss[0]]."'";
              else $c2.=intval($ss[$sss[0]]);

        };

      };

      $cmd="INSERT INTO $tbl_goods (name,page_id,goods_id $c1) VALUES ('$name',$p_id,$f_id $c2)";
      $line_ = mysql_query($cmd,$link);
      
      addlog("$cmd","Копирование папки",mysql_error());

      $cmd="SELECT max(id) FROM $tbl_goods WHERE name='$name'";
      $line = mysql_query($cmd,$link);
      $s=mysql_fetch_array($line);
      

      //print $cmd.mysql_error();

      return $s[0];
};

function copy_fold($fold_id,$f_id,$copy,$name){
// копирование папки fold_id в f_id, если copy!="" то содержимое тоже
// $name - новое имя, если ="" то имя не меняется
        global $link;
        global $tbl_goods;
        global $tbl_goods_;

        global $p_id; // id тома, в который переносим

        if($copy!="")$copy=1;

        $cmd="SELECT * FROM $tbl_goods WHERE id=$f_id";
        $line = mysql_query($cmd,$link);
        $s=mysql_fetch_array($line);
        if($p_id==0)$p_id=$s["page_id"];//page_id назначания


        $cmd="SELECT * FROM $tbl_goods WHERE id=$fold_id";
        $line = mysql_query($cmd,$link);
        if($s=mysql_fetch_array($line)){

           if($name=="")$name=$s["name"];
           //скопируем саму папку
           $new_fold=copy_fold_($s[0],$f_id,$name,$p_id);

           
           //print "-$fold_id-$f_id-$copy-$name-=$new_fold=";

           //exit(1);
           //скопируем файлы

           //print "-$copy-<br>";
           
           if($copy!=""){
             $cmd="SELECT * FROM $tbl_goods_ WHERE goods_id=$fold_id";
             $line = mysql_query($cmd,$link);
             while($ss=mysql_fetch_array($line)){
               
               //print "-$ss[0]-<br>";

               //copy_file($ss[0],1,$s["page_id"],$fold_id,intval($p_id),$new_fold,$ss["name"]);
               copy_file($ss[0],1,$s["page_id"],$fold_id,$p_id,$new_fold,$name);

               //print "-$s[0]-,1,-".$s["page_id"]."-,$fold_id,".intval($p_id).",$new_fold,".$s["name"]."-";
                
             };

             $cmd="SELECT * FROM $tbl_goods WHERE goods_id=$fold_id";
             $line = mysql_query($cmd,$link);
             while($ss=mysql_fetch_array($line)){

               copy_fold($ss[0],$new_fold,$copy,"");

             };

           };
        };
        
};


function check_move($fold_id,$f_id){
//проверим, папка fold_id, переносимая в папку $f_id, не является ли подпапкой fold_id?
        global $link;
        global $tbl_goods;
        
        $cmd="SELECT goods_id FROM $tbl_goods WHERE id=$f_id";
        $line10 = mysql_query($cmd,$link);
        $s=mysql_fetch_array($line10);

        if($s[0]==$fold_id) return false;
        if($s[0]==0) return true;

        return check_move($fold_id,$s[0]);

};

function copy_file_pole($id,$pole){
        global $link;
        global $tbl_goods_;
        global $pictures;
        global $tbl_pix;

        $file="";
        $cmd="SELECT $pole FROM $tbl_goods_ WHERE id=$id";
        $line10 = mysql_query($cmd,$link);
        $s=mysql_fetch_array($line10);

        //print $cmd.mysql_error();

        if($s[0]!=""){
        
          if($pictures=="file"){
            $m=explode(".",$s[0]);
            $tmp=tempnam("../upload/","");
            @unlink($tmp);
            $file=$tmp.".".$m[count($m)-1];
            if(is_file("../upload/".$s[0]))copy("../upload/".$s[0],"../upload/".$file);
            $file=eregi_replace("../upload/","",$file);
          } else {
            $cmd="SELECT * FROM $tbl_pix WHERE id=".$s[0];
            $line10 = mysql_query($cmd,$link);
            $ss=mysql_fetch_array($line10);

            $cmdq="INSERT INTO $tbl_pix (pix,name,type,seq,dat,descr,fizname,goods_id,goods_id_,pages_id,pages_id_) VALUES 
            ('".$ss["pix"]."','".$ss["name"]."',".$ss["type"].",".$ss["seq"].",".$ss["dat"].",'".$ss["descr"]."','".$ss["fizname"]."',0,0,0,0)";
            $line = mysql_query($cmdq,$link);
            $line = mysql_query("SELECT max(id) FROM $tbl_pix WHERE pix='".$ss["pix"]."'",$link);
            $st=mysql_fetch_array($line);
            $file=$st[0];
          };

        };
        return $file;
};

function ak_create_dirs($ak_id,$prefix)
{
	$droot = realpath(dirname(__FILE__) . '/../');
	$ddir = substr(strrev($ak_id),0,3);
	
	if (!is_dir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1)))
	{
		mkdir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1));
		chmod($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1),0777);
	}
	if (!is_dir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1)))
	{
		mkdir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1));
		chmod($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1),0777);
	}
	if (!is_dir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1)))
	{
		mkdir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1));
		chmod($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1),0777);
	}
	if (!is_dir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_id))
	{
		mkdir($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_id);
		chmod($droot.'/upload/'.$prefix.'/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_id,0777);
	}
	
	return substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_id;
}

function copy_file($good_id,$copys,$page_id,$fold_id,$p_id,$f_id,$name){
//ф-я копирует файл $good_id с количеством копий $copys, из папки fold_id и тома page_id в папку $f_id и том $p_id
        global $link;
        global $tbl_goods_;
        global $tbl_goods;
        global $maxpoles;

        $cmd="SELECT * FROM $tbl_goods WHERE id=".$fold_id;
        $line10 = mysql_query($cmd,$link);
        $s=mysql_fetch_array($line10);

        $cmd="SELECT * FROM $tbl_goods_ WHERE page_id=$page_id and goods_id=$fold_id and id=$good_id";
        $line10 = mysql_query($cmd,$link);
        $ss=mysql_fetch_array($line10);
    for($i=0;$i<$copys;$i++){
      $c1="";
      $c2="";
      $cmd="SHOW COLUMNS FROM $tbl_goods_";
      $line10 = mysql_query($cmd,$link);
      while($sss=mysql_fetch_array($line10)){
        
        $f="";
        if($sss[0]!="id" && $sss[0]!="page_id" && $sss[0]!="goods_id" && $sss[0]!="name"){
          
          $c1.=",";
          $c2.=",";
          $c1.=$sss[0];

          if(strpos(" ".$sss[0],"pole")){ // это поле
            if($s[$sss[0]."_"]>5 && $ss[$sss[0]]!=""){ // этот pole - файл
              $f=copy_file_pole($good_id,$sss[0]);
              $c2.="'$f'";
            } else {
              $c2.="'".$ss[$sss[0]]."'";
            };
          } else {
            if($sss[1]=="text" || $sss[1]=="longblob")$c2.="'".$ss[$sss[0]]."'";
              else $c2.=intval($ss[$sss[0]]);
          };

        };

      };

      $cmd="INSERT INTO $tbl_goods_ (name,page_id,goods_id $c1) VALUES ('$name',$p_id,$f_id $c2)";
      $line_ = mysql_query($cmd,$link);
      $ak_id = mysql_insert_id($link);
      
      /// NEWS IMAGES DUPLICATE
	if ($fold_id == 2 && $page_id = 2) {
		$ak_droot = realpath(dirname(__FILE__) . '/../');
		
		$ak_path = ak_create_dirs($ak_id, 'news_images');
		$ak_res = mysql_query(sprintf('SELECT news_id, seq, filepath FROM popcornnews_news_images WHERE news_id = %u', $good_id), $link);
		
		while ($ak_row = mysql_fetch_assoc($ak_res)) {
			copy(sprintf('%s/%s', $ak_droot, $ak_row['filepath']), sprintf('%s/upload/news_images/%s/%s', $ak_droot, $ak_path, basename($ak_row['filepath'])));
			mysql_query(
				sprintf(
					'INSERT INTO popcornnews_news_images SET timestamp = "%s", news_id = %u, seq = %u, filepath = "/upload/news_images/%s/%s"',
					date('Y-m-d H:i:s'), $ak_id, $ak_row['seq'], $ak_path, basename($ak_row['filepath'])
				),
				$link
			);
		}
	}
	/// \NEWSIMAGES  DUPLICATE
	
	/// MULTI TAGS DUPLICATE
	if ($fold_id == 2 && $page_id == 2) {
		mysql_sprintf(
			'INSERT INTO popcornnews_news_tags (tid, nid, regtime, type, news_regtime) SELECT tid, %u nid, %u regtime, type, news_regtime FROM popcornnews_news_tags WHERE nid = %u',
			$ak_id, time(), $good_id
		);
	}
	/// \MULTI TAGS DUPLICATE
	
	/// POLL DUPLICATE
	if ($fold_id == 2 && $page_id == 2) {
		mysql_sprintf(
			'INSERT INTO popcornnews_news_polls_options (nid, title, createtime) SELECT nid, title, %u time FROM popcornnews_news_polls_options WHERE nid = %u',
			time(), $ak_id
		);
	}
	/// \POLL DUPLICATE

      addlog("$cmd","Копирование файла",mysql_error());

      //print $cmd.mysql_error();
    };
};

function delete_file_trash($fold_id,$id){
//ф-я удаляет файл в карзину
        global $link;
        global $tbl_goods_;

        $cmd2="UPDATE $tbl_goods_ SET goods_id=0,page_id=1,goods_id_=$fold_id WHERE goods_id=$fold_id and id=$id";
        $line = mysql_query($cmd2,$link);

        addlog("$cmd2","Удаление файла в корзину",mysql_error());

        //print $cmd2.mysql_error();
        //exit(1);

};


function delete_file_pole($id){
//ф-я удаляет файл id (это или id из tbl_pix или дисковое физ. имя)      
      global $pictures;
      global $tbl_pix;
      global $link;

      if($pictures=="file"){
          ///@unlink("../upload/".$id);
      } else {
          $cmd2="DELETE FROM $tbl_pix WHERE id=".$id;
          $line = mysql_query($cmd2,$link);
          
          addlog("$cmd2","Удаление вложения",mysql_error());
      };
      
};

function delete_file($id){
//ф-я удаляет файл id 
        global $link;
        global $tbl_goods_;
        global $tbl_goods;
        global $maxpoles;
        global $pictures;

        $cmd="SELECT * FROM $tbl_goods_ WHERE page_id=1 and id=$id";
        $line10 = mysql_query($cmd,$link);
        $ss=mysql_fetch_array($line10);
//print $cmd.mysql_error()."<br>-$ss[0]-<br>";
        if($ss["goods_id"]==0)$ss["goods_id"]=$ss["goods_id_"];
        
        $cmd="SELECT * FROM $tbl_goods WHERE id=".intval($ss["goods_id"]);
        $line10 = mysql_query($cmd,$link);
        $s=mysql_fetch_array($line10);
//print $cmd.mysql_error();
        //удалим файлы из pole1-50
        for($i=1;$i<$maxpoles;$i++){
          
          if($s["pole".$i."_"]>5){
            //это файл
            delete_file_pole($ss["pole".$i]);
//print "123";
          };

        };

        //$line10 = mysql_query("DELETE FROM $tbl_goods_ WHERE page_id=1 and goods_id=$fold_id and id=$id",$link);
        $cmd2="DELETE FROM $tbl_goods_ WHERE page_id=1 and id=$id";
        $line10 = mysql_query($cmd2,$link);

        addlog("$cmd2","Удаление файла физически",mysql_error());

//print mysql_error();
//exit(1);

};

/*
function get_file_name($id){
// ф-я возвращает название файла
        global $tbl_goods_;
        global $link;
        $line = mysql_query("SELECT name FROM $tbl_goods_ WHERE id=".intval($id),$link);
        $s=mysql_fetch_array($line);
        return $s[0];
};

function get_fold_name($id){
// ф-я возвращает название папки
        global $tbl_goods;
        global $link;
        $line = mysql_query("SELECT name FROM $tbl_goods WHERE id=".intval($id),$link);
        $s=mysql_fetch_array($line);
        return $s[0];
};
*/

function get_three_move_($page_id,$goods_id,$fold_id,$step){
//ф-я возвращает дерево для селекта
        global $link;
        global $tbl_pages;
        global $tbl_goods;
        global $user_id;

        $cmd="SELECT * FROM $tbl_goods WHERE page_id=$page_id and goods_id=$goods_id ORDER BY name";
        $line1 = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line1)){

          if(check_fold($user_id,$page_id,$s[0])){
            $text.='<option style="color: #999999;" value="'.$s[0].'"';
            if($s[0]==$fold_id)$text.=' SELECTED';
            $text.='>'.$step.$s["name"].'</option>';
            $text.=get_three_move_($page_id,$s[0],$fold_id,$step."&nbsp;&nbsp;");
          };

        };

        //print $cmd.mysql_error();

        return $text;

};


function get_three_move($fold_id){
//ф-я возвращает дерево для селекта
        global $link;
        global $tbl_pages;
        global $tbl_goods;
        global $user_id;

        $line1 = mysql_query("SELECT * FROM $tbl_pages WHERE id>1 ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){
          
          if(check_fold($user_id,$s[0],0)){
            $text.='<option value="f'.$s[0].'">- '.$s["name"].'</option>';
            $text.=get_three_move_($s[0],0,$fold_id,"&nbsp;&nbsp;&nbsp;");
          };
        };

        return $text;

};

function update_order($fold_id,$order){
// ф-я устанавливает сортировку order для папки global fold_id
        global $link;
        global $tbl_goods;

        $cmd2="UPDATE $tbl_goods SET orderby='$order' WHERE id=$fold_id";
        $line = mysql_query($cmd2,$link);

};

function get_file_pages($fold_id,$tek){
//ф-я возвращает разбивку на страницы с файлами (tek - текущая страница)
        global $link;
        global $tbl_goods_;
        global $mpage_;
        global $page_id;
        global $showhead;
	$sql="SELECT count(id) FROM $tbl_goods_ WHERE $tbl_goods_.page_id=$page_id and goods_id=$fold_id";                             	
        $line1 = mysql_query($sql,$link);
//	print $sql;
	print mysql_error();
        $s=mysql_fetch_array($line1);

        if($s[0]>$mpage_){
          for($i=0;$i<$s[0];$i+=$mpage_){
            if($i==$tek) $text.='<div class="FilePageActive"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$fold_id.'&rand='.rand().'&tek='.$i.'">'.($i/$mpage_+1).'</a></div>';
              else $text.='<div class="FilePage"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$fold_id.'&rand='.rand().'&tek='.$i.'">'.($i/$mpage_+1).'</a></div>';
          };
        };

        return $text;
};

function get_file_letters($fold_id,$letter){
//ф-я разбивает алфавитную разбивку на страницы ($letter - текущая букавка)
        global $link;
        global $tbl_goods_;
        global $page_id;
        global $showhead;

        $cmd="SELECT id,name, UCASE(SUBSTRING(name,1,1)) as nm FROM $tbl_goods_ WHERE $tbl_goods_.page_id=$page_id and goods_id=$fold_id GROUP BY nm ORDER BY name";
        $line1 = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line1)){

            if($s[2]==$letter) $text.='<div class="FilePageActive"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$fold_id.'&rand='.rand().'&letter='.$letter.'&tek=-2">'.$s[2].'</a></div>';
              else $text.='<div class="FilePage"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$fold_id.'&rand='.rand().'&letter='.$s[2].'&tek=-2">'.$s[2].'</a></div>';

        };
        
        return $text;

};

function get_folds($page_id,$fold_id){
//ф-я возвращает подпапки для данной папке
        global $link;
        global $tbl_goods;
        global $showhead;
        global $user_id;

        /*
        $line1 = mysql_query("SELECT goods_id FROM $tbl_goods WHERE id=$fold_id and page_id=$page_id",$link);
        if($s=mysql_fetch_array($line1)){
          if($fold_id!=0){

            $text.='<div class="FolderBig"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'&rand='.rand().'"><img src="i/fbig_.gif" alt="перейти в родительскую папку" class="FolderBigImg"><br>..</a></div>';

          };
        };
        */

        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE goods_id=$fold_id and page_id=$page_id ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){
        
          if(check_fold($user_id,$page_id,$s[0])){  
            if(strlen($s["name"])>24)$s["name"]=substr($s["name"],0,21)."...";
            if($page_id!=1)$text.='<div class="FolderBig"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'&rand='.rand().'" onContextMenu="showcontext('."'".'fold'."'".','.$page_id.','.$s[0].');return false;"><img src="i/fbig.gif" alt="перейти в папку &laquo;'.$s["name"].'&raquo;" class="FolderBigImg"><br>'.$s["name"].'</a></div>';
              else $text.='<div class="FolderBig"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'&rand='.rand().'"><img src="i/fbig.gif" alt="перейти в папку &laquo;'.$s["name"].'&raquo;" class="FolderBigImg"><br>'.$s["name"].'</a></div>';
          };
        };
        
        return $text;
};

function get_newfile_form($page_id,$fold_id,$good_id){
//ф-я возвращает форму ввода файла 
        global $link;
        global $tbl_goods_;
        global $tbl_goods;
        global $maxpoles;
        global $pictures;
        global $showhead;
        global $tbl_pix;

        $line1 = mysql_query("SELECT * FROM $tbl_goods_ WHERE id=$good_id",$link);
        $s=mysql_fetch_array($line1);

        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $ss=mysql_fetch_array($line1);


        $text='<div class="FileBlock">
<script language="JavaScript"><!--
  function edit_field(field){
        eval("document.forms.editor.text.value=document.forms.workform2."+field+".value");
        document.forms.editor.field.value=field;
        document.forms.editor.submit();
  };
  function check_name(){
        if(document.forms.workform2.name.value==""){
                alert("Необходимо задать название файла!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

  function change_pole(name){
    try {
      eval("s=document.forms['."'".'workform2'."'".']."+name+".value.length;");
      eval("document.forms['."'".'workform2'."'".']."+name+"_.value=s;");
    } catch (e) {}
  }
//--></script>
<form name="editor" target="_blank" method="POST" action="editor.php?rnd='.rand().'" ENCTYPE="multipart/form-data">
<input type="hidden" name="field" value="">
<input type="hidden" name="text" value="">
</form>';

    if($s[0]==0)$text.='<h1>Cоздать новый файл<a name="new">&nbsp;</a></h1>';
      else $text.='<h1>редактирование файла &laquo;'.$s["name"].'&raquo;<a name="new">&nbsp;</a></h1>';

    $text.='<form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand='.rand().'" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid1" value="9">
    <input type="hidden" name="pageid" value="9">
    <input type="hidden" name="pageid2" value="9">
    <input type="hidden" name="page_id" value="'.$page_id.'">
    <input type="hidden" name="fold_id" value="'.$fold_id.'">
    <input type="hidden" name="good_id" value="'.$good_id.'">
    <input type="hidden" name="showhead" value="'.$showhead.'">';
    
    //<table cellspacing="1" width="100%" class="FileFormHeadTable">';

      $text.='<div class="FName">
        <h5>Название файла</h5>
        <table cellspacing="1" width="100%">
          <tr>                  
            <td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="name" value='."'".$s["name"]."'".' onChange="change_pole('."'".'name'."'".')" onPaste="change_pole('."'".'name'."'".')" onKeyUp="change_pole('."'".'name'."'".')" tabindex=1></td>
            <td class="FStat"><input class="Fcfreadonly" type="text" value="" readonly title="Количество символов" name="name_"></td>
          </tr>
        </table>
      </div>';
      
      /*
      <tr>
        <td class="FileFormHead" width="100%">Название<br><input class="cf" type="text" style="width:100%;" name="name" value='."'".$s["name"]."'".' onChange="change_pole('."'".'name'."'".')" onPaste="change_pole('."'".'name'."'".')" onKeyUp="change_pole('."'".'name'."'".')" tabindex=1></td>
        <td class="FileFormHead" width="1" valign="bottom"><input class="cfreadonly" type="text" style="width:40px;" value="60000" readonly title="Количество символов" name="name_"></td>
      </tr>';
      */

      /*
      if($ss["dat__"]!=0){
        
        $text.='<tr>
        <td class="FileFormHead" width="100%" colspan=2>Дата<br>
        <table cellpadding=0 cellspacing=0 border=0><tr>';
        
          //<input class="cf" type="text" style="width:100%;" name="dat" value='."'".$s["dat"]."'".' tabindex=2>
          if($s["dat"]==0)$s["dat"]=date("Ymd");
          
          $text.='<td>день: <select name="dat1" class=cf style="width: 100px;">';
            for($i=1;$i<32;$i++){
              $text.='<option value='.$i;
              if($i==substr($s["dat"],6,2))$text.=' SELECTED';
              $text.='>'.$i.'</option>';
            };
          $text.='</select></td>';

          $text.='<td>&nbsp;&nbsp;&nbsp;месяц: <select name="dat2" class=cf style="width: 100px;">';
            for($i=1;$i<13;$i++){
              $text.='<option value='.$i;
              if($i==substr($s["dat"],4,2))$text.=' SELECTED';
              $text.='>'.get_month2($i).'</option>';
            };
          $text.='</select></td>';

          $text.='<td>&nbsp;&nbsp;&nbsp;год: <select name="dat3" class=cf style="width: 100px;">';
            for($i=1900;$i<2101;$i++){
              $text.='<option value='.$i;
              if($i==substr($s["dat"],0,4))$text.=' SELECTED';
              $text.='>'.$i.'</option>';
            };
          $text.='</select></td>';

        $text.='</tr></table></td></tr>';

      } else $text.='<input class="cf" type="hidden" name="dat" value='."'".$s["dat"]."'".'>';
      */

      /*
      if($ss["seq__"]!=0)$text.='<tr>
        <td class="FileFormHead" width="100%" colspan=2>Последовательность<br><input class="cf" type="text" style="width:100%;" name="seq" value='."'".$s["seq"]."'".' tabindex=3></td>
      </tr>';  else $text.='<input class="cf" type="hidden" name="seq" value='."'".$s["seq"]."'".'>';
      */

      if($ss["seq__"]!=0)$text.='
      <div class="Ftext">
        <h5>Последовательность</h5>
        <table cellspacing="1" width="100%">
          <tr>                  
            <td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="seq" value='."'".$s["seq"]."'".' tabindex=3></td>
          </tr>
        </table>
      </div>
      '; else $text.='<input type="hidden" name="seq" value='."'".$s["seq"]."'".'>';


    //$text.='</table>';

    $scr='change_pole('."'".'name'."'".');';

    if($maxpoles>0){//до 50ти свойств файла

	$text .= '<script type="text/javascript" src="/js/jquery/jquery.js"></script>' . "\n";

      for($i=1;$i<=$maxpoles;$i++){
        
        if($ss["pole".$i]!=""){
          switch($ss["pole".$i."_"]){
            
            default:
            case(0)://строка

              $scr.='change_pole('."'".'pole'.$i."'".');';
              
              /*
              $text.='<tr><td class="FileFormHead" width="100%">'.$ss["pole".$i].'<br><input class="cf" type="text" style="width:100%;" name="pole'.$i.'" value='."'".$s["pole".$i]."'".' onChange="change_pole('."'".'pole'.$i."'".')" onPaste="change_pole('."'".'pole'.$i."'".')" onKeyUp="change_pole('."'".'pole'.$i."'".')" tabindex='.($i+4).'></td>
              <td class="FileFormHead" width="1" valign="bottom"><input class="cfreadonly" type="text" style="width:40px;" value="60000" readonly title="Количество символов" name="pole'.$i.'_"></td></tr>';
              */
              $column = false;
    if($fold_id == 11 && $page_id == 2 && $ss['pole'.$i] == 'Рубрика') {
        $hr = mysql_query('SELECT id, title FROM `pn_columns`');        
        $categories = array();
        if($hr) {
            while(false !== ($r = mysql_fetch_assoc($hr))) {
                $categories[] = $r;
            }
            mysql_free_result($hr);
        }
        $text.='<div class="Ftext">
        <h4>'.$ss["pole".$i].'</h4>
        <table cellspacing="1" width="100%"><tr>
        <select name="t_category[]" multiple="multiple" style="height: 150px;">';
            //<td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="pole'.$i.'" value='."'".$s["pole".$i]."'".' onChange="change_pole('."'".'pole'.$i."'".')" onPaste="change_pole('."'".'pole'.$i."'".')" onKeyUp="change_pole('."'".'pole'.$i."'".')" tabindex='.($i+4).'></td>
            //<td class="FStat"></td>
        $text .= '<option value="0">Без рубрики</option>';
        $tags = explode(',', $s['pole40']);
        foreach ($categories as $cat) {
            $def = (in_array($cat['id'], $tags)) ? ' selected="selected"' : '';
            $text .= '<option value="'.$cat['id'].'"'.$def.'>'.$cat['title'].'</option>';
        }
        $text .= '</select></tr></table></div>';
        $column = true;
    }              
              
	if (!($fold_id == 2 && $page_id == 2 && $ss['pole' . $i] == 'Названия фильмов с Киноафишы') && !$column) // news
    	{
              $text.='<div class="Ftext">
        <h5>'.$ss["pole".$i].'</h5>
        <table cellspacing="1" width="100%">
          <tr>                  
            <td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="pole'.$i.'" value='."'".$s["pole".$i]."'".' onChange="change_pole('."'".'pole'.$i."'".')" onPaste="change_pole('."'".'pole'.$i."'".')" onKeyUp="change_pole('."'".'pole'.$i."'".')" tabindex='.($i+4).'></td>
            <td class="FStat"><input class="Fcfreadonly" type="text" style="width:40px;" value="0" readonly title="Количество символов" name="pole'.$i.'_"></td>
          </tr>
        </table></div>';
	}

	/// POLL
	if ($fold_id == 2 && $page_id == 2 && $ss['pole' . $i] == 'Опрос') // news
	{
		$text .= <<<EOL
		<div class="FCheck" id="akPoll">
			<div id="akPollOptions">
EOL;
		// poll options
		$akPollOptions = mysql_fetch_all(mysql_sprintf('SELECT * FROM popcornnews_news_polls_options WHERE nid = %u ORDER BY id', $good_id));
		if ($akPollOptions) {
			foreach ($akPollOptions as &$akOption) {
				$text .= sprintf('<div><input type="text" name="akPollOptions[]" value="%s" /><span class="button" onclick="removeAkPollOption(event);">Убрать</span></div>', $akOption['title']);
			}
		}
		
		$text .=  <<<EOL
</div><span class="button" onclick="addAkPollOption();">Добавить</span></div>
<script type="text/javascript">
	function addAkPollOption() {
		$('div#akPoll div#akPollOptions').append('<div><input type="text" name="akPollOptions[]" /><span class="button" onclick="removeAkPollOption(event);">Убрать</span><div>');
	}
	
	function removeAkPollOption(e) {
		e = e || window.event;
		var ob = e.target || e.srcElement;
		
		$(ob).parent().remove();
	}
	
	addAkPollOption();
</script>
EOL;
	}
	
	
	
	/// \POLL

	/// KINOAFISHA MOVIES JSONP
	if ($fold_id == 2 && $page_id == 2 && $ss['pole' . $i] == 'Названия фильмов с Киноафишы') // news
	{
		$tab_index = $i+4;
		$text .= <<<EOL
		<div class="Ftext">
			<h5>{$ss["pole".$i]}</h5>
			<table cellspacing="1" width="100%">
				<tr>                  
					<td class="FInput">
						<input class="Fcf" type="hidden" name="pole$i" value="{$s["pole".$i]}">
						<select class="Fcf" type="text" style="width:100%;" name="autocomplete_pole$i" onChange="change_pole('autocomplete_pole$i')" onPaste="change_pole('autocomplete_pole$i')" onKeyUp="change_pole('autocomplete_pole$i')" tabindex="$tab_index"></select>
					</td>
					<td class="FStat">
						<input class="Fcfreadonly" type="text" style="width:40px;" value="0" readonly title="Количество символов" name="autocomplete_pole{$i}_">
					</td>
				</tr>
			</table>
		</div>
		
		<style type="text/css">
/* TextboxList sample CSS */
ul.holder { margin: 0; border: 1px solid #999; overflow: hidden; height: auto !important; height: 1%; padding: 4px 5px 0; }
*:first-child+html ul.holder { padding-bottom: 2px; } * html ul.holder { padding-bottom: 2px; } /* ie7 and below */
ul.holder li { float: left; list-style-type: none; margin: 0 5px 4px 0; white-space:nowrap;}
ul.holder li.bit-box, ul.holder li.bit-input input { font: 11px "Lucida Grande", "Verdana"; }
ul.holder li.bit-box { -moz-border-radius: 6px; -webkit-border-radius: 6px; border-radius: 6px; border: 1px solid #CAD8F3; background: #DEE7F8; padding: 1px 5px 2px; }
ul.holder li.bit-box-focus { border-color: #598BEC; background: #598BEC; color: #fff; }
ul.holder li.bit-input input { width: auto; overflow:visible; margin: 0; border: 0px; outline: 0; padding: 3px 0px 2px; } /* no left/right padding here please */
ul.holder li.bit-input input.smallinput { width: 20px; }

/* Facebook demo CSS */      
ul.holder { width: 500px; }
ul.holder { margin: 0 !important }
ul.holder li.bit-box, #apple-list ul.holder li.bit-box { padding-right: 15px; position: relative; z-index:1000;}
#apple-list ul.holder li.bit-input { margin: 0; }
#apple-list ul.holder li.bit-input input.smallinput { width: 5px; }
ul.holder li.bit-hover { background: #BBCEF1; border: 1px solid #6D95E0; }
ul.holder li.bit-box-focus { border-color: #598BEC; background: #598BEC; color: #fff; }
ul.holder li.bit-box a.closebutton { position: absolute; right: 4px; top: 5px; display: block; width: 7px; height: 7px; font-size: 1px; background: url('/i/fcbk-autocomplete-close.gif'); }
ul.holder li.bit-box a.closebutton:hover { background-position: 7px; }
ul.holder li.bit-box-focus a.closebutton, ul.holder li.bit-box-focus a.closebutton:hover { background-position: bottom; }

/* Autocompleter */

.facebook-auto { display: none; position: absolute; width: 512px; background: #eee; z-index:1001;}
.facebook-auto .default { padding: 5px 7px; border: 1px solid #ccc; border-width: 0 1px 1px;font-family:"Lucida Grande","Verdana"; font-size:11px; }
.facebook-auto ul { display: none; margin: 0; padding: 0; overflow: auto; position:absolute; z-index:9999}
.facebook-auto ul li {position:relative; height:100%; padding: 5px 12px; z-index: 1000; cursor: pointer; margin: 0; list-style-type: none; border: 1px solid #ccc; border-width: 0 1px 1px; font: 11px "Lucida Grande", "Verdana"; background-color: #eee;}
.facebook-auto ul li em { font-weight: bold; font-style: normal; background: #ccc; }
.facebook-auto ul li img {float: left; margin-right: 20px; height: 65px;}
.facebook-auto ul li div.year {font-weight: bold;}
.facebook-auto ul li div.clear {clear: both;}
.facebook-auto ul li.auto-focus { background: #4173CC; color: #fff; }
.facebook-auto ul li.auto-focus em { background: none; }
.deleted { background-color:#4173CC !important; color:#ffffff !important;}
.hidden { display:none;}

#demo ul.holder li.bit-input input { padding: 2px 0 1px; border: 1px solid #999; }
.ie6fix {height:1px;width:1px; position:absolute;top:0px;left:0px;z-index:1;}
		</style>
		
		<script type="text/javascript" src="/js/jquery/jquery.fcbkcomplete.new.js?new1"></script>
		<script type="text/javascript">
			$(function() {
				// preload already defined tags
				var ob = $("input[name=pole$i]");
				ids = ob.val().split(',');
				
				$.ajax({
					url: 'http://api.kinoafisha.info/p/moviesSuggest.php?',
					dataType: 'jsonp',
					data: {id: ids},
					success: function(response) {
						initAutocomplete(response);
					}
				});
				
				function initAutocomplete(presetElements) {
					$("select[name=autocomplete_pole$i]").fcbkcomplete({
						json_url: "http://api.kinoafisha.info/p/moviesSuggest.php?callback=?",
						addontab: true,
						height: 10,
						maxitems: 10,
						complete_text: 'Введите название фильма',
						preset_elements: presetElements,
						onremove: function(item) {
							var ob = $("input[name=pole$i]");
							if (ob.val().length != 0 && ob.val().substr(-1) != ',') {
								ob.val(ob.val() + ',');
							}
							
							item = eval('(' + item + ')');
							// console.log(item, ob.val().replace(item._value + ',', ''), ob.val());
							ob.val(ob.val().replace(item._value + ',', ''));
						},
						onselect: function(item) {
							var ob = $("input[name=pole$i]");
							if (ob.val().length != 0 && ob.val().substr(-2) != ',') {
								ob.val(ob.val() + ',');
							}
							item = eval('(' + item + ')');
							
							if (ob.val().indexOf(item._value + ',') != -1) {
								return false;
							}
							ob.val(ob.val() + item._value + ',');
						}
					});
				}
			});
		</script>
EOL;
	}
	/// \KINOAFISHA MOVIES JSONP

            break;

            case(5)://радиобатон

              //$scr.='change_pole('."'".'pole'.$i."'".');';
              
              /*
              $text.='<tr><td class="FileFormHead" width="100%">'.$ss["pole".$i].'</td><td class="FileFormHead"><input class="cf" type="radio" name="pole_radio" value="'.$i.'" tabindex='.($i+4);
              if($s["pole".$i]==$i) $text.=' CHECKED';
              $text.='></td></tr>';//<input type="hidden" name="pole'.$i.'" value="">
              */

                $text.='<div class="FCheck"><p><input type="radio" name="pole_radio" value="'.$i.'" tabindex='.($i+4);
              if($s["pole".$i]==$i) $text.=' CHECKED';
              $text.='>'.$ss["pole".$i].'</p></div>';

            break;

            case(1)://текст
                            
              $scr.='change_pole('."'".'pole'.$i."'".');';

              /*
              $text.='<tr><td colspan=2 class="FileFormHead">'.$ss["pole".$i].'</td></tr>
            <tr>
              <td class="FileFormInput" colspan="2">
                <textarea class="cf" rows="13" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).' onChange="change_pole('."'".'pole'.$i."'".')" onPaste="change_pole('."'".'pole'.$i."'".')" onKeyUp="change_pole('."'".'pole'.$i."'".')">'.check_str2($s["pole".$i]).'</textarea>
                <table cellspacing="0" width="100%">
                  <tr>
                    <td><input type="button" value="редактировать в редакторе" class="button" style="width:100%;" onClick="edit_field('."'".'pole'.$i."'".')"></td>
                    <td class="FileFormStat"><input class="cfreadonly" type="text" style="width:40px;" value="60000" readonly title="Количество символов" name="pole'.$i.'_"></td>
                  </tr>
                </table>
              </td>
            </tr>';
            */
      
		$text.=
		'<div class="Ftextarea">
			<h5>'.$ss["pole".$i].'</h5>
			<textarea class="Fcf" rows="13" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).' onChange="change_pole('."'".'pole'.$i."'".')" onPaste="change_pole('."'".'pole'.$i."'".')" onKeyUp="change_pole('."'".'pole'.$i."'".')">'.check_str2($s["pole".$i]).'</textarea>
			<table cellspacing="0" width="100%">
				<tr>
					<td width="100%"><input type="button" value="редактировать в редакторе" class="button" style="width:100%;" onClick="edit_field('."'".'pole'.$i."'".')"></td>
					<td><input class="Fcfreadonly" type="text" style="width:40px;" value="0" readonly title="Количество символов" name="pole'.$i.'_"></td>
				</tr>
			</table>
		</div>
		';

            break;


            case(3)://галочка

              /*
              $text.='<tr><td class="FileFormHead">'.$ss["pole".$i].' </td><td class="FileFormHead"><input tabindex='.($i+4).' class="cf" type="checkbox" value="Yes"';
              if($s["pole".$i]!="")$text.=" CHECKED";
              $text.=' name="pole'.$i.'"></td>';
              */

              $text.='<div class="FCheck"><p><input tabindex='.($i+4).' type="checkbox" value="Yes"';
              if($s["pole".$i]!="")$text.=" CHECKED";
              $text.=' name="pole'.$i.'">'.$ss["pole".$i].'</p></div>';


            break;

            case(4)://select файл

              $f_id=$ss["pole".$i];
              $line1z = mysql_query("SELECT name FROM $tbl_goods WHERE id=$f_id",$link);
              $s_=mysql_fetch_array($line1z);
              $f_name=$s_["name"];

              /*
              $text.='<tr><td class="FileFormHead" width="100%" colspan=2>Файл из папки "'.$f_name.'"<br><select class="cf" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).'><option value="">Выберите файл</option>';

              $line1z = mysql_query("SELECT id,name FROM $tbl_goods_ WHERE goods_id=$f_id",$link);
              while($s_=mysql_fetch_array($line1z)){
                $text.='<option value='.$s_[0];
                if($s_[0]==$s["pole".$i])$text.=' SELECTED';
                $text.='>'.$s_["name"].'</option>';
              };                        

              $text.='</select></td></tr>';
              */

              $text.='<div class="Ftext">
        <h5>Файл из папки "'.$f_name.'"</h5>
        <select class="Fcf" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).'><option value="">----------</option>';

              $line1z = mysql_query("SELECT id,name FROM $tbl_goods_ WHERE goods_id=$f_id ORDER BY name",$link);
              while($s_=mysql_fetch_array($line1z)){
                $text.='<option value='.$s_[0];
                if($s_[0]==$s["pole".$i])$text.=' SELECTED';
                $text.='>'.$s_["name"].'</option>';
              };                        

        $text.='</select></div>';

            break;  


            case(2)://select папка

              $f_id=$ss["pole".$i];
              $line1z = mysql_query("SELECT name FROM $tbl_goods WHERE id=$f_id ORDER BY name",$link);
              $s_=mysql_fetch_array($line1z);
              $f_name=$s_["name"];


              /*
              $text.='<tr><td class="FileFormHead" width="100%" colspan=2>Подпапка из папки "'.$f_name.'"<br><select class="cf" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).'><option value="">Выберите папку</option>';

              $line1z = mysql_query("SELECT id,name FROM $tbl_goods WHERE goods_id=$f_id",$link);
              while($s_=mysql_fetch_array($line1z)){
                $text.='<option value='.$s_[0];
                if($s_[0]==$s["pole".$i])$text.=' SELECTED';
                $text.='>'.$s_["name"].'</option>';
              };                        

              $text.='</select></td></tr>';
              */

              $text.='<div class="Ftext">
        <h5>Подпапка из папки "'.$f_name.'"</h5>
        <select class="Fcf" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).'><option value="">----------</option>';

              $line1z = mysql_query("SELECT id,name FROM $tbl_goods WHERE goods_id=$f_id ORDER BY name",$link);
              while($s_=mysql_fetch_array($line1z)){
                $text.='<option value='.$s_[0];
                if($s_[0]==$s["pole".$i])$text.=' SELECTED';
                $text.='>'.$s_["name"].'</option>';
              };                        

        $text.='</select></div>';
            
            break;


            case(6): // дата

              
              $text.='<div class="FDat"><h5>'.$ss["pole".$i].' <a href="javascript:;" onClick="document.workform2.pole'.$i.'d1.selectedIndex=new Date().getDate();document.workform2.pole'.$i.'d2.selectedIndex=new Date().getMonth()+1;document.workform2.pole'.$i.'d3.value=new Date().getFullYear();">установить текущую</a> | <a href="javascript:;" onClick="document.workform2.pole'.$i.'d1.value=0;document.workform2.pole'.$i.'d2.value=0;document.workform2.pole'.$i.'d3.value=0;">сбросить</a></h5>
        <table cellpadding=0 cellspacing=0 border=0><tr>';
        
          //<input class="cf" type="text" style="width:100%;" name="dat" value='."'".$s["dat"]."'".' tabindex=2>
          //if($s["pole".$i]==0)$s["pole".$i]=date("Ymd");
          
          $text.='<td>день: <select name="pole'.$i.'d1" class=cf style="width: 100px;">';
              $text.='<option value=0';
              if(!substr($s["pole".$i],6,2))$text.=' SELECTED';
              $text.='>--</option>';

            for($ii=1;$ii<32;$ii++){
              $text.='<option value='.$ii;
              if($ii==substr($s["pole".$i],6,2))$text.=' SELECTED';
              $text.='>'.$ii.'</option>';
            };
          $text.='</select></td>';

          $text.='<td>&nbsp;&nbsp;&nbsp;месяц: <select name="pole'.$i.'d2" class=cf style="width: 100px;">';
              $text.='<option value=0';
              if(!substr($s["pole".$i],4,2))$text.=' SELECTED';
              $text.='>--</option>';
            for($ii=1;$ii<13;$ii++){
              $text.='<option value='.$ii;
              if($ii==substr($s["pole".$i],4,2))$text.=' SELECTED';
              $text.='>'.get_month2($ii).'</option>';
            };
          $text.='</select></td>';

          $text.='<td>&nbsp;&nbsp;&nbsp;год: <select name="pole'.$i.'d3" class=cf style="width: 100px;">';
              $text.='<option value=0';
              if(!substr($s["pole".$i],0,4))$text.=' SELECTED';
              $text.='>--</option>';
            for($ii=1900;$ii<2101;$ii++){
              $text.='<option value='.$ii;
              if($ii==substr($s["pole".$i],0,4))$text.=' SELECTED';
              $text.='>'.$ii.'</option>';
            };
          $text.='</select></td>';

        $text.='</tr></table></div>';  
              

            break;

            case(7): // время

              
              $text.='<div class="FDat">
        <h5>'.$ss["pole".$i].'   <a href="javascript:;" onClick="document.workform2.pole'.$i.'t1.value=new Date().getHours();document.workform2.pole'.$i.'t2.value=new Date().getMinutes();document.workform2.pole'.$i.'t3.value=new Date().getSeconds();">установить текущее</a></h5>
        <table cellpadding=0 cellspacing=0 border=0><tr>';
        
          //<input class="cf" type="text" style="width:100%;" name="dat" value='."'".$s["dat"]."'".' tabindex=2>
          if($s["pole".$i]==0)$s["pole".$i]=date("His");
          while(strlen($s["pole".$i])<6)$s["pole".$i]="0".$s["pole".$i];
          
          $text.='<td>часы: <select name="pole'.$i.'t1" class=cf style="width: 100px;">';
            for($ii=0;$ii<24;$ii++){
              $text.='<option value='.$ii;
              if($ii==substr($s["pole".$i],0,2))$text.=' SELECTED';
              $text.='>'.$ii.'</option>';
            };
          $text.='</select></td>';

          $text.='<td>&nbsp;&nbsp;&nbsp;минуты: <select name="pole'.$i.'t2" class=cf style="width: 100px;">';
            for($ii=0;$ii<60;$ii++){
              $text.='<option value='.$ii;
              if($ii==substr($s["pole".$i],2,2))$text.=' SELECTED';
              $text.='>'.$ii.'</option>';
            };
          $text.='</select></td>';

          $text.='<td>&nbsp;&nbsp;&nbsp;секунды: <select name="pole'.$i.'t3" class=cf style="width: 100px;">';
            for($ii=0;$ii<60;$ii++){
              $text.='<option value='.$ii;
              if($ii==substr($s["pole".$i],4,2))$text.=' SELECTED';
              $text.='>'.$ii.'</option>';
            };
          $text.='</select></td>';

        $text.='</tr></table></div>';  
              

            break;

            case(17)://gif/jpg картинка

              $text.='<div class="FPictures">
              <h5>'.$ss["pole".$i].'</h5>
              <input class="cf" type="file" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).'>';

              $text.='<table cellspacing="0" width="100%"><tr>

        <td class="FileFormInput">';
          

          if($s["pole".$i]!=""){
            $text.='<img src="/upload/_200_70_70_'.$s["pole".$i].'" height="70" border="0" alt="" vspace="4" hspace="10" align="left">
            <ul style="margin:0px 0px 0px 120px;">
              <li style="list-style-image: url('."'".'i/open.gif'."'".'); vertical-align:top; padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base")$text.='../showpix.php?id='.$ss["pole".$i].'&rand='.rand().'&type='.$ss["pole".$i]; else
              $text.='../upload/'.$s["pole".$i].'?rand='.rand();

              $text.='" target=_blank>увеличить</a></li>
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="../showpix.php?id='.$s["pole".$i].'&rand='.rand().'&mode=save&type='.$ss["pole".$i."_"].'">сохранить</a></li>
              <li style="list-style-image: url('."'".'i/i2.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="javascript:{if(window.confirm('."'Удалить?      Внимание!    Восстановление невозможно!'".'))location.href='."'".'./goods.php?showhead='.$showhead.'&pageid=10&r='.rand().'&good_id='.$good_id.'&page_id='.$page_id.'&fold_id='.$fold_id.'&file=pole'.$i.''."'".'}">удалить</a></li>
            </ul>';
          };

        $text.='</td>';

              if($ss["pole".$i] == "главное фото") {
                  $text .= '<td valign="top">';
                  $text .= '<a href="#" class="open-main-photo-frame">выбрать фото из другой новости</a>';
                  $text .= '<style>
                  .main-photo-selector {
                    position: absolute;
                    width: 610px;
                    height: 510px;
                    background-color: #fff;
                    left: 50%;
                    margin-left: -300px;
                    overflow: hidden;
                    display: none;
                    border: 1px solid #666;
                  }
                  a.close-main-photo-frame {
                    position: absolute;
                    display: block;
                    top: 0;
                    right: 0;
                    padding: 4px;
                    background-color: #fff;
                  }
                  #main-photo-selector-frame {
                    display: block;
                    top: 5px;
                    left: 5px;
                    position: absolute;
                    width: 598px;
                    height: 498px;
                    border: none;
                  }
                  </style>';
                  $text .= <<<EOL
<script type="text/javascript">
    $(document).ready(function(){
        $('a.open-main-photo-frame').click(function() {
            $('#main-photo-selector-frame').attr('src', '/manager/admin.php?type=newsimages');
            $('div.main-photo-selector').show();
            return false;
        });
        $('a.close-main-photo-frame').click(function() {
            $('div.main-photo-selector').hide();
            return false;
        });
    });
</script>
EOL;

                  $text .= '<div class="main-photo-selector">
                  <iframe id="main-photo-selector-frame" src="/manager/admin.php?type=newsimages"></iframe>
                  <a class="close-main-photo-frame" href="#">[закрыть]</a>
                  </div>';
                  $text .= '<input type="hidden" id="extern_main_photo" name="extern_main_photo" value="" />';
                  $text .= '</td>';
              }

        $text .= '</tr></table></div>';


            break;

            case(25)://файл/данные
            case(26)://файл word
            case(16)://файл excel
            case(18)://SWF файл
            case(19)://PDF файл
            case(20)://AVI видео
            case(21)://MPEG видео
            case(22)://WAV звук
            case(23)://MP3 музыка
            case(24)://ZIP архив


                $text.='<div class="FPictures">
                
                <h5>'.$ss["pole".$i].'</h5>
                <input class="cf" type="file" style="width:100%;" name="pole'.$i.'" tabindex='.($i+4).'>

                <table cellspacing="0" width="100%"><tr>

        <td class="FileFormInput">';

          if($s["pole".$i]!=""){
            $text.='<ul style="margin:0px 0px 0px 15px;">
              <li style="list-style-image: url('."'".'i/open.gif'."'".'); vertical-align:top; padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base")$text.='../showpix.php?id='.$ss["pole".$i].'&rand='.rand().'&type='.$ss["pole".$i];
                else $text.='../upload/'.$s["pole".$i].'?rand='.rand();

              $text.='" target=_blank>открыть</a></li>
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="../showpix.php?id='.$s["pole".$i].'&rand='.rand().'&mode=save&type='.$ss["pole".$i."_"].'">сохранить</a></li>
              <li style="list-style-image: url('."'".'i/i2.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="javascript:{if(window.confirm('."'Удалить?      Внимание!    Восстановление невозможно!'".'))location.href='."'".'./goods.php?showhead='.$showhead.'&pageid=10&r='.rand().'&good_id='.$good_id.'&page_id='.$page_id.'&fold_id='.$fold_id.'&file=pole'.$i.''."'".'}">удалить</a></li>
            </ul>';
          };

                $text.='</td></tr></table></div>';


            break;



        
          };
        };
      };
      
	/// NEW IMAGE UPLOAD
	if ($fold_id == 2 && $page_id == 2) // news
	{
		$text .= '<input type="hidden" name="ak_add_news_images" value="1">';
		$text .= <<<EOL
		<script type="text/javascript">
		function addnews_image()
		{
			var html = '<div style="clear:both;">';
			html    += '	<input type="file" name="ak_news_images[]" style="width:100%;"><br/><br/>';
			html    += '	<input type="text" name="ak_news_images_name[]" style="width:60%;"> название фото<br/>';
			html    += '	<input type="text" name="ak_news_images_caption[]" style="width:60%;"> подпись к фото<br/><br/>';
			html    += '</div>';
			
			$('#ak_news_images').append(html);
		}
		
		$(function(){
				$('#ak_news_images_action').click(function(){
					var act = $(this).find('option:selected');
					
					if (act.val() == 'make_first')
					{
						if ($('input[name=ak_news_image_checkbox[]]:checked').size() != 1)
						{
							alert('Выберите только один изображение!');
							$('#ak_news_images_action option:eq(0)').attr('selected',true);
							return false;
						}
					}
				});
		});
		
		</script>
		<div class="FCheck" id="ak_news_images">
			<h2>Изображения</h2>
EOL;
			
			$ak_tmpl = <<<EOL
			<div style="float:left;margin:0 5px 0 0;">
				<a href="%s" target="_blank"><img src="/k/news/130%s" width="130" alt="%s" title="%s" /></a>
				<div style="position:relative;top:-20px;left:-4px;margin-bottom:-20px;float:left;"><input type="checkbox" name="ak_news_image_checkbox[]" value="%u"></div>
			</div>
EOL;
		$result = mysql_query(sprintf('SELECT * FROM popcornnews_news_images WHERE news_id = %u ORDER BY seq ASC',$good_id));
		$ak_num_rows = mysql_num_rows($result);
		
		if (!$ak_num_rows) $text .= '<div><strong>Нет изображений</strong></div>';
		
		while ($row = mysql_fetch_assoc($result))
		{
			$text .= sprintf($ak_tmpl,$row['filepath'],$row['filepath'],$row['name'],htmlspecialchars($row['name']),htmlspecialchars($row['id']));
		}
		$text .= <<<EOL
		</div>
		<div style="clear:both;" class="FCheck">
			<select id="ak_news_images_action" name="ak_news_image_action">
				<option value="">-выберите-</option>
				<option value="make_first">Поставить первым</option>
				<option value="delete">Удалить выбранные</option>
			</select>
			<div><a href="#" onclick="addnews_image();return false;">Добавить изображение</a></div>
		</div>
EOL;
	}
	/// \NEW IMAGE UPLOAD
	
	/// MULTI TAGS
	if ($fold_id == 2 && $page_id == 2) // news
	{
		// events list
		$akEvents = mysql_fetch_all(mysql_sprintf('SELECT id, name FROM %s WHERE goods_id = 11 AND page_id = 2 ORDER BY name, id DESC', $tbl_goods_));
		$akEvents = json_encode(mixed_iconv($akEvents, 'WINDOWS-1251', 'UTF-8'));
		// persons list
		$akPersons = mysql_fetch_all(mysql_sprintf('SELECT id, name FROM %s WHERE goods_id = 3 AND page_id = 2 ORDER BY name, id DESC', $tbl_goods_));
		$akPersons = json_encode(mixed_iconv($akPersons, 'WINDOWS-1251', 'UTF-8'));
		
		// selected events
		$akEventsSelected = mysql_fetch_all(mysql_sprintf('SELECT id, tid FROM popcornnews_news_tags WHERE nid = %u AND type = "events" ORDER BY id DESC', $good_id));
		$akEventsSelected = json_encode(mixed_iconv($akEventsSelected, 'WINDOWS-1251', 'UTF-8'));
		// selected persons
		$akPersonsSelected = mysql_fetch_all(mysql_sprintf('SELECT id, tid FROM popcornnews_news_tags WHERE nid = %u AND type = "persons" ORDER BY id DESC', $good_id));
		$akPersonsSelected = json_encode(mixed_iconv($akPersonsSelected, 'WINDOWS-1251', 'UTF-8'));
		
		$text .= <<<EOL
<style type="text/css">
	span.button {cursor: pointer;}
	div#ak_tags div div, div#ak_tags span, div#ak_columns div div, div#ak_columns span {margin: 5px;}
</style>
<script type="text/javascript">
	var events = eval({$akEvents});
	var persons = eval({$akPersons});
	
	var eventsSelected = eval({$akEventsSelected});
	var personsSelected = eval({$akPersonsSelected});
	
	function akGetOptionsFor(data) {
		var out = '<option value="0">Выберите</option>';
		for (var i = 0; i < data.length; i++) {
			out += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
		}
		return out;
	}
	function akAddPersonsSelect() {
		$('div#ak_tags div.persons').append('<div><select name="ak_persons_tags[]">' + akGetOptionsFor(persons) + '</select><span onclick="$(this).parent().remove();" class="button">Удалить</span></div>');
	}
	function akAddEventsSelect() {
		$('div#ak_tags div.events').append('<div><select name="ak_events_tags[]">' + akGetOptionsFor(events) + '</select><span onclick="$(this).parent().remove();" class="button">Удалить</span></div>');
	}
	function akAddPersonsSelected() {
		var out = '';
		var i, j = 0;
		for (i = 0; i < personsSelected.length; i++) {
			out += '<div><select name="ak_persons_tags[]">';
			for (j = 0; j < persons.length; j++) {
				out += '<option value="' + persons[j].id + '"' + (persons[j].id == personsSelected[i].tid ? ' selected' : '') + '>' + persons[j].name + '</option>';
			}
			out += '</select><span onclick="$(this).parent().remove();" class="button">Удалить</span></div>';
		}
		$('div#ak_tags div.persons').append(out);
	}
	function akAddEventsSelected() {
		var out = '';
		var i, j = 0;
		for (i = 0; i < eventsSelected.length; i++) {
			out += '<div><select name="ak_events_tags[]">';
			for (j = 0; j < events.length; j++) {
				out += '<option value="' + events[j].id + '"' + (events[j].id == eventsSelected[i].tid ? ' selected' : '') + '>' + events[j].name + '</option>';
			}
			out += '</select><span onclick="$(this).parent().remove();" class="button">Удалить</span></div>';
		}
		$('div#ak_tags div.events').append(out);
	}
</script>

<div class="FCheck" id="ak_tags">
	<h2 class="persons">Теги персон</h2>
	<div class="persons"></div>
	<span class="button" onclick="akAddPersonsSelect();">Добавить</span>
	
	<h2>Теги событий</h2>
	<div class="events"></div>
	<span class="button" onclick="akAddEventsSelect();">Добавить</span>
</div>

<script type="text/javascript">
	akAddEventsSelected();
	akAddPersonsSelected();
</script>

EOL;
	}
	/// \MULTI TAGS

    }
/*columns*/
    if ($fold_id == 2 && $page_id == 2) {
        
        $hr = mysql_query('SELECT id, title FROM pn_columns');
        $columns = array();
        while(false != ($r = mysql_fetch_assoc($hr))) {
            $columns[] = $r;
        }
        mysql_free_result($hr);
        
        $hr = mysql_query("
        SELECT cid FROM pn_columns_news_link
        WHERE nid = {$good_id}
        ");
        $selected = array();
        while(false != ($r = mysql_fetch_assoc($hr))) {
            $selected[] = $r;
        }
        mysql_free_result($hr);
        
        function createSelect($columns, $id = 0) {
            $out = '<select name="columns[]">';
            foreach ($columns as $i => $column) {
                if($column['id'] == $id) {
                    $out .= '<option value="'.$column['id'].'" selected="selected">'.$column['title'].'</option>';
                } else {
                    $out .= '<option value="'.$column['id'].'"'.($i == 0 ? ' default="default"' : '').'>'.$column['title'].'</option>';
                }
            }
            $out .= '</select>';
            return $out;
        }
        
        $defSelector = createSelect($columns, 0);
        $currentSelected = '';
        foreach ($selected as $item) {
            $sel = createSelect($columns, $item['cid']);
            $currentSelected .= '<div>'.$sel.'<span onclick="$(this).parent().remove();" class="button">Удалить</span></div>';
        }
        
        $text .= <<<EOL

<script type="text/javascript">
	function akAddColumnsSelect() {
		$('div#ak_columns div.columns').append('<div>{$defSelector}<span onclick="$(this).parent().remove();" class="button">Удалить</span></div>');
    }
</script>
<input type="hidden" name="ak_columns_action" value="" />        
<div class="FCheck" id="ak_columns">
	<h2 class="persons">Рубрики</h2>	
	<div class="columns">{$currentSelected}</div>
	<span class="button" onclick="akAddColumnsSelect();">Добавить</span>
</div>        
EOL;

    }
    /*-------*/

    $text.='<table cellspacing="1" width="100%">
      <tr>
        <td>&nbsp;</td>
        <td class="FileFormSubnit">
          <table cellspacing="3" width="100%">
            <tr>
              <td><input tabindex=60 type="submit" value="Сохранить файл" class="button" style="font-weight:700"></td>
              <td><input  onClick=javascript:{setTimeout("'.$scr.'",10);}; tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
              <td align="right" width="100%"><input tabindex=62 type="checkbox" name="readonly" value=1';
              if($s["readonly"]!=0) $text.=" CHECKED";
              $text.='>запретить удаление файла</td>
            </tr>
          </table>
        </td>
      </tr></table><script>
'.$scr.'
      </script></form>';


    if($good_id!=0){


      $line1 = mysql_query("SELECT count(id) FROM $tbl_pix WHERE goods_id_=$good_id",$link);
      $ss=mysql_fetch_array($line1);

      if($ss[0]>0){

    $text.='<h1>Вложения к файлу</h1><table cellspacing="1" width="100%" class="TableFiles">';
      
      $line1 = mysql_query("SELECT * FROM $tbl_pix WHERE goods_id_=$good_id",$link);
      while($ss=mysql_fetch_array($line1)){

        if($ss["type"]!=7 && $ss["type"]!=0){ // это НЕ картинка
          $text.='<tr>
            <td class="TF"><a href="';

            if($pictures=="base") $text.= '../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type='.$ss["type"];
                else $text.= '../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type='.$ss["type"];
          
          if($pictures=="base") $text.= '../upload/'.$ss[0].'&rand='.rand();
                else $text.= '../upload/'.$ss["diskname"].'&rand='.rand();
              
              if($pictures=="file"){
                 $text.= '&filename='.$ss["fizname"];
              };

            $text.='">'.$ss["name"].'</a> ('.$ss["fizname"].')</td>
            <td class="TF">'.get_pix_types_name($ss["type"]).'</td>
            <td class="TFaction"><a href="';
            
            if($pictures=="base") $text.= '../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type='.$ss["type"];
                else $text.= '../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type='.$ss["type"];
              
              if($pictures=="file"){
                 $text.= '&filename='.$ss["fizname"];
              };

            $text.='"><img src="i/open2.gif" alt="открыть" width="16" height="15" hspace="4"></a>
            <a href="./goods.php?page_id='.$page_id.'&pageid=2&fold_id='.$fold_id.'&subpageid=19&good_id='.$good_id.'&rnd='.rand().'&file_id='.$ss[0].'&showhead='.$showhead.'"><img src="i/open2.gif" alt="редактировать" width="16" height="15" hspace="4"></a>';

            $text.= '<a href="javascript:{if(window.confirm('."'Удалить?      Внимание!    Восстановление невозможно!'".'))location.href='."'".'./goods.php?showhead='.$showhead.'&pageid=35&r='.rand().'&good_id='.$good_id.'&file_id='.$ss[0].'&page_id='.$page_id.'&fold_id='.$fold_id."'".'}">';

            $text.='<img src="i/del.gif" alt="удалить" width="15" height="15" hspace="4"></a></td></tr>';
        
        } else { // картинка

          $text.='<tr><td colspan=3><table cellspacing="0" width="100%"><tr>

        <td class="FileFormName">'.$ss["name"].'</td>
        <td class="FileFormInput">';

          //if($s["pole".$i]!=""){
            $text.='<img src="../showimg.php?id=';
            
            if($pictures=="base") $text.=$ss[0];
              else $text.=$ss["diskname"];

            $text.='&wd=200&hd=70" height="70" border="0" alt="" vspace="4" hspace="10" align="left">
            <ul style="margin:0px 0px 0px 120px;">
              <li style="list-style-image: url('."'".'i/open.gif'."'".'); vertical-align:top; padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base")$text.='../showpix.php?id='.$ss[0].'&rand='.rand().'&type=7';
                else $text.='../upload/'.$ss["diskname"].'?rand='.rand();

              $text.='" target=_blank>увеличить</a></li>
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base") $text.='../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type=7';
                else $text.='../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type=7';
                //$text.='../upload/'.$ss["diskname"].'?rand='.rand().';

              //if($pictures=="base") $text.=$ss[0];
              //  else $text.=$ss["diskname"];
              
              if($pictures=="file"){
                $text.='&filename='.$ss["fizname"];
              };

              $text.='">сохранить</a></li>
              
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="./goods.php?page_id='.$page_id.'&good_id='.$good_id.'&pageid=2&fold_id='.$fold_id.'&subpageid=19&rnd='.rand().'&file_id='.$ss[0].'&showhead='.$showhead.'">редактировать</a></li>

              <li style="list-style-image: url('."'".'i/i2.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="javascript:{if(window.confirm('."'Удалить?      Внимание!    Восстановление невозможно!'".'))location.href='."'".'./goods.php?showhead='.$showhead.'&good_id='.$good_id.'&pageid=35&r='.rand().'&file_id='.$ss[0].'&page_id='.$page_id.'&fold_id='.$fold_id."'".'}">удалить</a></li>
            </ul>';
          //};

        $text.='</td>
        </tr></table></td>
      </tr>';

        //print $text;

        };
     
      };
     
      $text.='</table>';
      };



	/// NEW IMAGE UPLOAD - OLD IS NOT NEED
	if ($fold_id != 2 && $page_id != 2) // news
	{
      $text.='<h1>Прикрепить документ или картинку к файлу</h1>
<script language="JavaScript"><!--
  function check_name3(){
        if(document.forms.workform3.name.value==""){
                alert("Необходимо задать название прикрепляемого файла!");
                document.forms.workform3.name.focus();
                return false;
        };
        return true;
  };
//--></script>

      <form class="FileForm" ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform3" onSubmit="return check_name3();">
    <input type="hidden" name="pageid" value="31">
    <input type="hidden" name="page_id" value="'.$page_id.'">
    <input type="hidden" name="fold_id" value="'.$fold_id.'">
    <input type="hidden" name="good_id" value="'.$good_id.'">
    <input type="hidden" name="showhead" value="'.$showhead.'">

      <table cellspacing="1" width="100%">
        <tr>
          <td class="FileFormName">Имя</td>
          <td class="FileFormInput"><input class="cf" type="text" style="width:100%;" name="name"></td>
        </tr>

        <tr>
          <td class="FileFormName">Тип</td>
          <td class="FileFormInput"><select class="cf2" name="type" style="width:100%;">'.get_select_types(7).'</select></td>
        </tr>
        
        <tr>
          <td class="FileFormName">Путь к документу или картинке</td>
          <td class="FileFormInput" colspan="2"><input class="cf" type="file" style="width:100%;" name="userfile"></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td class="FileFormSubnit"><input type="submit" value="Загрузить" class="button" style="font-weight:700"></td>
      </tr>
      </table>
      </form>';
      }
      /// \NEW IMAGE UPLOAD - OLD IS NOT NEED

      };






      $text.='</div>';


    return $text;

};


function get_top_navi($page_id,$fold_id){
//ф-я возвращает сверху страницы навигацию - выпадающее меню и т.д.
        global $showhead;
        global $tbl_goods;
        global $link;

//print "!!!!!!!!!!=$showhead=";

        

  $text.='<div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>';
        
        $line1 = mysql_query("SELECT goods_id FROM $tbl_goods WHERE id=$fold_id and page_id=$page_id",$link);
        if($s=mysql_fetch_array($line1)){
          if($fold_id!=0){

            $text.='<td valign="top" class="NavBlockImg"><a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'&rand='.rand().'"><img src="i/c1.gif" alt=".." width="20" height="19"></a></td>';

          } else $text.='<td valign="top" class="NavBlockImg"><img src="i/c1_.gif" alt=".." width="20" height="19"></td>';
        
        } else $text.='<td valign="top" class="NavBlockImg"><img src="i/c1_.gif" alt=".." width="20" height="19"></td>';

        $text.='<td class="NavBlockAddress"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">'.get_path($page_id,$fold_id).'</td>';
        
        if($showhead==1 || $showhead==-2)$text.='<form><td class="NavBlockDescr" style="padding:0px 0px 0px 3px;" valign="top"><input type="button" class="button" style="width:20px;"  value=".." onclick="setTimeout('."'shownavi();'".',10);"></td></form>';
      
      $text.='</tr>
    </table>
  </div>';

  return $text;

};

function get_path_fold($page_id,$fold_id){
//ф-я возвращает путь к папке 
        global $link;
        global $tbl_goods;
        global $showhead;

        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        if($s=mysql_fetch_array($line1)){
          $text=get_path_fold($page_id,$s["goods_id"]).' / <a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'">'.$s["name"].'</a>';
        };

        return $text;
};

function get_path($page_id,$fold_id){
//ф-я возвращает путь к папке 
        global $link;
        global $tbl_pages;
        global $tbl_goods;
        global $showhead;

        $line1 = mysql_query("SELECT * FROM $tbl_pages WHERE id=$page_id",$link);
        if($s=mysql_fetch_array($line1)){
          $text.=' <a href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$s[0].'">'.$s["name"].'</a> ';
          if($fold_id!=0) $text.=get_path_fold($page_id,$fold_id);
        };

        return $text;

};


function get_popup_three_($page_id,$fold_id,$f_id){
//ф-я возвращает путь к папке для попапа 
        global $link;
        global $tbl_goods;
        global $showhead;
        global $user_id;

        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE goods_id=$f_id and page_id=$page_id ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){

          if(check_fold($user_id,$page_id,$s[0])){
            $text.='<li><a target=_parent href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'">'.$s["name"].'</a>';
          
            $text.=get_popup_three_($page_id,$fold_id,$s[0]);
          
            $text.="</li>";
          };

        };

        if($text!="")$text='<ul class="PopTreeUl">'.$text.'</ul>';

        return $text;
};

function get_popup_three($page_id,$fold_id){
//ф-я возвращает путь к папке для попапа
        global $link;
        global $tbl_pages;
        global $tbl_goods;
        global $showhead;
        global $user_id;

        $text='<ul class="PopTreeUl">';

        $line1 = mysql_query("SELECT * FROM $tbl_pages ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){

          if(check_fold($user_id,$s[0],0)){

            if($s[0]!=1){
              $text.='<li><a target=_parent href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$s[0].'">'.$s["name"].'</a> ';
              if($page_id==$s[0]) $text.=get_popup_three_($s[0],$fold_id,0);
              $text.="</li>";
            } else { // корзина
              $text1.='<li><a target=_parent href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$s[0].'">'.$s["name"].'</a> ';
              if($page_id==$s[0]) $text1.=get_popup_three_($s[0],$fold_id,0);
              $text1.="</li>";
            };
          
          };

        };

        $text.=$text1;

        $text.='</ul>';

        return $text;

};


function get_popup_three_count_($page_id,$fold_id,$f_id){
//ф-я возвращает путь к папке для попапа 
        global $link;
        global $tbl_goods;
        global $showhead;
        global $user_id;
//print "???";            
        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE goods_id=$f_id and page_id=$page_id ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){

          if(check_fold($user_id,$page_id,$s[0])){
//print "!!!";            
            $count+=get_popup_three_count_($page_id,$fold_id,$s[0]);
          
            $count++;
          };

        };


        return $count;
};


function get_popup_three_count($page_id,$fold_id){
//ф-я возвращает количество папок доступных
        global $link;
        global $tbl_pages;
        global $tbl_goods;
        global $showhead;
        global $user_id;

//print "ZZZ";            
        $line1 = mysql_query("SELECT * FROM $tbl_pages ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){
//print "111";
          if(check_fold($user_id,$s[0],0)){
          //$count++;
//print $s[0]."<br>";
//print "222";            
            if($s[0]!=1){
//print "333";            
              $count++;
              if($page_id==$s[0]) $count+=get_popup_three_count_($s[0],$fold_id,0);
            } else { // корзина
//print "444";            
              $count++;
              if($page_id==$s[0]) $count+=get_popup_three_count_($s[0],$fold_id,0);
            };
            
          };

        };

        return $count;

};


function get_popup_three_1($page_id,$fold_id,$f_id){
//ф-я возвращает путь к папке для попапа 
        global $link;
        global $tbl_goods;
        global $showhead;
        global $user_id;

        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE goods_id=$f_id and page_id=$page_id ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){
          
          if(check_fold($user_id,$page_id,$s[0])){

            $text.='<li> ';
            if($fold_id!=$s[0])$text.='<input type="checkbox" name="dir[]" value="'.$s[0].'" id=p'.$s[0].'> <a href="javascript:{switch_chk('.$s[0].');}">';
              else $text.="<b>";
            $text.=' '.$s["name"].'</a>';
            if($fold_id==$s[0])$text.="</b>";

            $text.=get_popup_three_1($page_id,$fold_id,$s[0]);

            $text.="</li>";

          };

        };

        if($text!="")$text='<ul class="PopTreeUl">'.$text.'</ul>';

        return $text;
};

function get_popup_three1($page_id,$fold_id){
//ф-я возвращает путь к папке для попапа
        global $link;
        global $tbl_pages;
        global $tbl_goods;
        global $showhead;
        global $user_id;

        $text='<ul class="PopTreeUl">';

        $line1 = mysql_query("SELECT * FROM $tbl_pages ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){
          
          if(check_fold($user_id,$s[0],0)){

            if($s[0]!=1){
              $text.='<li><b>'.$s["name"].'</b> ';
              $text.=get_popup_three_1($s[0],$fold_id,0);
              $text.="</li>";
            }/* else { // корзина
              $text1.='<li><a target=_parent href="./goods.php?showhead='.$showhead.'&pageid=2&subpageid=3&page_id='.$s[0].'">'.$s["name"].'</a> ';
              if($page_id==$s[0]) $text1.=get_popup_three_1($s[0],$fold_id,0);
              $text1.="</li>";
            };*/

          };

        };

        $text.=$text1;

        $text.='</ul>';

        return $text;

};


function check_str($str){
//ф-я очищает текст перед заносом в базу 
        
        $str=trim(eregi_replace("'","&#39;",$str));
        
        return $str;

};


function check_str2($str){
//ф-я очищает текст перед выводом в форму
        
        $str=trim(eregi_replace("</textarea>","&lt;/textarea>",$str));
        //$str=eregi_replace('"','\"',$str);
        
        return $str;

};


function get_more_three($page_id,$fold_id){
//ф-я рисует дерево для конкретного тома page_id
        global $link;
        global $tbl_goods;
        global $tbl_goods_;
        global $user_id;

        $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE page_id=$page_id and goods_id=$fold_id ORDER BY name",$link);
        while($s=mysql_fetch_array($line1)){

          if(check_fold($user_id,$page_id,$s[0])){
            
            $line1z = mysql_query("SELECT count(id) FROM $tbl_goods_ WHERE goods_id=$s[0]",$link);
            $ss=mysql_fetch_array($line1z);
            $fl=intval($ss[0]);
            if($fl!=0)$fl="($fl)"; else $fl="";

            if($s["page_id"]!=1)$text.='<li id=ff'.$page_id.'_'.$s[0].'><a id=ff'.$page_id.'_'.$s[0].'_ onContextMenu="showcontext('."'".'fold'."'".','.$page_id.','.$s[0].');return false;" href="./goods.php?pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'&rand='.rand().'" target=right1>'.$s["name"].'</a> '.$fl.get_more_three($page_id,$s[0]).'</li>';
              else $text.='<li id=ff'.$page_id.'_'.$s[0].'><a id=ff'.$page_id.'_'.$s[0].'_ onContextMenu="showcontext('."'".'frombin'."'".','.$page_id.','.$s[0].');return false;" href="./goods.php?pageid=2&subpageid=3&page_id='.$page_id.'&fold_id='.$s[0].'&rand='.rand().'" target=right1>'.$s["name"].'</a> '.$fl.get_more_three($page_id,$s[0]).'</li>';
          
          };

        };
        
        if($text!="")$text='<ul class="TreeUl">'.$text.'</ul>';
        
        return $text;
}

function get_three($page_id){
//ф-я рисует полное дерево для тома page_id включая список томов
        global $link;
        global $tbl_goods;
        global $tbl_pages;
        global $user_id;

        $text='<ul class="TreeUl">';

        $line1 = mysql_query("SELECT * FROM $tbl_pages ORDER BY name,id",$link);
        while($s=mysql_fetch_array($line1)){
          
          if(check_fold($user_id,$s[0],0)){

            if($s[0]!=1){
              if($page_id!=$s[0])$text.='<li id=ff'.$s[0].'_0 class=f1><a id=ff'.$s[0].'_0_ onContextMenu="showcontext('."'".'page'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&page_id='.$s[0].'&rand='.rand().'" target=_parent>'.$s["name"].'</a></li>';
                else $text.='<li id=ff'.$s[0].'_0 class=f1><a id=ff'.$s[0].'_0_ onContextMenu="showcontext('."'".'page'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&subpageid=3&page_id='.$s[0].'&rand='.rand().'" target=right1>'.$s["name"].'</a>'.get_more_three($page_id,0).'</li>';
            } else {
              if($page_id!=$s[0])$text1.='<li id=ff'.$s[0].'_0 class=f1><a id=ff'.$s[0].'_0_ onContextMenu="showcontext('."'".'bin'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&page_id='.$s[0].'&rand='.rand().'" target=_parent>'.$s["name"].'</a></li>';
                else $text1.='<li id=ff'.$s[0].'_0 class=f1><a id=ff'.$s[0].'_0_ onContextMenu="showcontext('."'".'bin'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&page_id='.$s[0].'&rand='.rand().'" target=_parent>'.$s["name"].'</a>'.get_more_three($page_id,0).'</li>';
            };
            /* else { // корзина
              if($page_id!=$s[0])$text.='<li>!<a onContextMenu="showcontext('."'".'bin'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&page_id='.$s[0].'" target=_parent>'.$s["name"].'</a></li>';
                else $text.='<li><a onContextMenu="showcontext('."'".'bin'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&subpageid=3&page_id='.$s[0].'" target=right1>'.$s["name"].'</a>'.get_more_three($page_id,0).'</li>';
              //$text.='<li><a onContextMenu="showcontext('."'".'bin'."'".','.$s[0].',0);return false;" href="./goods.php?pageid=2&page_id='.$s[0].'" target=_parent>'.$s["name"].'</a></li>';
            };*/
          
          };

        };

        $text.=$text1;

        $text.="</ul>";

        return $text;

};


switch($pageid){

  case(0):
  default: // первая страница - самая нижняя и важная

  ?><html>
<head>
<title>Система управления сайтом "TRAFFIC"</title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
<meta NAME="description" CONTENT="">
<meta NAME="keywords" CONTENT=''>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
</head>
<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF">
<iframe src="goods.php?pageid=1" width=100% height=100% scrolling=no frameborder=0></iframe>
</body>
</html><?

  break;

  case(1): // центральный фрейм 
   
  ?><html>
<head>
<title>Система управления сайтом "TRAFFIC"</title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
<meta NAME="description" CONTENT="">
<meta NAME="keywords" CONTENT=''>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">

<style><!--

.cw { font-family: Arial,Verdana,Helvetica,Tahoma; font-size: 11px; color: #FFFFFF;}
.cwg { font-family: Arial,Verdana,Helvetica,Tahoma; font-size: 11px; color: #CCCCCC;}
td { font-family: Arial,Verdana,Helvetica,Tahoma; font-size: 11px; color: #000000;}

.toolbar {border-bottom:#808080 2px solid;font-size:15px;color:#000000;background:#D4D0C8;cursor: normal;}

.progbut {border-top:#FFFFFF 2px solid;border-left:#FFFFFF 2px solid;border-bottom:#808080 2px solid;border-right:#808080 2px solid;font-size:15px;color:#000000;background:#D4D0C8; width: 100%; height: 22px; line-height: 16px; padding-left: 5px; padding-right: 5px;cursor: normal;}
.progbutactive {border-top:#808080 2px solid;border-left:#808080 2px solid;border-bottom:#FFFFFF 2px solid;border-right:#FFFFFF 2px solid;font-size:15px;color:#000000;background:#EAE8E4; width: 100%; height: 22px; line-height: 16px; padding-left: 5px; padding-right: 5px; font-weight: bold;cursor: normal;}

.topname {border-top:#00366F 0px solid;border-left:#00366F 0px solid;border-bottom:#00366F 0px solid;border-right:#00366F 0px solid;font-size:11px;color:#FFFFFF;background:#00366F; width: 100%; padding-left: 5px; padding-right: 5px; font-weight: bold;cursor: normal;}
.topnamegray {border-top:#808080 0px solid;border-left:#808080 0px solid;border-bottom:#808080 0px solid;border-right:#808080 0px solid;font-size:11px;color:#FFFFFF;background:#808080; width: 100%; padding-left: 5px; padding-right: 5px; font-weight: bold;cursor: normal;}

.tblwin {border-top:#FFFFFF 1px solid;border-left:#FFFFFF 1px solid;border-bottom:#808080 1px solid;border-right:#808080 1px solid;color:#000000;cursor: normal;}
.tblwin2 {border-top:#FFFFFF 2px solid;border-left:#FFFFFF 2px solid;border-bottom:#808080 2px solid;border-right:#808080 2px solid;color:#000000;cursor: normal;}

.adminname { font-family: Arial,Tahoma,Verdana,Helvetica; font-size: 20px; color: #FFFFFF; font-weight: 500; line-height: 30px;}
.menutext { font-family: Verdana,Arial,Tahoma,Helvetica; font-size: 11px; color: #000000; text-decoration: none;}

.shadow { FILTER: progid:DXImageTransform.Microsoft.Shadow(color='#222222', Direction=150, Strength=3) }


.Folders {padding:10px 10px 10px 10px; margin-bottom:20px;}
.FolderBig {width:80px; height:80px; vertical-align:top; text-align:center; margin:0px; float:left;}


.ContextMenu {padding:2px; width:240px; white-space:nowrap;  background:#D4D0C8; }
.ContextMenu a {width:100%; text-decoration:none; font-size:11px; color:#000000; padding:8 10 8 10;padding-left:38px;}
.ContextMenu a:hover { font-size:11px; color:#FFFFFF; background:#000080;}


--></style>

<script language="JavaScript"><!--
idZI=0;//текущий перетаскиваемый слой
ZI=5;//max z-index
cZI=5;//текущий z-index
hh=23;//высота шапки окна
clsp=4;//cellspacing - ширина бордюра таблицы
minwinw=100;//минимальная ширина окна
minwinh=80;//минимальная высота окна
toolbarh=30;//высота тулбара сверху
draggo=false;//признак того что можно перетаскивать
reswin=false;//признак того что окно ресайзится
tablecontent="";
wintopbgcolor="#00366F";//бакграунд верха всплывающего окна
wintopbgcolorgray="#808080";//бакграунд верха всплывающего окна не активный
startwidth=700;// по умолчанию ширина окна
startheight=500;// по умолчанию высота окна

startwidthdef=700;// по умолчанию ширина окна
startheightdef=500;// по умолчанию высота окна


startposleft=20;//начальные позиции открываемого окна
startpostop=20;//начальные позиции открываемого окна


// прелоад картинок для кнопок всплывающего меню
var b1=new Image();
var b2=new Image();
var b3=new Image();
b1.src="i/b1.gif";
b2.src="i/b2.gif";
b3.src="i/b3.gif";

var b1_=new Image();
var b2_=new Image();
var b3_=new Image();
b1_.src="i/b1_.gif";
b2_.src="i/b2_.gif";
b3_.src="i/b3_.gif";


//прелоад каринок для тулбара
var bt0=new Image();
bt0.src="i/start.gif";

var bt0_=new Image();
bt0_.src="i/start_.gif";


var bt1=new Image();
var bt2=new Image();
var bt3=new Image();
bt1.src="i/bt1.gif";
bt2.src="i/bt2.gif";
bt3.src="i/bt3.gif";

var bt1_=new Image();
var bt2_=new Image();
var bt3_=new Image();
bt1_.src="i/bt1_.gif";
bt2_.src="i/bt2_.gif";
bt3_.src="i/bt3_.gif";

var bt1__=new Image();
var bt2__=new Image();
var bt3__=new Image();
bt1__.src="i/bt1__.gif";
bt2__.src="i/bt2__.gif";
bt3__.src="i/bt3__.gif";


function hidepopup(){
//ф-я скрывает пап-ап окно с меню
        document.btz0.src=bt0.src;        
        document.all["startdot"].style.visibility="hidden";
        
};

function showpopup(){
//ф-я показывает пап-ап окно с меню
        ZI++;
        if(document.all["startdot"].style.visibility=="hidden"){
          document.all["startdot"].style.visibility="visible";
        } else {
          hidepopup();
        };
        event.cancelBubble=true;
        event.returnValue=false;

};


function maximumwin(win,dot){
//ф-я максимизирует окно
  
  rwin=document.getElementById("tbl"+dot);

  if(document.all["dot"+dot].style.left=="0px" 
    && document.all["dot"+dot].style.top==toolbarh+1+"px"
    && rwin.width==document.body.clientWidth
    && rwin.height==document.body.clientHeight-toolbarh-1
    ){//восстановим исходные

    rwin.width=rwin.lastwidth;
    rwin.height=rwin.lastheight;
    document.all["dot"+dot].style.left=rwin.lastleft;
    document.all["dot"+dot].style.top=rwin.lasttop;
  
  } else {//сделаем на максимум

    rwin.lastwidth=rwin.width;
    rwin.lastheight=rwin.height;
    rwin.lasttop=document.all["dot"+dot].style.top;
    rwin.lastleft=document.all["dot"+dot].style.left;

    document.all["dot"+dot].style.left="0px";
    document.all["dot"+dot].style.top=toolbarh+1+"px";
    rwin.width=document.body.clientWidth;
    rwin.height=document.body.clientHeight-toolbarh-1;

  };
  
};


function set_field_value(){
//ф-я пишет значения в инпуты тулбара  
  i=0;
  work=first.next;
  while(work!=last){
    
    document.toolbarform.elements[i].value=work.name;

    work=work.next;
    i++;
  };
};

function rewritetoolbar(){
  //перересуем тулбар
  
  tablecontent="";
  work=first.next;
  while(work!=last){
    
    tablecontent+="<td><input type=text  value='' name='f"+work.id+"' onMouseUp='changeZI_min("+work.id+")' readonly onFocus='this.blur();' title='"+work.name+"'";
    if(ZI==work.ZI) tablecontent+=" class=progbutactive "; // выделим текущий
      else tablecontent+=" class=progbut "; // выделим текущий
    tablecontent+="></td>";

    work=work.next;

  };

  bdt.innerHTML='<table cellpadding=0 cellspacing=1 border=0 width=100%><form name="toolbarform"><tr>'+tablecontent+'</tr></form></table>';

  setTimeout("set_field_value();",10);

};


function desctroy(dot){
//удалим обьект

        work=first;
        while(work!=last){
          if(work.id==dot){
            document.all["dot"+dot].style.visibility="hidden";
            work.next.prev=work.prev;
            work.prev.next=work.next;
            work=last;
          } else work=work.next;
        };

        //alert(dot);

        rewritetoolbar();
};


function create_obj(n,i){
        //ф-я создает обьект и делает его предпоследним
        work=new wnObjectinit(n,last.prev,last,i);
        last.prev.next=work;
        last.prev=work;

};

function wnObjectinit(){
        //создадим обьект
        this.name=arguments[0];//название
        this.prev=arguments[1];//ссылка на предыдущий
        this.next=arguments[2];//ссылка на слудующий
        this.id=arguments[3];//id слоя
        this.ZI=ZI;//Z-index слоя
        this.minimize=false;//минимизация окна false - нормальное, true - миномальное
};

first=new wnObjectinit("",null,null,0,0);
last=new wnObjectinit("",null,null,0,0);
first.next=last;
last.prev=first;
tek=first;

function changeZI_min(dot){
//ф-я вызываемая по клику на тулбар
//если окно показано и активно то оно минимизируется

        work=first.next;
        while(work!=last){
          if(work.id==dot){
            if(work.ZI==ZI && !work.minimize) {
              minwin(work,dot);
            } else changeZI(dot);
            work=last;
          } else work=work.next;
        };

};


function changeZI(dot){
//ф-я делает слой dot самым верхним
        
        hidepopup();

        //проверим а вдруг это итак текущий слой?
        flag=true;
        
        work=first;
        while(work!=last){
          if(work.id==dot && work.ZI==ZI){
            flag=false;
          };
          work=work.next;
        };

    if(flag){
        work=first.next;
        while(work!=last){
          document.getElementById("td"+work.id).innerHTML=get_win_top(work.id,wintopbgcolorgray,ZI);
          setTimeout("document.ttt"+work.id+".ttt.value='"+work.name+"';",1);
          work=work.next;
        };

      if(dot!=0){
        ZI++;
        document.all["dot"+dot].style.zIndex=ZI;
        maxwin(dot);
        work=first;
        while(work!=last){
          if(work.id==dot){
                work.ZI=ZI;
                teks=work;
                teks.minimize=false;
                document.getElementById("td"+teks.id).innerHTML=get_win_top(teks.id,wintopbgcolor,ZI);
                setTimeout("document.ttt"+teks.id+".ttt.value=teks.name;",1);
          };
          work=work.next;
        };
      };
        rewritetoolbar();
    };

    //alert(ZI+"/"+cZI);
    
    document.all["startdot"].style.zIndex=ZI+2;
    document.all["blankdot"].style.zIndex=ZI+3;
    
};


function showblank(){
        document.all["blankdot"].style.visibility="visible";
        document.all["blankdot"].style.width="100%";
        document.all["blankdot"].style.height="100%";
};

function resizewinstart(win,id){
//старт ресайза окна win
        
     if(!draggo){
        
        //minall_(id);
        showblank();
        
        reswin=true;
        rwin=win;//обьект который мы двигаем
        
        xkoord=-10;
        ykoord=-10;
        if(Math.abs(window.event.x-(parseInt(document.all['tbl'+id].width)+parseInt(document.all['dot'+id].style.pixelLeft)))<10){
          //ресайзим по х
          xkoord=document.all['dot'+id].style.pixelLeft-1;
        };
        
        if(Math.abs(window.event.y-(parseInt(document.all['tbl'+id].height)+parseInt(document.all['dot'+id].style.pixelTop)))<10){
          //ресайзим по y
          ykoord=document.all['dot'+id].style.pixelTop;
        };

     };
};


function minwin(win,dot){
//минимизируем окно dot
   
   work=first.next;
   while(work!=last){
     if(work.id==dot)work.minimize=true;
     work=work.next;
   };

   document.all["dot"+dot].style.minimize=true;
   document.all["dot"+dot].style.visibility="hidden";
   ZI++;
   work=last;
   rewritetoolbar();
};

function maxwin(dot){
//максимизируем окно dot
   document.all["dot"+dot].style.minimize=false;
   document.all["dot"+dot].style.visibility="visible";
};



function godrag(){
//ф-я перетаскивания окна
  if(draggo){ // перетаскиваем окно
    document.all['dot'+idZI].style.pixelLeft=window.event.x-xkoord;
    if(window.event.y-ykoord>toolbarh)document.all['dot'+idZI].style.pixelTop=window.event.y-ykoord;
      else document.all['dot'+idZI].style.pixelTop=toolbarh+1;
  };
  
  if(reswin){//ресайзим окно
    if(xkoord!=-10 && (window.event.x-xkoord>minwinw))rwin.width=window.event.x-xkoord;
    if(ykoord!=-10 && (window.event.y-ykoord>minwinh))rwin.height=window.event.y-ykoord;
    startwidth=window.event.x-xkoord;
    startheight=window.event.y-ykoord+5;
  };

  event.cancelBubble=true;
  event.returnValue=false;
};


function godrag2(id,x,y){
//ф-я перетаскивания окна под управлением вложеного фрейма

  //alert(id,x,y);

  xx=document.all['dot'+id].style.pixelLeft;
  yy=document.all['dot'+id].style.pixelTop;
  x=xx+x+clsp+2;
  y=yy+y+clsp+2+20;

  if(draggo){ // перетаскиваем окно
    document.all['dot'+idZI].style.pixelLeft=x-xkoord;
    if(y-ykoord>toolbarh)document.all['dot'+idZI].style.pixelTop=y-ykoord;
      else document.all['dot'+idZI].style.pixelTop=toolbarh+1;
  };
  
  if(reswin){//ресайзим окно
    if(xkoord!=-10 && (x-xkoord>minwinw))rwin.width=x-xkoord;
    if(ykoord!=-10 && (y-ykoord>minwinh))rwin.height=y-ykoord+5;
    startwidth=x-xkoord;
    startheight=y-ykoord+5;
  };

};


function drag_start(id){
// ф-я начала драгндропа слоя dot+id
  
  xkoord=window.event.clientX-document.all['dot'+id].style.left.substr(0,document.all['dot'+id].style.left.length-2);
  ykoord=window.event.clientY-document.all['dot'+id].style.top.substr(0,document.all['dot'+id].style.top.length-2);
  draggo=true;
  idZI=id;

};


function get_win_top(dotZ,color,dZI){
//ф-я рисуте верх для окна
                                                                                                                                  
  
  ss1='<table bgcolor='+color+' cellpadding=0 border=0 cellspacing=0 width=100%><tr><form name=ttt'+dotZ+'><td width=100% bgcolor='+color+' onMouseDown="changeZI('+dotZ+');drag_start('+dotZ+');reswin=false;showblank();" onMouseUp="draggo=false;" style="cursor: normal" class=cw nowrap';

  ss1+='><input type="text" onClick="return false;" value="" ';
  
  if(color==wintopbgcolor)ss1+='class=topname ';
    else ss1+='class=topnamegray ';
  ss1+='name=ttt readonly onFocus="this.blur();" onDblClick="maximumwin(this,'+dotZ+');"></td></form>';
  ss1+='<td><a href="#" onMouseOut="document.b1'+dotZ+dZI+'.src=b1.src;" onMouseDown="document.b1'+dotZ+dZI+'.src=b1_.src;" onMouseUp="minwin(this,'+dotZ+')" onFocus="this.blur()"><img name=b1'+dotZ+dZI+' src="i/b1.gif" alt="" border=0 width=16 height=14 vspace=2 hspace=1></a></td>';
  ss1+='<td><a href="#" onMouseOut="document.b2'+dotZ+dZI+'.src=b2.src;" onMouseDown="document.b2'+dotZ+dZI+'.src=b2_.src;" onMouseUp="maximumwin(this,'+dotZ+');/*document.b2'+dotZ+'.src=b2.src;*/" onFocus="this.blur()"><img name=b2'+dotZ+dZI+' src="i/b2.gif" alt="" border=0 width=16 height=14 vspace=2></a></td>';
  ss1+='<td><a href="#" onMouseOut="document.b3'+dotZ+dZI+'.src=b3.src;" onMouseDown="document.b3'+dotZ+dZI+'.src=b3_.src;" onMouseUp="desctroy('+dotZ+');" onFocus="this.blur()"><img name=b3'+dotZ+dZI+' src="i/b3.gif" alt="" border=0 width=16 height=14 vspace=2 hspace=1></a></td>';
  ss1+='</tr></table>';
  

  return ss1;

};

function hide_blank(){
//ф-я скрывает прозрачный слой
        document.all["blankdot"].style.visibility="hidden";
        document.all["blankdot"].style.width="1";
        document.all["blankdot"].style.height="1";
        //alert();
};

function create_dot(dn,url){
  
  i=1;
  dn_=dn;
  work=first.next;
  while(work!=last){
    if(work.name==dn_){
      work=first;
      dn_=dn+" ("+i+")";
      i++;
    }
    work=work.next;
  };

  dn=dn_;
  
  changeZI(0);
  
  ZI++;
  
  // class=shadow
  ss='<div id="dot'+cZI+'" style="POSITION: absolute; Z-INDEX: '+ZI+'; VISIBILITY: visible; TOP: '+(toolbarh+1+startpostop)+'px; LEFT: '+startposleft+'px;"><table cellpadding='+clsp+' cellspacing=0 border=0 width='+startwidthdef+' height='+startheightdef+' bgcolor="#CCCCCC" onMouseUp="/*minall_2(id);*/hide_blank();reswin=false;" onMouseDown="changeZI('+cZI+');resizewinstart(this,'+cZI+');" id="tbl'+cZI+'" class=tblwin>';
  ss+='<tr><td id=td'+cZI+'>';

  startposleft+=20;
  startpostop+=20;
  if(startposleft>190){
    startposleft=20;
    startpostop=20;
  };
  
  ss+=get_win_top(cZI,wintopbgcolor,ZI);
  
  ss+='</td></tr>';
  
  if(url.charAt(0)!=".")ss+='<tr><td width=100% height=100% valign=top><iframe src="./goods.php?'+url+'" name="'+cZI+'" width=100% height=100% border=0 framespacing=0></iframe></td></tr><!--<tr><td align=right><img src="i/p.gif" border=1 width=10 height=10 onMouseDown="resizewinstart(this,'+cZI+');"></td></tr>--></table></div><div id="bd'+(cZI+1)+'"></div>';
    else ss+='<tr><td width=100% height=100% valign=top><iframe src="'+url+'" name="'+cZI+'" width=100% height=100% border=0 framespacing=0></iframe></td></tr><!--<tr><td align=right><img src="i/p.gif" border=1 width=10 height=10 onMouseDown="resizewinstart(this,'+cZI+');"></td></tr>--></table></div><div id="bd'+(cZI+1)+'"></div>';
  
  //bd.innerHTML+=ss;
  eval("bd"+cZI+".innerHTML+=ss;");

  setTimeout("document.ttt"+cZI+".ttt.value='"+dn+"';",1);

  create_obj(dn,cZI);

  rewritetoolbar();

  cZI++;
  ZI++;
};

function linestand(){
//выстроим окна

  i=0;
  work1=first.next;
  while(work1!=last){
    
    rwin=work1;
    dot=work1.id;

    rwin.lastwidth=rwin.width;
    rwin.lastheight=rwin.height;
    rwin.lasttop=document.all["dot"+dot].style.top;
    rwin.lastleft=document.all["dot"+dot].style.left;

    document.all["dot"+dot].style.left=(i*20)+"px";
    document.all["dot"+dot].style.top=toolbarh+1+(i*20)+"px";
    rwin.width=startwidthdef;
    rwin.height=startwidthdef;

    document.getElementById("tbl"+work1.id).width=startwidthdef;
    document.getElementById("tbl"+work1.id).height=startheightdef;

    changeZI(dot);
    work1=work1.next;
    i++;
  };

};

function minall(){
//ф-я сворачивает (разворачивает) все окна

  mn=false;

  work=first.next;
  while(work!=last){
    if(!work.minimize)mn=true;
    work=work.next;
  };

  if(mn){ // минимизируем все
    work1=first.next;
    while(work1!=last){
      if(work1.id!=0)minwin(work1,work1.id);
      work1=work1.next;
    };
    
  } else { // покажем все

    work1=first.next;
    while(work1!=last){
      if(work1.id!=0)changeZI(work1.id);
      work1=work1.next;
    };

  };

};

/*
function minall_(id){
//ф-я сворачивает  все окна при ресайзе

  work1=first.next;
  while(work1!=last){
    if(work1.id!=0 && work1.id!=id)minwin(work1,work1.id);
    work1=work1.next;
  };
};
*/

/*
function minall_2(id){
//ф-я разворачивает все окна после ресайза

  alert();

  work1=first.next;
  while(work1!=last){
    if(work1.id!=0 && work1.id!=id)maxwin(work1.id);
    work1=work1.next;
  };
};
*/

//--></script>
</head>
<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#008080" onMouseMove="godrag();" onMouseUp="draggo=false;hide_blank();reswin=false;hidepopup();" onContextMenu="event.cancelBubble=true;event.returnValue=false;return false;" background="i/bg_2.gif" style="background-repeat: repeat-x;">
<table cellpadding=0 cellspacing=0 border=0 width=100% height=100%>
<tr><td valign=top><table cellpadding=0 cellspacing=0 border=0 width=100% height=32 bgcolor="#CCCCCC" class="toolbar">
  <tr>
    <td><a href="#" onMouseOut="if(document.all['startdot'].style.visibility=='hidden')document.btz0.src=bt0.src;" onMouseDown="document.btz0.src=bt0_.src;" onMouseUp="/*document.btz0.src=bt0.src;*/showpopup();" onFocus="this.blur()"><img name=btz0 src="i/start.gif" alt="" border=0 width=51 height=22 vspace=4 hspace=4></a></td>
    <td><a href="#" onMouseOut="document.btz1.src=bt1.src;" onMouseOver="document.btz1.src=bt1_.src;" onMouseDown="document.btz1.src=bt1__.src;" onMouseUp="document.btz1.src=bt1.src;minall();" onFocus="this.blur()"><img name=btz1 src="i/bt1.gif" alt="" border=0 width=23 height=22 vspace=4 hspace=1 title="Свернуть все"></a></td>
    <td><a href="#" onMouseOut="document.btz3.src=bt3.src;" onMouseOver="document.btz3.src=bt3_.src;" onMouseDown="document.btz3.src=bt3__.src;" onMouseUp="document.btz3.src=bt3.src;linestand();" onFocus="this.blur();"><img name=btz3 src="i/bt3.gif" alt="" border=0 width=23 height=22 vspace=4 hspace=1 title="Выстроить окна"></a></td>
    <td><a href="#" onMouseOut="document.btz2.src=bt2.src;" onMouseOver="document.btz2.src=bt2_.src;" onMouseDown="document.btz2.src=bt2__.src;" onMouseUp="document.btz2.src=bt2.src;create_dot('проводник','&pageid=2&page_id=2');" onFocus="this.blur()"><img name=btz2 src="i/bt2.gif" alt="" border=0 width=23 height=22 vspace=4 hspace=1 title="Проводник"></a></td>
    <td width=100%><div id=bdt></div></td>
  </tr>
</table>
<!-- иконки рабочего стола -->
<div class="Folders">
<div class="FolderBig"><a href="javascript:{create_dot('проводник','&pageid=2&page_id=2');}" class=menutext style="color: #FFFFFF"><img src="i/explore.gif" alt="корзина" border=0 width=32 height=32><br>проводник</a></div>
<? 

//посмотрим на пользовательские темы - внешние файлы и проги

include "inc/menu.php";

for($i=0;$i<count($modul_link);$i++){

  ?><div class="FolderBig"><a href="javascript:{create_dot('<? print $modul_name[$i]; ?>','<? print $modul_link[$i]; ?>');}" class=menutext style="color: #FFFFFF"><img src="i/<? print $modul_icon[$i]; ?>" alt="<? print $modul_name[$i]; ?>" border=0 width=32 height=32><br><? print $modul_name[$i]; ?></a></div><?

};

//покажем тома на десктопе
$line10 = mysql_query("SELECT * FROM $tbl_pages WHERE desctop<>0 ORDER BY name",$link);
while($s=mysql_fetch_array($line10)){

  if($s["icon"]=="")$s["icon"]="fbig.gif";
  ?><div class="FolderBig"><a href="javascript:{create_dot('проводник - <? print $s["name"]; ?>','pageid=2&subpageid=0&fold_id=0&good_id=0&page_id=<? print $s[0]; ?>&rand=<? print rand(); ?>&showhead=1');}" class=menutext style="color: #FFFFFF"><img src="icons/<? print $s["icon"]; ?>" alt="<? print $s["name"]; ?>" border=0 width=32 height=32><br><? print $s["name"]; ?></a></div><?

};

//покажем папочки на десктопе
$line10 = mysql_query("SELECT * FROM $tbl_goods WHERE desctop<>0 and page_id<>1 ORDER BY name",$link);
while($s=mysql_fetch_array($line10)){

  if($s["icon"]=="")$s["icon"]="fbig.gif";
  ?><div class="FolderBig"><a href="javascript:{create_dot('проводник - <? print $s["name"]; ?>','&pageid=2&page_id=<? print $s["page_id"]; ?>&fold_id=<? print $s["id"]; ?>&showhead=1');}" class=menutext style="color: #FFFFFF"><img src="icons/<? print $s["icon"]; ?>" alt="<? print $s["name"]; ?>" border=0 width=32 height=32><br><? print $s["name"]; ?></a></div><?

};

?>

<!-- icons here -->
</div>
<!--
<table cellpadding=0 cellspacing=15 border=0>
  <tr>
    <td align=center><a href="javascript:{create_dot('корзина','&pageid=2&fold_id=1');}" class=menutext style="color: #FFFFFF"><img src="i/recycle.gif" alt="корзина" border=0 width=32 height=32><br>корзина</a></td>
  </tr>
  <tr>
    <td align=center><a href="javascript:{create_dot('проводник','&pageid=2&fold_id=2');}" class=menutext style="color: #FFFFFF"><img src="i/explore.gif" alt="корзина" border=0 width=32 height=32><br>проводник</a></td>
  </tr>
</table>
-->
<!-- конц иконок рабочего стола -->
</td></tr>
<tr><td valign="bottom"><table cellpadding=0 cellspacing=2 border=0>
  <tr>
    <td><a href="http://www.traffic.spb.ru" target="_blank"><img src="i/traffic.gif" alt="" border=1 style="border-color: #000000;"></a></td>
    <td valign=bottom nowrap class=cwg>&nbsp;&nbsp;Система управления контентмом сайтов <? print $ver; ?>. Поддержка: <a href="mailto:sky@traffic.spb.ru" class=cwg>sky@traffic.spb.ru</a>; тел +7 (812) 740-20-67</td>
  </tr>
</table></td></tr>
</table>
<div id="bd5"></div>
<div id="startdot" style="POSITION: absolute; Z-INDEX: 50; VISIBILITY: hidden; TOP: 26px; LEFT: 4px;" class=shadow><table cellpadding=5 cellspacing=2 border=0 width=250 class=tblwin2 bgcolor=#D4D0C8>
  <tr>
    <td>
    
    <div class="ContextMenu">
    <a href="javascript:{create_dot('проводник','&pageid=2&page_id=2');}" class=menutext style="background-image: url('i/explore.gif');background-repeat:no-repeat;">Проводник</a>
    <hr>
    <a href="javascript:{create_dot('помощь','&pageid=3');}" class=menutext style="background-image: url('i/big_b1.gif');background-repeat:no-repeat;">Помощь</a><br>
    <a href="javascript:{create_dot('настройки','&pageid=4');}" class=menutext style="background-image: url('i/big_b2.gif');background-repeat:no-repeat;">Настройки</a><br>
    <a href="javascript:{create_dot('поиск','&pageid=5');}" class=menutext style="background-image: url('i/big_b3.gif');background-repeat:no-repeat;">Поиск</a><br>
    <a href="javascript:{create_dot('восстановление базы','&pageid=33');}" class=menutext style="background-image: url('i/big_b3.gif');background-repeat:no-repeat;">Восстановление базы</a><br>
    <a href="javascript:{create_dot('пользователи','&pageid=6');}" class=menutext style="background-image: url('i/big_b4.gif');background-repeat:no-repeat;">Пользователи</a>
    </div>
    <!--
    <table cellpadding=0 cellspacing=0 border=0 width=100%>
      <tr>
        <td onClick="showpopup();"><img src="i/explore.gif" width=32 border=0 alt=""></td>
        <td width=100%><a href="javascript:{create_dot('проводник');}" class=menutext>Проводник</a></td>
      </tr>
      <tr onClick="showpopup();">
        <td colspan=2><hr></td>
      </tr>
      <tr>
        <td onClick="showpopup();"><img src="i/big_b1.gif" width=32 border=0 alt=""></td>
        <td width=100%><a href="#" class=menutext>Помощь</a></td>
      </tr>
      <tr>
        <td onClick="showpopup();"><img src="i/big_b2.gif" width=32 border=0 alt=""></td>
        <td width=100%><a href="#" class=menutext>Настройки</a></td>
      </tr>
      <tr>
        <td onClick="showpopup();"><img src="i/big_b3.gif" width=32 border=0 alt=""></td>
        <td width=100%><a href="#" class=menutext>Поиск</a></td>
      </tr>
    </table>--></td>
  </tr>
  <tr>
    <td class="adminname" background="i/bg_1.jpg" onClick="showpopup();"><i><? print get_cur_admin_name(); ?></i></td>
  </td>
</table></div>

<!-- прозрачный слой, который мы покажем при ресайзе -->
<div id="blankdot" style="POSITION: absolute; Z-INDEX: 51; VISIBILITY: visible; TOP: 0px; width: 1; LEFT: 0px; height: 1;">
<!--
<table cellpadding=0 cellspacing=0 border=1 width=100% height=100%>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
-->
</div>

</body>
</html><?

  break;

  case(2)://проводник

    switch($subpageid){

      case(0):
      default: // главная с фреймами

      ?><html>
<head>
<title></title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<title>Content site manager</title>
<script language="JavaScript"><!--

function reload_frame(){
//обновим правый фрейм
        self.left1.location.reload();
        //alert("./goods.php?pageid=2&subpageid=1&fold_id=0&good_id=0&page_id=<? print $page_id; ?>&rand=<? print rand(); ?>");
        //self.left1.document.location="./goods.php?pageid=2&subpageid=1&fold_id=0&good_id=0&page_id=<? print $page_id; ?>&rand=<? print rand(); ?>";
        //alert(self.left1.document.location);
};

var p_id="";//id тома для выделения папок в дереве слева
var t_id="";//id папки для выделения папок в дереве слева
var left_load=false;//(true) признак того что левый фрейм загрузился
var back_id="";//id до этого активной папки

function set_active_fold(pp,tt){
//ф-я выделяет папки в дереве слева
/*  
  p_id=parseInt(pp);
  t_id=parseInt(tt);
  //alert(p_id+"/"+t_id);

  <?

  if($showhead!=1){
  
  ?>

  if(left_load && (p_id!=0 || t_id!=0)){

    
    //alert(p_id+"/"+t_id);
    //alert(pp+"/"+tt);
    
    if(back_id!="")eval('self.left1.document.all["'+back_id+'"].className = "";');
    if(back_id!="")eval('self.left1.document.all["'+back_id+'_"].className = "";');

    eval('back_id="ff'+p_id+'_'+t_id+'";');
    
    eval('self.left1.document.all["ff'+p_id+'_'+t_id+'"].className = "TreeLiActive";');
    eval('self.left1.document.all["ff'+p_id+'_'+t_id+'_"].className = "TreeLiActive";');
    
  } else setTimeout("set_active_fold("+pp+","+tt+");",200);

  <? }; ?>
*/
};

//--></script>
</head>
<?

//print "-$showhead-";
//exit(1);

if($showhead==0){

?>
<frameset cols="27%,*," framespacing="0" frameborder="1" border="false">
     <frame name="left1" id="left1" target="left1" src="goods.php?pageid=2&subpageid=1&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>" scrolling="auto">
     <? if($page_id!=0){ ?>
     <frame name="right1" id="right1" target="right1" src="goods.php?pageid=2&subpageid=3&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>" scrolling="yes">
     <? } else { ?>
     <frame name="right1" id="right1" target="right1" src="goods.php?pageid=2&subpageid=4&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>" scrolling="yes">
     <? }; ?>
</frameset>
<?

} else {

//покажем проводник - только папка без навигации слева
?><frameset cols="0,*," framespacing="0" frameborder="1" border="false">
     <frame name="left1" id="left1" target="left1" src="goods.php?pageid=2&subpageid=1&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>&showhead=1" scrolling="auto">
     <? if($page_id!=0){ ?>
     <frame name="right1" id="right1" target="right1" src="goods.php?pageid=2&subpageid=3&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>&showhead=1" scrolling="yes">
     <? } else { ?>
     <frame name="right1" id="right1" target="right1" src="goods.php?pageid=2&subpageid=4&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>&showhead=1" scrolling="yes">
     <? }; ?>
</frameset>
<?          

}/* else {

//покажем проводник - только папка без навигации слева
?><frameset cols="0,*," framespacing="0" frameborder="1" border="false">
     <frame name="left1" id="left1" target="left1" src="goods.php?pageid=2&subpageid=1&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>&showhead=1" scrolling="auto">
     <? if($page_id!=0){ ?>
     <frame name="right1" id="right1" target="right1" src="goods.php?pageid=2&subpageid=<? print $subpageid; ?>&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>&showhead=-1" scrolling="yes">
     <? } else { ?>
     <frame name="right1" id="right1" target="right1" src="goods.php?pageid=2&subpageid=<? print $subpageid; ?>&fold_id=<? print $fold_id; ?>&good_id=<? print $good_id; ?>&page_id=<? print $page_id; ?>&showhead=-1" scrolling="yes">
     <? }; ?>
</frameset>
<?          

};*/


?>
<!--
<body onClick="self.parent.changeZI(window.name);" onMouseMove="self.parent.godrag2(window.name,window.event.x,window.event.y);event.cancelBubble=true;event.returnValue=false;">
-->
<body onClick="self.parent.changeZI(window.name);">
<p>Для правильного отображения страницы необходим браузер с поддержкой фреймoв</p>
<? print $page_id; ?>
</body>
</noframes>
</html><?

      break;

      case(1): // лево - список папок

      ?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title></title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<META NAME="description" CONTENT="">
<META NAME="keywords" CONTENT="">
<META NAME="ask" CONTENT="">
<META HTTP-EQUIV="keywords" CONTENT="">
<style>
body,td,p,div { font-family: Arial,Helvetica; font-size: 11px; color: #000000;}
body {margin:0px; padding:0px; background-color:#FFFFFF;}
p {margin-top:10px; margin-bottom:10px;}

#MainBlock {padding:10 10 10 10;}

.TreeUl {margin:0px 0px 0px 12px; list-style-image: url('i/f.gif');}
.TreeUl li {padding:1px 0px 0px 7px; margin:0px 0px 0px 5px; vertical-align:top; color:#919191; font-size:10px; white-space:nowrap;}
.TreeUl a {color:#0000FF; text-decoration:none; font-size:12px;}
.TreeUl a:hover {color:#0000FF; text-decoration:underline;}

li.TreeLiActive {list-style-image: url('i/f_.gif');}
a.TreeLiActive ,a.TreeLiActive a:hover  {color:#FF0000; text-decoration:underline;}

.TreeNewFolder {list-style-image: url('i/f_new.gif'); font-weight:600;}

.ContextMenu {padding:2px; width:150px; white-space:nowrap; border-top:#FFFFFF 1px solid;border-left:#FFFFFF 1px solid;border-bottom:#000000 1px solid;border-right:#000000 1px solid; background:#D4D0C8;}
.ContextMenu a {width:100%; text-decoration:none; font-size:11px; color:#000000; padding:2 10 2 10;}
.ContextMenu a:hover { font-size:11px; color:#FFFFFF; background:#000080;text-decoration:none;}

</style>
<SCRIPT language="JavaScript"><!--

var page_id=0;//id тома
var fold_id=0;//id папки
var menuflag=true;//признак того, что можно показывать контекст на body

function hidecontext(){
//спрячем контекстное меню
   document.all["context_page"].style.visibility="hidden";
   document.all["context_fold"].style.visibility="hidden";
   document.all["context_bin"].style.visibility="hidden";
   document.all["context_all"].style.visibility="hidden";
   document.all["context_frombin"].style.visibility="hidden";
   menuflag=true;
};


function showcontext(name,p_id,f_id){
//покажем контекстное меню
   hidecontext();
   //alert();
   //alert(event.y);
   document.all["context_"+name].style.top=document.body.scrollTop+event.y;
   document.all["context_"+name].style.left=event.x;
   document.all["context_"+name].style.visibility="visible";
   page_id=p_id;
   fold_id=f_id;
   menuflag=false;
};

function openfold(){
//откроем том
  lnk="./goods.php?page_id="+page_id+"&pageid=2&action=reloadnew&subpageid=3&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

/*
function openfoldnew(){
//откроем том
  lnk="page_id="+page_id+"&pageid=2&showhead=1&subpageid=3&rnd=<? print rand(); ?>";
  self.parent.self.parent.create_dot('папка',lnk);
};
*/

function opendir(){
//откроем папку
  lnk="./goods.php?page_id="+page_id+"&fold_id="+fold_id+"&action=reloadnew&pageid=2&subpageid=3&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

<? if($admin>=3){ ?>
function securefold(){
//доступ на папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=20&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;

};
<? }; ?>

function properties(){
//свойства тома
  lnk="./goods.php?page_id="+page_id+"&pageid=2&subpageid=2&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function properties_(){
//свойства папки
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=5&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};


function copystruct(){
//копирование свойств папки
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=16&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};


function movedir(){
//переместим папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=10&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function movedirtrash(){
//восстановим папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=12&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function deletedir(){
//удалим папку в корзину папку
  lnk="./goods.php?page_id="+page_id+"&pageid=23&fold_id="+fold_id+"&rnd=<? print rand(); ?>";
  if(window.confirm("Удалить папку в корзину?"))self.parent.right1.location.href=lnk;
};


function cleardir(){
//очистим папку
  lnk="./goods.php?page_id="+page_id+"&pageid=27&fold_id="+fold_id+"&rnd=<? print rand(); ?>";
  if(window.confirm("Очистить папку? Удалить файлы из папки в корзину?"))self.parent.right1.location.href=lnk;
};


function emptydir(){
//очистим корзину
  lnk="./goods.php?pageid=24&rnd=<? print rand(); ?>";
  if(window.confirm("Очистить корзину? Внимание! Восстановить данные будет невозможно!"))self.parent.right1.location.href=lnk;
};


function copydir(){
//скопируем папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=11&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function adddir(){
//добавим папку
  lnk="./goods.php?page_id="+page_id+"&goods_id="+fold_id+"&pageid=2&subpageid=5&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function addfile(){
//добавим файл
  lnk="./goods.php?pageid=2&subpageid=6&r=<? print rand(); ?>&page_id="+page_id+"&fold_id="+fold_id;
  self.parent.right1.location.href=lnk;
};

function addexcel(){
//добавим файл
  lnk="./goods.php?pageid=2&subpageid=14&r=<? print rand(); ?>&page_id="+page_id+"&fold_id="+fold_id;
  self.parent.right1.location.href=lnk;
};


function wait_time_(xx,yy){

  if(menuflag){
    hidecontext();
    document.all["context_all"].style.top=yy;
    document.all["context_all"].style.left=xx;
    document.all["context_all"].style.visibility="visible";
    page_id=0;
    fold_id=0;
    menuflag=false;
  };

};

function wait_time(xx,yy){
//ф-я задержки контекстного меню
     //setTimeout("showcontext('all',0,0);",100);
     setTimeout("wait_time_("+xx+","+yy+");",100);
};

 
 //--></script>
</head>
<!--
<body onContextMenu="showcontext('all',0,0);return false;" onClick="hidecontext();self.parent.self.parent.changeZI(self.parent.window.name);" onMouseMove="self.parent.self.parent.godrag2(self.parent.window.name,window.event.x,window.event.y);event.cancelBubble=true;event.returnValue=false;">
-->
<body
	onContextMenu="wait_time(event.x,event.y); return false;"
	onClick="hidecontext(); try {self.parent.self.parent.changeZI(self.parent.window.name); } catch (e) {}"
	onLoad="self.parent.left_load = true;"
	onUnload="self.parent.left_load = false;"
>
<div id="MainBlock">
  
  <? 
  
  print get_three($page_id);
  
  /*
  <ul class="TreeUl">
    <li class="TreeNewFolder"><a href="#">Добавить новую папку</a></li>
  </ul>

  <ul class="TreeUl">
    <li><a href="#">Знакомства</a></li>
    <li><a href="#">Моя анкета</a></li>
    <li><a href="#">Настройки чата</a></li>
    <li><a href="#">Поиск</a></li>
    <li><a href="#">Правила конкурса</a></li>
    <li><a href="#">Правила чата</a></li>
    <li>
      <a href="#">Чат</a>
      <ul class="TreeUl">
        <li><a href="#">Предложение о сотрудничестве</a> (1)</li>
        <li><a href="#">Гостевая книга</a> (1)</li>
        <li><a href="#">Общие вопросы </a> (1)</li>
        <li><a href="#">Ваши отзывы, пожелания</a> (1)</li>
        <li><a href="#">Жалобы</a> (1)</li>
        <li>
          <a href="#">Подписчики на новости</a> (1)
          <ul class="TreeUl">
            <li><a href="#">Знакомства</a></li>
            <li class="TreeLiActive"><a href="#">Моя анкета</a></li>
            <li><a href="#">Настройки чата</a></li>
            <li><a href="#">Поиск</a></li>
            <li><a href="#">Правила конкурса</a></li>
            <li><a href="#">Правила чата</a></li>
          </ul>
        </li>
      </ul>
    </li>
  </ul>

  <ul class="TreeUl">
    <li><a href="#">Работа</a> (1)</li>
    <li><a href="#">Мои вакансии</a> (1)</li>
    <li><a href="#">Мои резюме</a> (1)</li>
    <li><a href="#">Поиск работы</a> (1)</li>
    <li><a href="#">Поиск сотрудников</a> (1)</li>
    <li><a href="#">Рекрутерам</a> (1)</li>
  </ul>
  */

  ?>
</div>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td><img src="i/p.gif" alt="" border="0" width="1" height="1"></td>
  </tr>
</table>

<div class="ContextMenu" id="context_all" style="POSITION: absolute; Z-INDEX: 48; VISIBILITY: hidden; TOP: 0px; LEFT: 0px;">
  <a href="javascript:properties();">Добавить том</a><br>
  <a href="javascript:location.reload();">Обновить</a><br>
</div>

<!--
<div class="ContextMenu" id="context_all">
  <a href="javascript:properties();">Добавить том</a><br>
  <a href="javascript:location.reload();">Обновить</a><br>
</div>
-->

<div class="ContextMenu" id="context_bin" style="POSITION: absolute; Z-INDEX: 49; VISIBILITY: hidden; TOP: 0px; LEFT: 0px;">
  <a href="javascript:openfold();">Открыть корзину</a><br>
  <? if($page_id==1){ ?>
  <a href="javascript:emptydir();">Очистить корзину</a><br>
  <? }; ?>
</div>

<div class="ContextMenu" id="context_page" style="POSITION: absolute; Z-INDEX: 50; VISIBILITY: hidden; TOP: 0px; LEFT: 0px;">
  <a href="javascript:openfold();">Открыть</a><br>
  <!--
  <a href="javascript:openfoldnew();">Открыть в новом окне</a><br>
  -->
  <a href="javascript:properties();">Свойства тома</a><br>
  <a href="javascript:adddir();">Добавить папку</a><br>
</div>

<div class="ContextMenu" id="context_fold" style="POSITION: absolute; Z-INDEX: 51; VISIBILITY: hidden; TOP: 0px; LEFT: 0px;">
  <a href="javascript:opendir();">Открыть</a><br>
  <a href="javascript:addfile();">Добавить файл</a><br>
  <a href="javascript:adddir();">Добавить папку</a><br>
  <a href="javascript:addexcel();">Импорт EXCEL</a><br>
  <a href="javascript:copydir();">Копировать папку</a><br>
  <a href="javascript:movedir();">Переместить папку</a><br>
  <a href="javascript:copystruct();">Копировать структуру</a><br>
  <a href="javascript:properties_();">Свойства папки</a><br>
  <a href="javascript:cleardir();">Очистить папку</a><br>
  <a href="javascript:deletedir();">Удалить папку</a><br>
  <? if($admin>=3){ ?>
  <a href="javascript:securefold();">Доступ к папке</a><br>
  <? }; ?>
</div>

<div class="ContextMenu" id="context_frombin" style="POSITION: absolute; Z-INDEX: 52; VISIBILITY: hidden; TOP: 0px; LEFT: 0px;">
  <a href="javascript:opendir();">Открыть</a><br>
  <a href="javascript:movedirtrash();">Восстановить папку</a><br>
</div>
     
</body>
</html><?

      break;

      case(2): // право - свойства тома

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      $line1 = mysql_query("SELECT * FROM $tbl_pages WHERE id=$page_id",$link);
      $s=mysql_fetch_array($line1);

      include "inc/header.php";

?><div id="MainBlock">
  <? print get_top_navi($page_id,$fold_id); ?>
  <div class="FileBlock">
<script language="JavaScript"><!--
  function edit_field(field){
        eval("document.forms.editor.text.value=document.forms.workform2."+field+".value");
        document.forms.editor.field.value=field;
        document.forms.editor.submit();
  };
  function check_name(){
        if(document.forms.workform2.name.value==""){
                alert("Необходимо задать название тома!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };
//--></script>
<form name="editor" target="_blank" method="POST" action="editor.php?rnd=<? print rand(); ?>" ENCTYPE="multipart/form-data">
<input type="hidden" name="field" value="">
<input type="hidden" name="text" value="">
</form>

    <? if($s["name"]){ ?>
    <h1>Свойства тома &laquo;<? print $s["name"]; ?>&raquo;</h1>
    <? } else { ?>
    <h1>Добавление нового тома</h1>
    <? }; ?>
    
    <form class="FileForm" ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform2" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="7">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="good_id" value="<? print $good_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">

    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormName">Название тома</td>
        <td class="FileFormInput" colspan="3"><input class="cf" type="text" style="width:100%;" name="name" value='<? print check_str2($s["name"]); ?>'></td>
      </tr>
      <tr>
        <td class="FileFormName">Описание тома</td>
        <td class="FileFormInput" colspan="3"><textarea class="cf" rows="13" style="width:100%;" name="descr"><? print check_str2($s["descr"]); ?></textarea><br><input type="button" value="редактировать в редакторе" class="button" style="width:100%;" onClick="edit_field('descr')"></td>
      </tr>
    </table>

    <div align="right"><a href="javascript:{shmore_()}" style=" font-size:10px;">выбор&nbsp;иконки</a></div>

    <div id="more_" style="display:none;">
    <h1>Выбор иконки</h1>

    <?

    if($s["icon"]=="")$s["icon"]="fbig.gif";

    ?>

    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormName">Выбор иконки<br><small><b>ярлык на рабочий стол&nbsp;</b></small><input type="checkbox" name="desctop"<? if($s["desctop"]!=0) print " CHECKED"; ?> value=1><input type="hidden" name="icon" value="<? print $s["icon"]; ?>"><div class="IconSelect"><img src="icons/<? print $s["icon"]; ?>" width=32 height=32 name="icon"></div></td>
        <td class="FileFormInput" colspan="3"><iframe src="goods.php?pageid=25&rand=<? print rand(); ?>&icon=<? print $s["icon"]; ?>" width=100% height=100 border=1 frameborder=1></iframe></td>
      </tr>
    </table>
    </div>

    <div style="padding:5 10 10 15px;"><input type="submit" value="Сохранить" class="button" style="font-weight:700; width:150px;">&nbsp;&nbsp;&nbsp;<input type="reset" value="Отменить изменения" class="button"></div>
    </form>        

</div>


</body>
</html><?

      break;

      case(3): // право - папки и файлы

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      if ($fold_id){
        $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $s10=mysql_fetch_array($line10);
      };

      include "inc/header.php";

        ?>


<SCRIPT language="JavaScript"><!--

var page_id=<? print $page_id; ?>;//id тома
var fold_id=0;//id папки
var menuflag=true;//признак того, что можно показывать контекст на body

function hidecontext(){
//спрячем контекстное меню
   //document.all["context_page"].style.visibility="hidden";
   document.all["context_fold"].style.visibility="hidden";
   //document.all["context_bin"].style.visibility="hidden";
   //document.all["context_all"].style.visibility="hidden";
   //document.all["context_frombin"].style.visibility="hidden";
   menuflag=true;
};


function showcontext(name,p_id,f_id){
//покажем контекстное меню
   hidecontext();
   //alert();
   //alert(event.y);
   document.all["context_"+name].style.top=document.body.scrollTop+event.y;
   document.all["context_"+name].style.left=event.x;
   document.all["context_"+name].style.visibility="visible";
   page_id=p_id;
   fold_id=f_id;
   menuflag=false;
};

function openfold(){
//откроем том
  lnk="./goods.php?page_id="+page_id+"&pageid=2&action=reloadnew&subpageid=3&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

/*
function openfoldnew(){
//откроем том
  lnk="page_id="+page_id+"&pageid=2&showhead=1&subpageid=3&rnd=<? print rand(); ?>";
  self.parent.self.parent.create_dot('папка',lnk);
};
*/

function opendir(){
//откроем папку
  lnk="./goods.php?page_id="+page_id+"&fold_id="+fold_id+"&action=reloadnew&pageid=2&subpageid=3&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

<? if($admin>=3){ ?>
function securefold(){
//доступ на папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=20&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;

};
<? }; ?>

function properties(){
//свойства тома
  lnk="./goods.php?page_id="+page_id+"&pageid=2&subpageid=2&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function properties_(){
//свойства папки
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=5&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};


function copystruct(){
//копирование свойств папки
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=16&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};


function movedir(){
//переместим папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=10&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function movedirtrash(){
//восстановим папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=12&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function deletedir(){
//удалим папку в корзину папку
  lnk="./goods.php?page_id="+page_id+"&pageid=23&fold_id="+fold_id+"&rnd=<? print rand(); ?>";
  if(window.confirm("Удалить папку в корзину?"))self.parent.right1.location.href=lnk;
};


function cleardir(){
//очистим папку
  lnk="./goods.php?page_id="+page_id+"&pageid=27&fold_id="+fold_id+"&rnd=<? print rand(); ?>";
  if(window.confirm("Очистить папку? Удалить файлы из папки в корзину?"))self.parent.right1.location.href=lnk;
};


function emptydir(){
//очистим корзину
  lnk="./goods.php?pageid=24&rnd=<? print rand(); ?>";
  if(window.confirm("Очистить корзину? Внимание! Восстановить данные будет невозможно!"))self.parent.right1.location.href=lnk;
};


function copydir(){
//скопируем папку
  lnk="./goods.php?page_id="+page_id+"&pageid=2&fold_id="+fold_id+"&subpageid=11&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function adddir(){
//добавим папку
  lnk="./goods.php?page_id="+page_id+"&goods_id="+fold_id+"&pageid=2&subpageid=5&rnd=<? print rand(); ?>";
  self.parent.right1.location.href=lnk;
};

function addfile(){
//добавим файл
  lnk="./goods.php?pageid=2&subpageid=6&r=<? print rand(); ?>&page_id="+page_id+"&fold_id="+fold_id;
  self.parent.right1.location.href=lnk;
};

function addexcel(){
//добавим файл
  lnk="./goods.php?pageid=2&subpageid=14&r=<? print rand(); ?>&page_id="+page_id+"&fold_id="+fold_id;
  self.parent.right1.location.href=lnk;
};


function wait_time_(xx,yy){

  if(menuflag){
    hidecontext();
    
    document.all["context_all"].style.top=yy;
    document.all["context_all"].style.left=xx;
    document.all["context_all"].style.visibility="visible";
    
    page_id=0;
    fold_id=0;
    menuflag=false;
  };

};

function wait_time(xx,yy){
//ф-я задержки контекстного меню
     //setTimeout("showcontext('all',0,0);",100);
     setTimeout("wait_time_("+xx+","+yy+");",100);
};

 
 //--></script>


<? if($page_id!=1){ ?>
<div class="ContextMenu" id="context_fold" style="POSITION: absolute; Z-INDEX: 51; VISIBILITY: hidden; TOP: 0px; LEFT: 0px;">
  <a href="javascript:opendir();">Открыть</a><br>
  <a href="javascript:addfile();">Добавить файл</a><br>
  <a href="javascript:adddir();">Добавить папку</a><br>
  <a href="javascript:addexcel();">Импорт EXCEL</a><br>
  <a href="javascript:copydir();">Копировать папку</a><br>
  <a href="javascript:movedir();">Переместить папку</a><br>
  <a href="javascript:copystruct();">Копировать структуру</a><br>
  <a href="javascript:properties_();">Свойства папки</a><br>
  <a href="javascript:cleardir();">Очистить папку</a><br>
  <a href="javascript:deletedir();">Удалить папку</a><br>
  <? if($admin>=3){ ?>
  <a href="javascript:securefold();">Доступ к папке</a><br>
  <? }; ?>
</div>
<? }; ?>



<div id="MainBlock">
  <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>
  <div class="ToolBar">
    <table cellspacing="0" width="100%">
      <tr>
        <? if($s10["rem1"]!=""){ ?>
        <td>
          <img src="i/tips.gif" width="24" height="24" hspace="7" style="margin-top:2px;" align="left">
          <div class="Tips"><? print $s10["rem1"]; ?></div>
        </td>
        <? }; ?>
        <td align="right">
          <form class="FilesSearchForm" ENCTYPE="multipart/form-data" action="goods.php?r=461057728" method="GET" name="workform">
          <input type="hidden" name="pageid" value="2">
          <input type="hidden" name="tek" value="-2">
          <input type="hidden" name="subpageid" value="3">
          <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
          <input type="hidden" name="page_id" value="<? print $page_id; ?>">
          <input type="hidden" name="showhead" value="<? print $showhead; ?>">
            <table cellspacing="1">
              <tr>
                <td><input type="text" name="search" class="cf" value="<? print $search; ?>"></td>
                <td><input class="button" type="submit" size="10" value="найти в папке"></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
  </div>
  
  <? 
  

  $txt=get_folds($page_id,$fold_id); 
  if($txt!="")print '<div class="Folders">'.$txt.'</div>';
  
  
  $pages=get_file_pages($fold_id,$tek);
  $ltrs=get_file_letters($fold_id,$letter);

  print '<div class="Files">';
  
  if($pages!="" || $ltrs!=""){
  ?>
  
    <div class="FilesNavigation">
      <table cellspacing="0" width="100%" class="FilesNavigationTable">
        <tr>
          <td valign="bottom"><?

            if($pages!=""){

            ?><div class="FilesPages">
              <div class="FilePageDescr">страницы</div>
              <? print $pages; ?>
              <div class="FilePage<? if($tek==-1) print "Active"; ?>"><a href="./goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=3&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&rand=<? print rand(); ?>&tek=-1">все</a></div>
            </div>
            <?
            
            };

            if($ltrs!=""){

            ?>
            <div class="FilesPages">
              <div class="FilePageDescr">указатель</div>
              <? print $ltrs; ?>
              <div class="FilePage<? if($letter=="") print "Active"; ?>"><a href="./goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=3&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&rand=<? print rand(); ?>">все</a></div>
            </div>
            <? 
            
            };
            
            ?>
          </td>
        </tr>
      </table>
    </div>
  <?
  };

  $l="";

  if($order!=""){
    $o=$order;
    update_order($fold_id,$order);
  } else $o=$s10["orderby"];
  if($o=="") $o="id";

  if($tek>=0)$lim=" LIMIT $tek,$mpage_ ";

  if($letter!="")$l=" and SUBSTRING(name,1,1)='$letter' ";

  $sr="";
  if($search!=""){
        $sr=" and (name LIKE '%$search%' or dat LIKE '%$search%' or seq LIKE '%$search%'";
        for($i=1;$i<=$maxpoles;$i++)$sr.=" or pole$i LIKE '%$search%' ";
        $sr.=")";
        $lim="";
  };

  $cmd="SELECT $tbl_goods_.* FROM $tbl_goods_ WHERE $tbl_goods_.page_id=$page_id and $tbl_goods_.goods_id=$fold_id $l $sr ORDER BY $o $lim";
  //print $cmd;
  $line = mysql_query($cmd,$link);
//print "<br>=$l=$sr=<br>".$cmd."<br>".mysql_error();
  $i=0;
  while($string_=mysql_fetch_array($line))
    {
     $i++;
     if(!$flag)
       {

print $textpages;

//print "=$order=";

?>
<table cellspacing="1" class="TableFiles">
<?
        
        if (!$s10["temp_id"]) $s10["temp_id"]="1";
        ?><tr><td class="TFHeader"><? if($o=="id" || $o=="id DESC")print "<b>"; ?><a href="goods.php?showhead=<? print $showhead; ?>&pageid=<? print $pageid; ?>&rnd=<? print rand(); ?>&subpageid=<? print $subpageid; ?>&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&letter=<? print $letter; ?>&sr=<? print $sr; ?>&order=<? if($o=="id") print "id DESC"; else print "id"; ?>">ID</a><?

        ?><td class="TFHeader"><? if($o=="name" || $o=="name DESC")print "<b>"; ?><a href="goods.php?showhead=<? print $showhead; ?>&pageid=<? print $pageid; ?>&subpageid=<? print $subpageid; ?>&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&rnd=<? print rand(); ?>&letter=<? print $letter; ?>&sr=<? print $sr; ?>&order=<? if($o=="name") print "name DESC"; else print "name"; ?>">Название файла</a></td><?

if($s10["dat__"]!=0) {
        
        print '<td class="TFHeader">';
        if($order=="dat" || $order=="dat DESC") print "<b>";
        print "<a href='goods.php?showhead=$showhead&pageid=4&fold_id=$fold_id&rnd=".rand()."&order=";
        if($order!="dat") print "dat";
          else print "dat DESC";
        print "'>дата</a></td>";
};

if($s10["seq__"]!=0) {
        print '<td class="TFHeader">';
        if($order=="seq" || $order=="seq DESC") print "<b>";
        print "<a href='goods.php?showhead=$showhead&pageid=4&fold_id=$fold_id&rnd=".rand()."&order=";
        if($order!="seq")print "seq";
          else print "seq DESC";
        print "'>порядок</a></td>";
};

for($i=1;$i<$maxpoles;$i++){
  if($s10["pole".$i."__"]!=0) {
    $nob="pole$i";
    if($o==$nob) $nob.=" DESC";
    if($s10["pole".$i."_"]!=4 && $s10["pole".$i."_"]!=2){
        print '<td class="TFHeader">';
        if($o=="pole$i" || $o=="pole$i DESC") print "<b>";
        print "<a href='./goods.php?showhead=$showhead&pageid=$pageid&subpageid=$subpageid&page_id=$page_id&fold_id=$fold_id&rnd=".rand()."&letter=$letter&sr=$sr&order=$nob'>";
        //if($order=="pole$i" || $order=="pole$i DESC") print "<b>";
        print $s10["pole".$i]."</a></td>";
    } else{//файл
        print '<td class="TFHeader">';
        //print "<a href='goods.php?pageid=4&fold_id=$fold_id&rnd=".rand()."&order=$nob'>";
        //if($order=="pole$i" || $order=="pole$i DESC") print "<b>";
        if($o=="pole$i" || $o=="pole$i DESC") print "<b>";
        print "<a href='./goods.php?showhead=$showhead&pageid=$pageid&subpageid=$subpageid&page_id=$page_id&fold_id=$fold_id&rnd=".rand()."&letter=$letter&sr=$sr&order=$nob'>";
        print get_fold_name($s10["pole".$i])."</a></td>";
    } /*elseif($s10["pole".$i."_"]==2) {//папка
        print '<td class="TFHeader">';
        //print "<a href='goods.php?pageid=4&fold_id=$fold_id&rnd=".rand()."&order=$nob'>";
        //if($order=="pole$i" || $order=="pole$i DESC") print "<b>";
        if($o=="pole$i" || $o=="pole$i DESC") print "<b>";
        print "<a href='./goods.php?pageid=$pageid&subpageid=$subpageid&page_id=$page_id&fold_id=$fold_id&rnd=".rand()."&letter=$letter&sr=$sr&order=$nob'>";
        print get_fold_name($s10["pole".$i])."</a></td>";
    }*/
  };
};

        


  print '<td class="TFHeader">';
  ?>действия</td></tr><?

$t=1;
if($s10["dat__"]!=0)$t++;
if($s10["seq__"]!=0)$t++;

for($i=1;$i<$maxpoles;$i++){
  if($s10["pole".$i."__"]!=0) {
    $t++;
  };
};

//print "==$t==";

   ?>
   <script language="JavaScript">

   
   bg1="<? print $bg1; ?>";
   bg2="<? print $bg2; ?>";
   bg=bg1;
   
   bg1="#FF0000";
   bg2="#00FF00";
   bg="#FF0000";

   ii=<? 
   if($tek>=0)print $tek+1; 
     else print 1;
   ?>;

   function off_row(id){
     eval('document.all["row'+id+'"].className = "TF";');
   };

   function light_row(id){
     eval('document.all["row'+id+'"].className = "TF2";');
   };


   function w(id<?

   for($i=1;$i<=$t;$i++) print ",i".$i;
   
   ?>,readonly){
        if(bg==bg1)bg=bg2; else bg=bg1;
        
        document.write("<tr class=TF id=row"+id+" ");
        //document.write(" onmouseover=\"setPointer(this, 0, 'over', '"+bg+"', '#DDDDDD', '#FFCC99');\" onmouseout=\"setPointer(this, 0, 'out', '"+bg+"', '#DDDDDD', '#FFCC99');\"");
        
        document.write(" onMouseOut=\"off_row("+id+")\" onMouseOver=\"light_row("+id+")\" ");
        
        document.write("><td width=1><b>"+id+"</td>");
        document.write("<td>"+ii+". <? if($page_id!=1){ ?><a href='goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=6&r="+Math.random()+"&good_id="+id+"&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>'><? }; ?>"+i1+"<? if($page_id!=1){ ?></a><? }; ?></td>");
//alert(id+"/"+i5);
        for(i=2;i<<? print $t+1; ?>;i++){
          
          eval("if(i"+i+".charAt(i"+i+".length-1)=='\\n' || i"+i+".charAt(i"+i+".length-1)=='\\r')ln=i"+i+".substring(0,i"+i+".length-1); else ln=i"+i+";");
          document.write("<td>"+ln+"</td>");
          //alert(ln);
        };
        <? if($page_id!=1){ ?>
        document.write("<td nowrap width=69><a href='goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=7&page_id=<? print $page_id; ?>&rand="+Math.random()+"&fold_id=<? print $fold_id; ?>&good_id="+id+"'><img src='i/copy_file.gif' alt='копировать' width=15 height=15 hspace=4></a>");
        document.write("<a href='goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=8&page_id=<? print $page_id; ?>&rand="+Math.random()+"&fold_id=<? print $fold_id; ?>&good_id="+id+"'><img src='i/move.gif' alt='переместить' width=15 height=15 hspace=4></a>");
        if(readonly==0)document.write("<a href='javascript:{d_g("+id+");};'><img src='i/del.gif' alt='удалить' width=15 height=15 hspace=4></a>");
          else document.write("<a href='javascript:{alert("+'"файл не может быть удален"'+");}'><img src='i/del.gif' alt='удалить нельзя' width=15 height=15 hspace=4></a>");
        document.write("</td></tr>");
        <? } else { ?>
        document.write("<td nowrap width=46>");
        document.write("<a href='goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=9&rand="+Math.random()+"&fold_id=<? print $string_["goods_id_"]; ?>&good_id="+id+"'><img src='i/move.gif' alt='восстановить' width=15 height=15 hspace=4></a>");
        if(readonly==0)document.write("<a href='javascript:{d_g("+id+");};'><img src='i/del.gif' alt='удалить' width=15 height=15 hspace=4></a>");
          else document.write("<a href='javascript:{alert("+'"файл не может быть удален"'+");}'><img src='i/del.gif' alt='удалить нельзя' width=15 height=15 hspace=4></a>");
        document.write("</td></tr>");
        <? }; ?>
        ii++;
   };

<?
                               };
                             $flag=true;
                             if($bg==$bg1)$bg=$bg2; else $bg=$bg1;


for($i=1;$i<$maxpoles;$i++){
        $string_["pole".$i]=eregi_replace("
",'',$string_["pole".$i]);
        $string_["pole".$i]=eregi_replace("\n",'',$string_["pole".$i]);
        $string_["pole".$i]=eregi_replace("\r",'',$string_["pole".$i]);
};

$string_["name"]=eregi_replace("
",'',$string_["name"]);

$string_["name"]=eregi_replace("\n",'',$string_["name"]);
$string_["name"]=eregi_replace("\r",'',$string_["name"]);


?>w('<? print $string_[0]; ?>','<? print substr(eregi_replace("'",'"',strip_tags($string_["name"])),0,65); ?>'<?

/*
if($s10["dat__"]!=0) {
        
        if($string_["dat"]!=0)print ",'".dat($string_["dat"],"/")."'";
          else print ",'----'";
};
*/
if($s10["seq__"]!=0) print ",'".$string_["seq"]."'";

#require_once('/data/sites/popcornnews.ru/htdocs/data/libs/ui/im/RoomFactory.php');

for($i=1;$i<=$maxpoles;$i++){
	
	#mail('donflash@t-agency.ru','asd',print_r($s10,1));

//print "\n===".$s10["pole".$i."_"]."=\n";

  if($s10["pole".$i."__"]!=0) {

    if($s10["pole".$i."_"]==4){ // селект файл
    
        $n=get_file_name($string_["pole".$i]);

        $n=check_str($n);
        $n=str_replace("\n","",$n);
        $n=str_replace("\r","",$n);
        $n=strip_tags(nl2br($n));
        $n=str_replace("
","",$n);

        //print "===$n===";


        print ",'".substr(htmlspecialchars(eregi_replace("'",'"',$n)),0,65)."&nbsp;'";
    
    } elseif($s10["pole".$i."_"]==2){ // селект папка
    
        $n=get_fold_name($string_["pole".$i]);
        
        $n=check_str($n);
        $n=str_replace("\n","",$n);
        $n=str_replace("\r","",$n);
        $n=strip_tags(nl2br($n));
        $n=str_replace("
","",$n);

        //print "---$n---";


        print ",'".substr(htmlspecialchars(eregi_replace("'",'"',$n)),0,65)."&nbsp;'";
      
    }elseif($s10["pole".$i."_"]==99){
    	$ak_result = mysql_query(sprintf('SELECT count(id) FROM pn_comments_news WHERE news_id = %u', $string_['id']));
		list($ak_count) = mysql_fetch_row($ak_result);
    	print ",'".$ak_count."'";
    	
    }elseif($s10["pole".$i."_"]==6){//дата

        if($string_["pole".$i]!=0)print ",'".dat($string_["pole".$i],"/")."'";
          else print ",''";

    } elseif($s10["pole".$i."_"]==7){ //время
        
        if($string_["pole".$i]!=0)print ",'".tim($string_["pole".$i],":")."'";
          else print ",''";

    } elseif($s10["pole".$i."_"]==3 || $s10["pole".$i."_"]==5){ //галочка
        
        if($string_["pole".$i]!="")print ",'<img src=\"i/ok.gif\" class=TFimg>'";
          else print ",''";

    } elseif($s10["pole".$i."_"]!=17){ // файлы
        
        print ",'".substr(htmlspecialchars(eregi_replace("'",'"',$string_["pole".$i])),0,65)."&nbsp;'";
   
    } elseif($s10["pole".$i."_"]==17) { // картинка
    	if ($string_['goods_id'] == 2 && $string_['page_id'] == 2)
    	{
    		$ak_result = mysql_query(sprintf('SELECT filepath FROM popcornnews_news_images WHERE movie_id = %u ORDER BY seq ASC LIMIT 1', $string_['id']));
    		list($ak_poster) = mysql_fetch_row($ak_result);
    		
    		if(!empty($df_poster))print ",'".'<a href="'.$ak_poster.'" target=_blank><img src="/k/movie_posters/60'.$ak_poster.'" alt="увеличить" border=0></a>'."'";
    		else print ",'".'&nbsp;'."'";
    	}
    	else
    	{
        if($string_["pole".$i]!="")print ",'".'<a href="../showpix.php?id='.$string_["pole".$i].'" target=_blank><img src="../showimg.php?id='.$string_["pole".$i].'&wd=60&hd=60" alt="увеличить" border=0></a>'."'";
          else print ",'".'&nbsp;'."'";
        }
    } else print ",'".'&nbsp;'."'";
    /*?>w('<? print $string_[0]; ?>','<? print substr($string_["name"],0,65); ?>','','','');<?*/

 //   $ttt.="\n//===".$s10["pole".$i."_"]."\n";
  
  }// else print ",''";
};

print ','.$string_["readonly"].');';

print $ttt;

//print "alert('"$i."/"."')";


  print "\n";
                            };
                          print "\n</script>";
?>
                          </table>
<?


//print mysql_error();
                          
                          if(!$flag){
                           //if($s=="" && $l=="") print "<br><b>В папке нет ни одного файла</b><br><br>";
                           if($i==0 && $sr=="") print "";//print "<br><b>В папке нет ни одного файла</b><br><br>";
                            else print "<br><b>По Вашему запросу ничего не найдено!</b><br><br>";
                          };

print $textpages;

print "</td></tr></table>";

if($pages!="" || $ltrs!=""){
  ?>
    <div class="FilesNavigation">
      <table cellspacing="0" width="100%" class="FilesNavigationTable">
        <tr>
          <td valign="bottom"><?

            if($pages!=""){

            ?><div class="FilesPages">
              <div class="FilePageDescr">страницы</div>
              <? print $pages; ?>
              <div class="FilePage<? if($tek==-1) print "Active"; ?>"><a href="./goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=3&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&rand=<? print rand(); ?>&tek=-1">все</a></div>
            </div>
            <?
            
            };

            if($ltrs!=""){

            ?>
            <div class="FilesPages">
              <div class="FilePageDescr">указатель</div>
              <? print $ltrs; ?>
              <div class="FilePage<? if($letter=="") print "Active"; ?>"><a href="./goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=3&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&rand=<? print rand(); ?>">все</a></div>
            </div>
            <? 
            
            };
            
            ?>
          </td>
        </tr>
      </table>
    </div>
  <?
  };

 ?>

  </div>
  
    <?
    
    if($fold_id!=0 && $page_id!=1)print get_newfile_form($page_id,$fold_id,$good_id);
    
    
    ?>
    
</div>
<SCRIPT language="JavaScript"><!--
<?

if($s10["rem2"]!="") print "alert('".$s10["rem1"]."')";

?>
 //--></script>

</body>
</html>
        <?
      
      break;

      case(4): // право - страница приветствия (с дедушкой "....:) ")

        print "hello";
      
      break;

      case(5): // свойства папки / добавим новую папку хтмл

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      $line1 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
      $s=mysql_fetch_array($line1);

      include "inc/header.php";

      ?>
<script language="JavaScript"><!--
  function edit_field(field){
        eval("document.forms.editor.text.value=document.forms.workform2."+field+".value");
        document.forms.editor.field.value=field;
        document.forms.editor.submit();
  };
  function check_name(){
        if(document.forms.workform2.name.value==""){
                alert("Необходимо задать название папки!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };
  function check_name3(){
        if(document.forms.workform3.name.value==""){
                alert("Необходимо задать название прикрепляемого файла!");
                document.forms.workform3.name.focus();
                return false;
        };
        return true;
  };
  function get_sel(i){
        eval('v=document.workform2.pole'+i+'_.selectedIndex');
        eval('f=document.workform2.pole'+i+'__h.value');
        //if(v==3 || v==4){ // выбран селект
        if(v==3)window.open('goods.php?pageid=13&i='+i+'&fold_id='+f,'sel','width=300,height=100,status=no,toolbar=no,menubar=no,scrollbars=no,resizable=no');
        if(v==4)window.open('goods.php?pageid=14&i='+i+'&fold_id='+f,'sel','width=300,height=100,status=no,toolbar=no,menubar=no,scrollbars=no,resizable=no');
        //};
  };
//--></script>

<div id="MainBlock">
  
  <? print get_top_navi($page_id,$fold_id); ?>
  
<form name="editor" target="_blank" method="POST" action="editor.php?rnd=<? print rand(); ?>" ENCTYPE="multipart/form-data">
<input type="hidden" name="field" value="">
<input type="hidden" name="text" value="">
</form>
  
  <div class="FileBlock">
    
    <div style="float: right">
      <div align="right"><a href="javascript:{shmore_2()}" style=" font-size:10px;">настройки&nbsp;папки</a></div>
      <div align="right"><a href="javascript:{shmore_()}" style=" font-size:10px;">выбор&nbsp;иконки</a></div>
      <div align="right"><a href="javascript:{shmore()}" style=" font-size:10px;">настройки&nbsp;полей</a></div>
    </div>
        
    <? if($s["name"]!=""){ ?>
    <h1>Свойства папки &laquo;<? print $s["name"]; ?>&raquo;</h1>
    <? } else { ?>
    <h1>Добавление новой папки</h1>
    <? }; ?>
    <form class="FileForm" ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform2" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="8">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="good_id" value="<? print $good_id; ?>">
    <input type="hidden" name="goods_id" value="<? print $goods_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">

    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormName">Название</td>
        <td class="FileFormInput" colspan="3"><input class="cf" type="text" style="width:100%;" name="name" value='<? print $s["name"]; ?>'></td>
      </tr>
    </table>
    

    <div id="more_2" style="display:none;">
    
    <h1>Настройки папки</h1>
    
    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormName">Порядковое число для сортировки</td>
        <td class="FileFormInput" colspan="3"><input class="cf" type="text" style="width:100%;" name="seq" value='<? print $s["seq"]; ?>'></td>
      </tr>
      <tr>
        <td class="FileFormName">Выводить на одну страницу файлов</td>
        <td class="FileFormInput" colspan="3"><input class="cf" type="text" style="width:100%;" name="mpage" value='<? print $s["mpage"]; ?>'></td>
      </tr>
      
      <? for($i=1;$i<=$maxdescrs;$i++){ ?>
      <tr>
        <td class="FileFormName"><input type="text" class="cf3" name="opt<? print $i; ?>" value='<? print $s["opt".$i]; ?>'></td>
        <td class="FileFormInput" colspan="3"><textarea class="cf" rows="13" style="width:100%;" name="descr<? print $i; ?>"><? print check_str2($s["descr".$i]); ?></textarea><br><input type="button" value="редактировать в редакторе" class="button" style="width:100%;" onClick="edit_field('descr<? print $i; ?>')"></td>
      </tr>
      <? }; ?>
    </table>
    <? if($maxmores>0){ ?>
    <table cellspacing="1" width="100%">
      <? for($i=1;$i<=$maxmores;$i++){ ?>
      <tr>
        <td class="FileFormInput2"><input type="checkbox" value="Yes" name="more<? print $i; ?>"<? if($s["more".$i]!="") print " CHECKED"; ?>></td><td  style="width:100%;" class="FileFormName"><input type="text" class="cf4" name="more<? print $i; ?>_" value='<? if($s["more".$i."_"]!="") print check_str($s["more".$i."_"]); else print "Дополнительное поле ".$i; ?>' style="width:100%;"></td>
      </tr>
      <? }; ?>
    </table>
    <? }; ?>

    </div>

    <div id="more_" style="display:none;">
    <h1>Выбор иконки</h1>

    <?

    if($s["icon"]=="")$s["icon"]="fbig.gif";

    ?>

    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormName">Выбор иконки<br><small><b>ярлык на рабочий стол&nbsp;</b></small><input type="checkbox" name="desctop"<? if($s["desctop"]!=0) print " CHECKED"; ?> value=1><input type="hidden" name="icon" value="<? print $s["icon"]; ?>"><div class="IconSelect"><img src="icons/<? print $s["icon"]; ?>" width=32 height=32 name="icon"></div></td>
        <td class="FileFormInput" colspan="3"><iframe src="goods.php?pageid=25&rand=<? print rand(); ?>&icon=<? print $s["icon"]; ?>" width=100% height=100 border=1 frameborder=1></iframe></td>
      </tr>
    </table>
    </div>

    <div id="more" style="display:none;">
    <h1>Настройки полей</h1>
    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormInput" style="width:50%;"><input type="checkbox" value="1" name="readonly"<? if($s["readonly"]!=0) print " CHECKED"; ?>>Папку нельзя удалить</td>
        <td class="FileFormInput" style="width:50%;"><!--<input type="checkbox" value="1" name="dat__"<? if($s["dat__"]!=0) print " CHECKED"; ?>>Показывать дату при выводе файлов-->&nbsp;</td>
      </tr>
      <tr>
        <td class="FileFormInput" style="width:50%;"><input type="checkbox" value="1" name="cansr"<? if($s["cansr"]!=0) print " CHECKED"; ?>>Поиск по папке разрешен (если требуется для данной папки)</td>
        <td class="FileFormInput" style="width:50%;"><input type="checkbox" value="1" name="seq__"<? if($s["seq__"]!=0) print " CHECKED"; ?>>Показывать последовательность при выводе файлов</td>
      </tr>
    </table>
    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormName">Подсказка для папки<br><small><b>выводить как alert&nbsp;</b></small><input type="checkbox" name="rem2"<? if($s["rem2"]!="") print " CHECKED"; ?>></td>
        <td class="FileFormInput" colspan="3"><textarea class="cf" rows="3" style="width:100%;" name="rem1"><? print nl2br(check_str2($s["rem1"])); ?></textarea></td>
      </tr>
    <? for($i=1;$i<=$maxpoles;$i++){ ?>  
      <tr>
        <td class="FileFormName">Поле <? print $i; ?></td>
        <td class="FileFormInput"><input class="cf" type="text" style="width:100%;" name="pole<? print $i; ?>" value='<? 
        if($s["pole".$i."_"]!=2 && $s["pole".$i."_"]!=4){
                print $s["pole".$i]; 
        } else {
          print 'Файл из папки "'.get_fold_name($s["pole".$i]).'"';
        }
        ?>'></td>
        <td class="FileFormInput"><select class="cf2" name="pole<? print $i; ?>_" onchange="get_sel(<? print $i; ?>)"><option value=0<? if($s["pole".$i."_"]==0) print " SELECTED"; ?>>строка</option><option value=1<? if($s["pole".$i."_"]==1) print " SELECTED"; ?>>текст</option><option value=3<? if($s["pole".$i."_"]==3) print " SELECTED"; ?>>галочка</option><option value=4<? if($s["pole".$i."_"]==4) print " SELECTED"; ?>>выбор файла</option><option value=2<? if($s["pole".$i."_"]==2) print " SELECTED"; ?>>выбор папки</option><option value=5<? if($s["pole".$i."_"]==5) print " SELECTED"; ?>>радио галочка</option><option value=6<? if($s["pole".$i."_"]==6) print " SELECTED"; ?>>дата</option><option value=7<? if($s["pole".$i."_"]==7) print " SELECTED"; ?>>время</option><option value=25<? if($s["pole".$i."_"]==25) print " SELECTED"; ?>>файл/данные</option><option value=26<? if($s["pole".$i."_"]==26) print " SELECTED"; ?>>файл word</option><option value=16<? if($s["pole".$i."_"]==16) print " SELECTED"; ?>>файл excel</option><option value=17<? if($s["pole".$i."_"]==17) print " SELECTED"; ?>>gif/jpg картинка</option><option value=18<? if($s["pole".$i."_"]==18) print " SELECTED"; ?>>swf файл</option><option value=19<? if($s["pole".$i."_"]==19) print " SELECTED"; ?>>PDF файл</option><option value=99<? if($s["pole".$i."_"]==99) print " SELECTED"; ?>>Не обновляемое (для счетчиков)</option></select></td>
        <td class="FileFormInput"><input type="checkbox" name="pole<? print $i; ?>__" <? if($s["pole".$i."__"]!=0) print "CHECKED"; ?> value="1"></td>
        <input type="hidden" name="pole<? print $i; ?>__h" value='<? if($s["pole".$i."_"]==4 || $s["pole".$i."_"]==2) print $s["pole".$i]; ?>'>
      </tr>
    <? }; ?>
    </table>
    </div>

    <div style="padding:5 10 10 15px;"><input type="submit" value="Сохранить" class="button" style="font-weight:700; width:150px;">&nbsp;&nbsp;&nbsp;<input type="reset" value="Отменить изменения" class="button"></div>
    </form>        

    <?

    if($fold_id!=0){


      $line1 = mysql_query("SELECT count(id) FROM $tbl_pix WHERE goods_id=$fold_id",$link);
      $ss=mysql_fetch_array($line1);

      if($ss[0]>0){

    ?>
    
      <h1>Вложения к папке</h1>
      
      <table cellspacing="1" width="100%" class="TableFiles">
      <?
      
      $line1 = mysql_query("SELECT * FROM $tbl_pix WHERE goods_id=$fold_id",$link);
      while($ss=mysql_fetch_array($line1)){

        if($ss["type"]!=7 && $ss["type"]!=0){ // это НЕ картинка
          ?><tr>
            <td class="TF"><a href="<?

            if($pictures=="base") print '../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type='.$ss["type"];
                else print '../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type='.$ss["type"];
              
              if($pictures=="file"){
                 print '&filename='.$ss["fizname"];
              };

            ?>"><? print $ss["name"]; ?></a> (<? print $ss["fizname"]; ?>)</td>
            <td class="TF"><? print get_pix_types_name($ss["type"]); ?></td>
            <td class="TFaction"><a href="<?
            
            if($pictures=="base") print '../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type='.$ss["type"];
                else print '../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type='.$ss["type"];
              
              if($pictures=="file"){
                 print '&filename='.$ss["fizname"];
              };

            ?>"><img src="i/open2.gif" alt="открыть" width="16" height="15" hspace="4"></a>
            <a href="./goods.php?page_id=<? print $page_id; ?>&pageid=2&fold_id=<? print $fold_id; ?>&subpageid=18&rnd=<? print rand(); ?>&file_id=<? print $ss[0]; ?>&showhead=<? print $showhead; ?>"><img src="i/open2.gif" alt="редактировать" width="16" height="15" hspace="4"></a><?

            print '<a href="javascript:{if(window.confirm('."'Удалить?      Внимание!    Восстановление невозможно!'".'))location.href='."'".'./goods.php?showhead='.$showhead.'&pageid=29&r='.rand().'&file_id='.$ss[0].'&page_id='.$page_id.'&fold_id='.$fold_id."'".'}">';

            ?><img src="i/del.gif" alt="удалить" width="15" height="15" hspace="4"></a></td>
          </tr><?
        
        } else { // картинка

          $text='<tr><td colspan=3><table cellspacing="0" width="100%"><tr>

        <td class="FileFormName">'.$ss["name"].'</td>
        <td class="FileFormInput">';

          //if($s["pole".$i]!=""){
            $text.='<img src="../showimg.php?id=';
            
            if($pictures=="base") $text.=$ss[0];
              else $text.=$ss["diskname"];

            $text.='&wd=200&hd=70" height="70" border="0" alt="" vspace="4" hspace="10" align="left">
            <ul style="margin:0px 0px 0px 120px;">
              <li style="list-style-image: url('."'".'i/open.gif'."'".'); vertical-align:top; padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base")$text.='../showpix.php?id='.$ss[0].'&rand='.rand().'&type=7';
                else $text.='../upload/'.$ss["diskname"].'?rand='.rand();

              $text.='" target=_blank>увеличить</a></li>
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base") $text.='../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type=7';
                else $text.='../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type=7';
                //$text.='../upload/'.$ss["diskname"].'?rand='.rand().';

              //if($pictures=="base") $text.=$ss[0];
              //  else $text.=$ss["diskname"];
              
              if($pictures=="file"){
                $text.='&filename='.$ss["fizname"];
              };

              $text.='">сохранить</a></li>
              
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="./goods.php?page_id='.$page_id.'&pageid=2&fold_id='.$fold_id.'&subpageid=18&rnd='.rand().'&file_id='.$ss[0].'&showhead='.$showhead.'">редактировать</a></li>

              <li style="list-style-image: url('."'".'i/i2.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="javascript:{if(window.confirm('."'Удалить?      Внимание!    Восстановление невозможно!'".'))location.href='."'".'./goods.php?showhead='.$showhead.'&pageid=29&r='.rand().'&file_id='.$ss[0].'&page_id='.$page_id.'&fold_id='.$fold_id."'".'}">удалить</a></li>
            </ul>';
          //};

        $text.='</td>
        </tr></table></td>
      </tr>';

        print $text;

        };
     
      };
     
     ?>

      </table>
      
      <?  
      
      };

      ?>

      <h1>Прикрепить документ или картинку к папке</h1>
      <form class="FileForm" ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform3" onSubmit="return check_name3();">
    <input type="hidden" name="pageid" value="28">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">

      <table cellspacing="1" width="100%">
        <tr>
          <td class="FileFormName">Имя</td>
          <td class="FileFormInput"><input class="cf" type="text" style="width:100%;" name="name"></td>
        </tr>

        <tr>
          <td class="FileFormName">Тип</td>
          <td class="FileFormInput"><select class="cf2" name="type" style="width:100%;"><? print get_select_types(7); ?></select></td>
        </tr>
        
        <tr>
          <td class="FileFormName">Путь к документу или картинке</td>
          <td class="FileFormInput" colspan="2"><input class="cf" type="file" style="width:100%;" name="userfile"></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td class="FileFormSubnit"><input type="submit" value="Загрузить" class="button" style="font-weight:700"></td>
      </tr>
      </table>
      </form>
      <?

      };

      ?>
  </div>


</div>


</body>
</html><?


      break;

      case(6): // отредактируем файл

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      if ($fold_id){
        $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $s10=mysql_fetch_array($line10);
      };

      include "inc/header.php";

?>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>
  <div class="ToolBar">
    <table cellspacing="0" width="100%">
      <tr>
        <? if($s10["rem1"]!=""){ ?>
        <td>
          <img src="i/tips.gif" width="24" height="24" hspace="7" style="margin-top:2px;" align="left">
          <div class="Tips"><? print $s10["rem1"]; ?></div>
        </td>
        <? }; ?>
        <td align="right">
          <form class="FilesSearchForm" ENCTYPE="multipart/form-data" action="goods.php?r=461057728" method="GET" name="workform">
          <input type="hidden" name="pageid" value="2">
          <input type="hidden" name="tek" value="-2">
          <input type="hidden" name="subpageid" value="3">
          <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
          <input type="hidden" name="page_id" value="<? print $page_id; ?>">
          <input type="hidden" name="showhead" value="<? print $showhead; ?>">
            <table cellspacing="1">
              <tr>
                <td><input type="text" name="search" class="cf" value="<? print $search; ?>"></td>
                <td><input class="button" type="submit" size="10" value="найти в папке"></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
  </div>
  



    <?
    
    print get_newfile_form($page_id,$fold_id,$good_id);
    
    
    ?>
    
</div>

</body>
</html>
<?


      break;


      case(7): // скопируем файл хтмл
      
      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      if ($fold_id){
        $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $s10=mysql_fetch_array($line10);
      };

      $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE id=$good_id",$link);
      $s=mysql_fetch_array($line10);


      include "inc/header.php";

?>
 <SCRIPT language="JavaScript"><!--
  function change_pole(name){
    try {
      eval("s=document.forms['workform2']."+name+".value.length;");
      eval("document.forms['workform2']."+name+"_.value=s;");
    } catch (e) {}
  }

  function check_name(){
        eval('f=document.workform2.f_id.value;');
        if(document.forms.workform2.name.value=="" || f==""){
                alert("Необходимо задать название файла и выбрать папку назначения!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

  function get_sel(){
        eval('f=document.workform2.f_id.value;');
        if(f!=""){
          //location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
  };

 //--></script>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
    
    ?>

    <div class="FileBlock">

<h1>Копирование файла &laquo;<? print $s["name"]; ?>&raquo;</h1><form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand=<? print rand(); ?>" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="18">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="good_id" value="<? print $good_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">
    <table cellspacing="1" width="100%" class="FileFormHeadTable">
      <tr>
        <td class="FileFormHead" width="100%">Новое название<br><input class="cf" type="text" style="width:100%;" name="name" value='<? print $s["name"]; ?>' onChange="change_pole('name')" onPaste="change_pole('name')" onKeyUp="change_pole('name')" tabindex=1></td>
        <td class="FileFormHead" width="1" valign="bottom"><input class="cfreadonly" type="text" style="width:40px;" value="60000" readonly title="Количество символов" name="name_"></td>
      </tr>

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Путь копирования<br>
    <select class="cf" name="f_id" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($fold_id);

        ?>
    </select>
        </td>
      </tr>

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Количество<br><input class="cf" type="text" style="width:100%;" name="copys" value='1' tabindex=1></td>
      </tr>


      <tr>
        <td class="FileFormSubnit" colspan=2>
          <table cellspacing="3">
            <tr>
              <td><input tabindex=60 type="submit" value="Копировать файл" class="button" style="font-weight:700"></td>
              <td><input tabindex=61 type="reset" onClick=javascript:{setTimeout("change_pole('name');",10);}; value="Отменить изменения" class="button"></td>
            </tr>
          </table>
        </td>
      </tr>

      </table><script>
change_pole('name');
      </script></form>


    </div>
    
</div>

</body>
</html>
<?


      break;




      case(8): // переместим файл хтмл
      
      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      if ($fold_id){
        $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $s10=mysql_fetch_array($line10);
      };

      $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE id=$good_id",$link);
      $s=mysql_fetch_array($line10);

      include "inc/header.php";

?>
<SCRIPT language="JavaScript"><!--
  function change_pole(name){
    try {
      eval("s=document.forms['workform2']."+name+".value.length;");
      eval("document.forms['workform2']."+name+"_.value=s;");
    } catch (e) {}
  }

  function check_name(){
        eval('f=document.workform2.f_id.value;');
        if(f==""){
                alert("Необходимо задать название файла и выбрать папку назначения!");
                return false;
        };
        return true;
  };

  function get_sel(){
        eval('f=document.workform2.f_id.value;');
        if(f!=""){
          //location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
  };

 //--></script>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
    
    ?>

    <div class="FileBlock">

<h1>Перенос файла &laquo;<? print $s["name"]; ?>&raquo;</h1><form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand=<? print rand(); ?>" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="19">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="good_id" value="<? print $good_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">
    <table cellspacing="1" width="100%" class="FileFormHeadTable">

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Папка, в которую необходимо перенести файл<br>
    <select class="cf" name="f_id" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($fold_id);

        ?>
    </select>
        </td>
      </tr>


      <tr>
        <td class="FileFormSubnit" colspan=2>
          <table cellspacing="3">
            <tr>
              <td><input tabindex=60 type="submit" value="Переместить файл" class="button" style="font-weight:700"></td>
              <td><input tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
            </tr>
          </table>
        </td>
      </tr>

      </table><script>
      </script></form>


    </div>
    
</div>

</body>
</html>
<?


      break;



      case(9): // восстановим файл из корзины хтмл
      
      //if(!check_fold($user_id,$page_id,$fold_id)) norules();

      if ($fold_id){
        $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
        $s10=mysql_fetch_array($line10);
      };

      $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE id=$good_id",$link);
      $s=mysql_fetch_array($line10);

      include "inc/header.php";

?>
<SCRIPT language="JavaScript"><!--
  function change_pole(name){
    try {
      eval("s=document.forms['workform2']."+name+".value.length;");
      eval("document.forms['workform2']."+name+"_.value=s;");
    } catch (e) {}
  }

  function check_name(){
        eval('f=document.workform2.f_id.value;');
        if(f==""){
                alert("Необходимо задать название файла и выбрать папку назначения!");
                return false;
        };
        return true;
  };

  function get_sel(){
        eval('f=document.workform2.f_id.value;');
        if(f!=""){
          //location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
  };

 //--></script>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
    
    ?>

    <div class="FileBlock">

<h1>Восстановление файла &laquo;<? print $s["name"]; ?>&raquo;</h1><form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand=<? print rand(); ?>" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="20">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="good_id" value="<? print $good_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">
    <table cellspacing="1" width="100%" class="FileFormHeadTable">

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Папка, в которую необходимо восстановить файл<br>
    <select class="cf" name="f_id" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($fold_id);

        ?>
    </select>
        </td>
      </tr>


      <tr>
        <td class="FileFormSubnit" colspan=2>
          <table cellspacing="3">
            <tr>
              <td><input tabindex=60 type="submit" value="Восстановить файл" class="button" style="font-weight:700"></td>
              <td><input tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
            </tr>
          </table>
        </td>
      </tr>

      </table><script>
      </script></form>


    </div>
    
</div>

</body>
</html>
<?


      break;


      case(10): // перенос папки

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
      $s=mysql_fetch_array($line10);

      include "inc/header.php";


?>
<SCRIPT language="JavaScript"><!--
  function change_pole(name){
    try {
      eval("s=document.forms['workform2']."+name+".value.length;");
      eval("document.forms['workform2']."+name+"_.value=s;");
    } catch (e) {}
  }

  function check_name(){
        eval('f=document.workform2.f_id.value;');
        if(document.forms.workform2.name.value=="" || f==""){
                alert("Необходимо задать название файла и выбрать папку назначения!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

  function get_sel(){
        eval('f=document.workform2.f_id.value;');
        if(f!=""){
          //location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
  };

 //--></script>


<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
    
    ?>

    <div class="FileBlock">

<h1>Перенос папки &laquo;<? print $s["name"]; ?>&raquo;</h1><form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand=<? print rand(); ?>" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="21">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">
    <table cellspacing="1" width="100%" class="FileFormHeadTable">

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Путь для переноса<br>
    <select class="cf" name="f_id" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($fold_id);

        ?>
    </select>
        </td>
      </tr>

      <tr>
        <td class="FileFormSubnit" colspan=2>
          <table cellspacing="3">
            <tr>
              <td><input tabindex=60 type="submit" value="Перенести папку" class="button" style="font-weight:700"></td>
              <td><input tabindex=61 type="reset" onClick=javascript:{setTimeout("change_pole('name');",10);}; value="Отменить изменения" class="button"></td>
            </tr>
          </table>
        </td>
      </tr>

      </table></form>


    </div>
    
</div>

</body>
</html>
<?



      break;


      case(11): // копирование папки

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
      $s=mysql_fetch_array($line10);


      include "inc/header.php";


?>

<SCRIPT language="JavaScript"><!--
  function change_pole(name){
    try {
      eval("s=document.forms['workform2']."+name+".value.length;");
      eval("document.forms['workform2']."+name+"_.value=s;");
    } catch (e) {}
  }

  function check_name(){
        eval('f=document.workform2.f_id.value;');
        if(document.forms.workform2.name.value=="" || f==""){
                alert("Необходимо задать название файла и выбрать папку назначения!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

  function get_sel(){
        eval('f=document.workform2.f_id.value;');
        if(f!=""){
          //location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
  };

 //--></script>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
    
    ?>

    <div class="FileBlock">

<h1>Копирование папки &laquo;<? print $s["name"]; ?>&raquo;</h1><form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand=<? print rand(); ?>" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="22">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">
    <table cellspacing="1" width="100%" class="FileFormHeadTable">

      <tr>
        <td class="FileFormHead" width="100%">Новое название<br><input class="cf" type="text" style="width:100%;" name="name" value='<? print $s["name"]; ?>' onChange="change_pole('name')" onPaste="change_pole('name')" onKeyUp="change_pole('name')" tabindex=1></td>
        <td class="FileFormHead" width="1" valign="bottom"><input class="cfreadonly" type="text" style="width:40px;" value="60000" readonly title="Количество символов" name="name_"></td>
      </tr>

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Путь, куда копировать<br>
    <select class="cf" name="f_id" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($fold_id);

        ?>
    </select>
        </td>
      </tr>

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Копировать содержимое (файлы и подпапки) <input type="checkbox" name="copy" value='Yes' tabindex=1></td>
      </tr>

      <tr>
        <td class="FileFormSubnit" colspan=2>
          <table cellspacing="3">
            <tr>
              <td><input tabindex=60 type="submit" value="Копировать папку" class="button" style="font-weight:700"></td>
              <td><input tabindex=61 type="reset" onClick=javascript:{setTimeout("change_pole('name');",10);}; value="Отменить изменения" class="button"></td>
            </tr>
          </table>
        </td>
      </tr>


      </table><script>
change_pole('name');
      </script></form>

    </div>
    
</div>

</body>
</html>
<?



      break;



      case(12): // восстановление папки из корзины


      $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
      $s=mysql_fetch_array($line10);


      include "inc/header.php";

?>
<SCRIPT language="JavaScript"><!--
  function change_pole(name){
    try {
      eval("s=document.forms['workform2']."+name+".value.length;");
      eval("document.forms['workform2']."+name+"_.value=s;");
    } catch (e) {}
  }

  function check_name(){
        eval('f=document.workform2.f_id.value;');
        if(document.forms.workform2.name.value=="" || f==""){
                alert("Необходимо задать название файла и выбрать папку назначения!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

  function get_sel(){
        eval('f=document.workform2.f_id.value;');
        if(f!=""){
          //location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
  };

 //--></script>


<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
    
    ?>

    <div class="FileBlock">

<h1>Восстановление папки &laquo;<? print $s["name"]; ?>&raquo;</h1><form class="FileForm" ENCTYPE="multipart/form-data" action="./goods.php?rand=<? print rand(); ?>" name="workform2" method="POST" onSubmit="return check_name();">
    <input type="hidden" name="pageid" value="21">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">
    <table cellspacing="1" width="100%" class="FileFormHeadTable">

      <tr>
        <td class="FileFormHead" width="100%" colspan=2>Путь для восстановления<br>
    <select class="cf" name="f_id" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($s["goods_id_"]);

        ?>
    </select>
        </td>
      </tr>

      <tr>
        <td class="FileFormSubnit" colspan=2>
          <table cellspacing="3">
            <tr>
              <td><input tabindex=60 type="submit" value="Восстановить папку" class="button" style="font-weight:700"></td>
              <td><input tabindex=61 type="reset" onClick=javascript:{setTimeout("change_pole('name');",10);}; value="Отменить изменения" class="button"></td>
            </tr>
          </table>
        </td>
      </tr>

      </table></form>


    </div>
    
</div>

</body>
</html>
<?

      break;


      case(13): // покажем выпадающий попап с навигацией


        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title></title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<META NAME="description" CONTENT="">
<META NAME="keywords" CONTENT="">
<META NAME="ask" CONTENT="">
<META HTTP-EQUIV="keywords" CONTENT="">
<style>
body,td,p,div { font-family: Arial,Helvetica; font-size: 11px; color: #000000;}
body {margin:0px; padding:0px; background-color:#F5F5F5;}
p {margin-top:10px; margin-bottom:10px;}

#PopMainBlock {padding:5 5 5 5; background:#F5F5F5;}

.PopTreeUl {margin:0px 0px 0px 10px; list-style-image: url('i/fsmall.gif');}
.PopTreeUl li {padding:2px 0px 0px 3px; margin:0px 0px 0px 5px; vertical-align:top; color:#919191; font-size:10px; white-space:nowrap;}
.PopTreeUl a {color:#000000; text-decoration:none; font-size:10px; width:100%}
.PopTreeUl a:hover {color:#FFFFFF; background:#6C6C6C;}


</style>
<SCRIPT language="JavaScript"><!--
 //--></script>
</head>

<body>

<div id="PopMainBlock">
<?

print get_popup_three($page_id,$fold_id);

?>
</div>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td><img src="i/p.gif" alt="" border="0" width="1" height="1"></td>
  </tr>
</table>
   
</body>
</html>
        <?

      break;


      case(14): // импортнем эксель

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      include "inc/header.php";

      $line = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id and page_id=$page_id",$link);
      $string=mysql_fetch_array($line);


?>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>

 <div class="FileBlock"> 
<h1>Импорт EXCEL в папку &laquo;<? print $string["name"]; ?>&raquo;<a name="new">&nbsp;</a></h1>

<form ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform">

<table border='0' cellpadding='10' cellspacing='1' width="100%" bgcolor="#B5B5B5">
  <input type="hidden" name="pageid" value="2">
  <input type="hidden" name="subpageid" value="15">
  <input type="hidden" name="tek" value="-2">
  <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
  <input type="hidden" name="page_id" value="<? print $page_id; ?>">
  <input type="hidden" name="showhead" value="<? print $showhead; ?>">
          
  <tr>
    <td bgcolor="#EEEEEE">
      <table border='0' cellpadding='3' cellspacing='0' width="100%">
        <tr>
          <td width="1"><nobr>из столбца №</nobr></td>
          <td width="1">
            <select name="fromst[]" class=fsmall>
              <option value=''>&nbsp;</option>
<?
for ($i=1; $i<=$maxpoles; $i++)
    {
     ?><option value='<? print $i; ?>'><? print $i; ?></option><?
    };
?>
            </select>
          </td>
          <td align="right"><nobr>в поле &laquo;Название&raquo;</nobr></td>
          <td width="100%"><? print '<input type=checkbox name="namest[]" value="name">'; ?></td>
        </tr>    
      <?

      if($string["dat__"]!=0){

      ?>
        <tr>
          <td width="1"><nobr>из столбца №</nobr></td>
          <td width="1">
            <select name="fromst[]" class=fsmall>
              <option value=''>&nbsp;</option>
<?
for ($i=1; $i<=$maxpoles; $i++)
    {
     ?><option value='<? print $i; ?>'><? print $i; ?></option><?
    };
?>
            </select>
          </td>
          <td align="right"><nobr>в поле &laquo;Дата&raquo;</nobr></td>
          <td width="100%"><? print '<input type=checkbox name="namest[]" value="dat">'; ?></td>
        </tr>
       <?

       };

       if($string["seq__"]!=0){

       ?> 
        <tr>
          <td width="1"><nobr>из столбца №</nobr></td>
          <td width="1">
            <select name="fromst[]" class=fsmall>
              <option value=''>&nbsp;</option>
<?
for ($i=1; $i<=$maxpoles; $i++)
    {
     ?><option value='<? print $i; ?>'><? print $i; ?></option><?
    };
?>
            </select>
          </td>
          <td align="right"><nobr>в поле &laquo;Последовательность&raquo;</nobr></td>
          <td width="100%"><? print '<input type=checkbox name="namest[]" value="seq">'; ?></td>
        </tr>    
        <?

        };

        for ($i=1; $i<=$maxpoles; $i++)
          {
           if (($string["pole".$i])&&(($string["pole".$i."_"]!=2)))
              {
?>
<tr>
  <td width="1"><nobr>из столбца №</nobr></td>
  <td width="1">
    <select name="fromst[]" class=fsmall>
      <option value=''>&nbsp;</option>
<?
for ($i2=1; $i2<=$maxpoles; $i2++)
    {
?>
     <option value='<? print $i2; ?>'><? print $i2; ?></option>
<?
    };
?>
    </select>
  </td>
  <td align="right"><nobr>в поле &laquo;<? print $string["pole".$i]; ?>&raquo;</nobr></td>
  <td width="100%"><? print "<input type=checkbox name='namest[]' value='pole".$i."' >"; ?></td>
</tr>
<?
              };
          };
?>      
      </table>
    </td>
  </tr>
</table><img src="i/p.gif" border="0" alt="" width="1" height="4"><table border='0' cellpadding='10' cellspacing='1' width="100%" bgcolor="#B5B5B5">
  <tr>
    <td bgcolor="#EEEEEE">
      <table border='0' cellpadding='3' cellspacing='0' width="100%">
        <tr>
          <td align="right"><nobr>Укажите путь к документу Excel<br>на вашем компьютере</nobr></td>
          <td width="100%">
            <table cellpadding="8" cellspacing="1" border="0" bgcolor="#C5C5C5" width="100%">
              <tr>
                <td bgcolor="#F6F6F6"><input class="f" type="file" name="excel_file" size="20"></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right"><nobr><b><font color="red">Очистить папку<br>перед импортом</font></b></nobr></td>
          <td width="100%"><input type="checkbox" name="clear" value="Yes"></td>
        </tr>
        <tr>
          <td colspan="3" width="100%">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3" width="100%"><input class=fsmallb type="submit" size="10" value="Приступить"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>


    
</div>
</div>

</body>
</html>
<?

      break;


      case(15): // распарсим эксель в базу

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      if($admin==0)norules();//запрет на действия для гостя


      include "inc/header.php";

      $line = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id and page_id=$page_id",$link);
      $string=mysql_fetch_array($line);


?>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>

 <div class="FileBlock"> 
<h1>Обработка файла EXCEL<a name="new">&nbsp;</a></h1>

<?



          $line = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
          $st=mysql_fetch_array($line);

          require "inc/excelparser.php";


$err_corr = "Формат не поддерживается или файл поврежден";

$excel_file_size;
$excel_file = $HTTP_POST_FILES['excel_file'];
if( $excel_file )
        $excel_file = $HTTP_POST_FILES['excel_file']['tmp_name'];

if( $excel_file == '' ) fatal("No file uploaded");

$fh = @fopen ($excel_file,'rb');
if( !$fh ) fatal("No file uploaded");
if( filesize($excel_file)==0 ) fatal("No file uploaded");

$fc = fread( $fh, filesize($excel_file) );
@fclose($fh);
if( strlen($fc) < filesize($excel_file) )
        fatal("Cannot read file");

$exc = new ExcelFileParser;

$time_start = getmicrotime();
//if( $exc->ParseFromFile($excel_file)>0 ) fatal($err_corr);
$res = $exc->ParseFromString($fc);
$time_end = getmicrotime();

switch ($res) {
        case 0: break;
        case 1: fatal("Can't open file");
        case 2: fatal("File too small to be an Excel file");
        case 3: fatal("Error reading file header");
        case 4: fatal("Error reading file");
        case 5: fatal("This is not an Excel file or file stored in Excel < 5.0");
        case 6: fatal("File corrupted");
        case 7: fatal("No Excel data found in file");
        case 8: fatal("Unsupported file version");

        default:
                fatal("Unknown error");
}

//print "=$clear=";

if(($clear=="Yes")||($clear=="on")){
        //$cmd="DELETE FROM $tbl_goods_ WHERE goods_id=$fold_id and page_id=$page_id";
        //$line = mysql_query("$cmd",$link);
        //addlog("$cmd"."<b>".mysql_error()."</b>","импорт экселя хтмл - очистка папки");

     $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id and goods_id=$fold_id",$link);
     while($s=mysql_fetch_array($line10)){

       delete_file_trash($fold_id,$s[0]);

     };
     

};

        
        
        for($i=0;$i<count($fromst);$i++){
                if($fromst[$i]==""){
                  $j=$i;
                  while(($j<count($fromst))&&($fromst[$j]==""))$j++;
                  $fromst[$i]=$fromst[$j];
                  if($j<count($fromst))$fromst[$j]="";
                };
        };
        
        for( $ws_num=0; $ws_num<count($exc->worksheet['name']); $ws_num++ )
        {
                print "<b>Страница: \"";
                if( $exc->worksheet['unicode'][$ws_num] ) {
                        print uc2html($exc->worksheet['name'][$ws_num]);//."!";
                } else
                        print $exc->worksheet['name'][$ws_num];

                print "\"</b><br>";
                $ws = $exc->worksheet['data'][$ws_num];

                if( is_array($ws) &&
                    isset($ws['max_row']) && isset($ws['max_col']) ) {

                 for( $i=0; $i<=$ws['max_row']; $i++ ) {
                  if(isset($ws['cell'][$i]) && is_array($ws['cell'][$i]) ) {
                   for( $j=0; $j<=$ws['max_col']; $j++ ) {

                        if( ( is_array($ws['cell'][$i]) ) &&
                            ( isset($ws['cell'][$i][$j]) )
                           ){

                         $data = $ws['cell'][$i][$j];

                         $font = $ws['cell'][$i][$j]['font'];
                         $style = " style ='".ExcelFont::ExcelToCSS($exc->fonts[$font])."'";
                   
                   switch ($data['type']) {
                        // string
                        case 0:
                                $ind = $data['data'];
                                if( $exc->sst['unicode'][$ind] ) {
                                        $s = uc2html($exc->sst['data'][$ind]);
                                } else
                                        $s = $exc->sst['data'][$ind];
                                break;
                        // integer number
                        case 1:
                                $s=(int)($data['data']);
                                break;
                        // float number
                        case 2:
                                $s=(float)($data['data']);
                                break;
                        // date
                        case 3:

                                $ret = $exc->getDateArray($data['data']);
                                $s=$ret['day'].$ret['month'].$ret['year'];
                                break;
                        default:
                                break;
                   }

                   $arr[count($arr)]=$s;
                   print "$s/";

                   /**/
                        } else {
                        }
                   }
                  } else {
                        for( $j=0; $j<=$ws['max_col']; $j++ )
                                print "&nbsp;";
                        print "\n";
                  }

                  insarr();


                  unset($arr);
                  print "<br>";
                 }

                } else {
                        // emtpty worksheet
                        print "<b> - ничего нет</b><br>\n";
                }
                print "<br>";
        }


?>
    
</div>
</div>

</body>
</html>
<?
 

      break;



      case(16): // копирование структуры папки - шаг 1

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      include "inc/header.php";

      $line = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id and page_id=$page_id",$link);
      $s=mysql_fetch_array($line);


?>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>

 <div class="FileBlock"> 
<h1>Копирование свойств папки &laquo;<? print $s["name"]; ?>&raquo;<a name="new">&nbsp;</a></h1>

<form ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform">

<script language="JavaScript"><!-- 

function switch_chk(id){

  //alert(id+"/"+document.getElementById("p1").checked);

  if(document.getElementById("p"+id).checked)document.getElementById("p"+id).checked=false;
    else document.getElementById("p"+id).checked=true;

};

//--></script>

<table border='0' cellpadding='10' cellspacing='1' width="100%" bgcolor="#B5B5B5">
  <input type="hidden" name="pageid" value="2">
  <input type="hidden" name="subpageid" value="17">
  <input type="hidden" name="tek" value="-2">
  <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
  <input type="hidden" name="page_id" value="<? print $page_id; ?>">
  <input type="hidden" name="showhead" value="<? print $showhead; ?>">
          
  <tr>
    <td bgcolor="#EEEEEE">
      <table cellpadding=10 cellspacing=0 border=0>
        <tr>
          <td><b>ШАГ 1: выбор полей для копирования</b><br>

          <input type=checkbox name=poles[] value='readonly' id=p1> <a href="javascript:{switch_chk(1);}">Папка только для чтения</a><br>
          <!--<input type=checkbox name=poles[] value='dat__'> Показывать дату при выводе файлов<br>-->
          <input type=checkbox name=poles[] value='seq__' id=p2> <a href="javascript:{switch_chk(2);}">Показывать последовательность при выводе файлов</a><br>
          <input type=checkbox name=poles[] value='cansr' id=p3> <a href="javascript:{switch_chk(3);}">Поиск по папке разрешен</a><br>
          <input type=checkbox name=poles[] value='rem1' id=p4> <a href="javascript:{switch_chk(4);}">Выводить напоминание к папке как alert</a><br>
          <input type=checkbox name=poles[] value='rem2' id=p5> <a href="javascript:{switch_chk(5);}">Напоминание к папке</a><br>

          <?

for($i=1;$i<=$maxdescrs;$i++){
  
  if(strlen($s["descr".$i])>80)$s["descr".$i]=substr($s["descr".$i],0,80);
  if(strlen($s["opt".$i])>80)$s["opt".$i]=substr($s["opt".$i],0,80);

  print "<input type=checkbox name=poles[] value='opt$i' id=p1$i> <a href=\"javascript:{switch_chk(1$i);}\">Название к дополнительному описанию $i</a> (".htmlspecialchars($s["opt".$i]).")<br>";
  print "<input type=checkbox name=poles[] value='descr$i' id=p2$i> <a href=\"javascript:{switch_chk(2$i);}\">Дополнительное описание $i</a> (".htmlspecialchars($s["descr".$i]).")<br>";

};

for($i=1;$i<=$maxmores;$i++){
  
  print "<input type=checkbox name=poles[] value='more$i' id=p3$i> <a href=\"javascript:{switch_chk(3$i);}\">Галочка к дополнительному свойству $i</a> (".htmlspecialchars($s["more".$i]).")<br>";
  print "<input type=checkbox name=poles[] value='more".$i."_' id=p4$i> <a href=\"javascript:{switch_chk(4$i);}\">Дополнительное описание $i</a> (".htmlspecialchars($s["more".$i."_"]).")<br>";

};


for($i=1;$i<=$maxpoles;$i++){
  print "<input type=checkbox name=poles[] value='pole$i' id=p10$i> <a href=\"javascript:{switch_chk(10$i);}\">Поле$i</a> (".htmlspecialchars($s["pole".$i]).")<br>";
};


          ?></td>
        </tr>
        <tr>
          <td colspan="3" width="100%">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3" width="100%"><input class=fsmallb type="submit" size="10" value="Далее >>"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>


    
</div>
</div>

</body>
</html>
<?

      break;


      case(17): // копирование структуры папки - шаг 2

      if(!check_fold($user_id,$page_id,$fold_id)) norules();

      include "inc/header.php";

      $line = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id and page_id=$page_id",$link);
      $s=mysql_fetch_array($line);


?>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>

 <div class="FileBlock"> 
<h1>Копирование свойств папки &laquo;<? print $s["name"]; ?>&raquo;<a name="new">&nbsp;</a></h1>
<script language="JavaScript"><!-- 

function switch_chk(id){

  //alert(id+"/"+document.getElementById("p1").checked);

  if(document.getElementById("p"+id).checked)document.getElementById("p"+id).checked=false;
    else document.getElementById("p"+id).checked=true;

};

//--></script>

<form ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform">

<table border='0' cellpadding='10' cellspacing='1' width="100%" bgcolor="#B5B5B5">
  <input type="hidden" name="pageid" value="26">
  <input type="hidden" name="tek" value="-2">
  <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
  <input type="hidden" name="page_id" value="<? print $page_id; ?>">
  <input type="hidden" name="showhead" value="<? print $showhead; ?>">
          
  <tr>
    <td bgcolor="#EEEEEE">
      <table cellpadding=10 cellspacing=0 border=0>
        <tr>
          <td><b>ШАГ 2: выбор папок, для которых необходимо применить структуру</b><br>
<div id="PopMainBlock">
          <?

for($i=0;$i<count($poles);$i++) print '<input type="hidden" name="poles[]" value="'.$poles[$i].'">';

print get_popup_three1($page_id,$fold_id);

          ?>
</div>
          </td>
        </tr>
        <tr>
          <td colspan="3" width="100%">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3" width="100%"><input class=fsmallb type="submit" size="10" value="Применить"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>


    
</div>
</div>

</body>
</html>
<?

      break;


      case(19): // отредактируем файл-аттач к файлу
      case(18): // отредактируем файл-аттач к папке


      include "inc/header.php";

      $line = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id",$link);
      $s=mysql_fetch_array($line);

      $line = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id",$link);
      $ss=mysql_fetch_array($line);


?>

<script>
function check_name3(){
        if(document.forms.workform3.name.value==""){
                alert("Необходимо задать название прикрепляемого файла!");
                document.forms.workform3.name.focus();
                return false;
        };
        return true;
  };
</script>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>

 <div class="FileBlock"> 
<h1>Редактирование вложения &laquo;<? print $s["name"]; ?>&raquo;<a name="new">&nbsp;</a></h1>


<table border='0' cellpadding='10' cellspacing='1' width="100%" bgcolor="#B5B5B5"><tr><td>

<form class="FileForm" ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform3" onSubmit="return check_name3();">
    <input type="hidden" name="pageid" value="30">
    <input type="hidden" name="page_id" value="<? print $page_id; ?>">
    <input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
    <input type="hidden" name="file_id" value="<? print $file_id; ?>">
    <input type="hidden" name="good_id" value="<? print $good_id; ?>">
    <input type="hidden" name="showhead" value="<? print $showhead; ?>">

      <table cellspacing="1" width="100%">
        <tr>
          <td class="FileFormName">Имя</td>
          <td class="FileFormInput"><input class="cf" type="text" style="width:100%;" name="name" value='<? print $s["name"]; ?>'></td>
        </tr>

        <tr>
          <td class="FileFormName">Тип</td>
          <td class="FileFormInput"><select class="cf2" name="type" style="width:100%;"><? print get_select_types($s["type"]); ?></select></td>
        </tr>
        
        <tr>
          <td class="FileFormName">Путь к документу или картинке</td>
          <td class="FileFormInput" colspan="2"><input class="cf" type="file" style="width:100%;" name="userfile"></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td class="FileFormSubnit"><input type="submit" value="Загрузить" class="button" style="font-weight:700"></td>
      </tr>
      </table>
      </form>
    </td>
  </tr>
</table>

<br><br>
<a href="javascript:{history.back()}">назад</a>
<br><br>


<table cellspacing="1" width="100%" class="TableFiles">
<?

if($ss["type"]!=7 && $ss["type"]!=0){ // это НЕ картинка
          ?><tr>
            <td class="TF"><a href="<?

            if($pictures=="base") print '../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type='.$ss["type"];
                else print '../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type='.$ss["type"];
              
              if($pictures=="file"){
                 print '&filename='.$ss["fizname"];
              };

            ?>"><? print $ss["name"]; ?></a> (<? print $ss["fizname"]; ?>)</td>
            <td class="TF"><? print get_pix_types_name($ss["type"]); ?></td>
            <td class="TFaction"><a href="<?
            
            if($pictures=="base") print '../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type='.$ss["type"];
                else print '../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type='.$ss["type"];
              
              if($pictures=="file"){
                 print '&filename='.$ss["fizname"];
              };

            ?>"><img src="i/open2.gif" alt="открыть" width="16" height="15" hspace="4"></a></td>
          </tr><?
        
        } else { // картинка

          $text='<tr><td colspan=3><table cellspacing="0" width="100%"><tr>

        <td class="FileFormName">'.$ss["name"].'</td>
        <td class="FileFormInput">';

          //if($s["pole".$i]!=""){
            $text.='<img src="../showimg.php?id=';
            
            if($pictures=="base") $text.=$ss[0];
              else $text.=$ss["diskname"];

            $text.='&wd=200&hd=70" height="70" border="0" alt="" vspace="4" hspace="10" align="left">
            <ul style="margin:0px 0px 0px 120px;">
              <li style="list-style-image: url('."'".'i/open.gif'."'".'); vertical-align:top; padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base")$text.='../showpix.php?id='.$ss[0].'&rand='.rand().'&type=7';
                else $text.='../upload/'.$ss["diskname"].'?rand='.rand();

              $text.='" target=_blank>увеличить</a></li>
              <li style="list-style-image: url('."'".'i/i34.gif'."'".'); vertical-align:top;  padding:2px 0px 0px 7px;"><a href="';
              
              if($pictures=="base") $text.='../showpix.php?id='.$ss[0].'&rand='.rand().'&mode=save&type=7';
                else $text.='../showpix.php?id='.$ss["diskname"].'&rand='.rand().'&mode=save&type=7';
                //$text.='../upload/'.$ss["diskname"].'?rand='.rand().';

              //if($pictures=="base") $text.=$ss[0];
              //  else $text.=$ss["diskname"];
              
              if($pictures=="file"){
                $text.='&filename='.$ss["fizname"];
              };

              $text.='">сохранить</a></li>

            </ul>';
          //};

        $text.='</td>
        </tr></table></td>
      </tr>';

        print $text;

        };

?>
</table>    
</div>
</div>

</body>
</html>
<?



      break;


      case(20): // доступ на папку

      
      if($admin<3)norules();//запрет на действия для гостя

      $line = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
      $s=mysql_fetch_array($line);

      include "inc/header.php";

?>

<div id="MainBlock">

    <?
  
  print get_top_navi($page_id,$fold_id);
  
  ?>

      
 <div class="FileBlock"> 
<h1>Управление доступом к папке &laquo;<? print $s["name"]; ?>&raquo;<a name="new">&nbsp;</a></h1>

<b>Пользователи, которым открыт доступ к папке:</b><br>
<form class="FileForm" ENCTYPE="multipart/form-data" action="goods.php" method="post" name="workform3">
<input type="hidden" name="pageid" value="34">
<input type="hidden" name="page_id" value="<? print $page_id; ?>">
<input type="hidden" name="fold_id" value="<? print $fold_id; ?>">
<input type="hidden" name="file_id" value="<? print $file_id; ?>">
<input type="hidden" name="good_id" value="<? print $good_id; ?>">
<input type="hidden" name="showhead" value="<? print $showhead; ?>">

<?

$line = mysql_query("SELECT * FROM $tbl_goods_users WHERE status<>3 and status<>0 ORDER BY status DESC",$link);
while($s=mysql_fetch_array($line)){

  print '<input type="checkbox" name=ids[] value='.$s[0];
  if(check_fold($s[0],$page_id,$fold_id)) print " CHECKED";
  print '> '.$s["name"]." (".$s["login"].") - ";

  switch($s["status"]){
        
        case(0): print "Гость";
        break;
        case(1): print "Пользователь";
        break;
        case(2): print "Менеджер";
        break;
        case(3): print "Администратор";
        break;

    };

  print "<br>";


};

?>      
<input type="submit" value="сохранить">
</form>      
<b>Внимание!</b><br>
Доступ можно выставить только для пользователей со статусом "менеджер".<br>
<b>Хотябы у уодного пользователя со статусом "менеджер" должен быть доступ к папке!</b><br>
Если не выбирать никого - то будет полный доступ для всех.<br>
Администраторы всегда имеют полный доступ.<br>

</div>
</div>

</body>
</html>
<?

      break;


    };

  break;

  case(3):// помощь
  break;

  case(4):// настройки
  break;

  case(5):// поиск

    $showhead=-1;

    include "inc/header.php";

    $type=intval($type);
    
    
    ?><div id="MainBlock">

  <div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>
        <td class="NavBlockAddress"><a href="goods.php?pageid=5&rand=<? print rand(); ?>"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">Поиск</a></td>
      </tr>
    </table>
  </div>
  <div class="Files"><?
    /*

    ?><div id="MainBlock">


    <div class="FileBlock"> 
    <? */ ?>
    <h1>Поиск по базе данных</h1>


    <div class="ToolBar">
    <table cellspacing="0" width="100%">
      <tr>
        <td align="right">
          <form class="FilesSearchForm" ENCTYPE="multipart/form-data" action="goods.php?r=461057728" method="GET" name="workform">
          <input type="hidden" name="pageid" value="5">
          <input type="hidden" name="tek" value="0">
          <input type="hidden" name="showhead" value="<? print $showhead; ?>">
            <table cellspacing="1">
              <tr>
                <td><input type="text" name="search" class="cf" value="<? print $search; ?>"></td>
                <!--<td><select name="type" class="cf"><option value="0">поиск в файлах</option><option value="1"<? if($type==1)print " SELECTED"; ?>>поиск в папках</option></select></td>-->
                <td><input class="button" type="submit" size="10" value="найти"></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
  </div>

<script language="JavaScript"><!--
   bg1="#FF0000";
   bg2="#00FF00";
   bg="#FF0000";

   ii=<? 
   if($tek>=0)print $tek+1; 
     else print 1;
   ?>;

   function off_row(id){
     eval('document.all["row'+id+'"].className = "TF";');
   };

   function light_row(id){
     eval('document.all["row'+id+'"].className = "TF2";');
   };

function w(id,name,page_id,fold_id){
        if(bg==bg1)bg=bg2; else bg=bg1;
        
        document.write("<tr class=TF id=row"+id+" ");
        //document.write(" onmouseover=\"setPointer(this, 0, 'over', '"+bg+"', '#DDDDDD', '#FFCC99');\" onmouseout=\"setPointer(this, 0, 'out', '"+bg+"', '#DDDDDD', '#FFCC99');\"");
        
        document.write(" onMouseOut=\"off_row("+id+")\" onMouseOver=\"light_row("+id+")\" ");
        
        document.write("><td width=1><b>"+id+"</td>");
        //document.write("<td>"+ii+". <a href='goods.php?pageid=5&r="+Math.random()+"&good_id="+id+"&page_id="+page_id+"&fold_id="+fold_id+"'>"+name+"</a></td>");
        //document.write("<td>"+ii+". <a href='javascript:{self.parent.create_dot("+'"проводник - '+name+'"'+","+'"./goods.php?showhead=1&pageid=2&subpageid=6&r=<? print rand(); ?>&good_id='+id+'&page_id='+page_id+'&fold_id='+fold_id+'"'+")}'>"+name+"</a></td>");
        document.write("<td>"+ii+". <a href='javascript:{self.parent.create_dot("+'"проводник - '+name+'"'+","+'"&showhead=-2&pageid=2&subpageid=6&r=<? print rand(); ?>&good_id='+id+'&page_id='+page_id+'&fold_id='+fold_id+'"'+")}'>"+name+"</a></td>");


                ii++;
   };
//--></script>


  <?

  
  if($search!=""){
    
    
/*      
  $txt=get_folds($page_id,$fold_id); 
  if($txt!="")print '<div class="Folders">'.$txt.'</div>';
  
  
  $pages=get_file_pages($fold_id,$tek);
  $ltrs=get_file_letters($fold_id,$letter);
*/  
      
      print '<div class="Files">';
/*  
  if($pages!="" || $ltrs!=""){
  ?>
  
    <div class="FilesNavigation">
      <table cellspacing="0" width="100%" class="FilesNavigationTable">
        <tr>
          <td valign="bottom"><?

            if($pages!=""){

            ?><div class="FilesPages">
              <div class="FilePageDescr">страницы</div>
              <? print $pages; ?>
              <div class="FilePage<? if($tek==-1) print "Active"; ?>"><a href="./goods.php?showhead=<? print $showhead; ?>&pageid=5&rand=<? print rand(); ?>&tek=-1">все</a></div>
            </div>
            <?
            
            };

            if($ltrs!=""){

            ?>
            <div class="FilesPages">
              <div class="FilePageDescr">указатель</div>
              <? print $ltrs; ?>
              <div class="FilePage<? if($letter=="") print "Active"; ?>"><a href="./goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=3&page_id=<? print $page_id; ?>&fold_id=<? print $fold_id; ?>&rand=<? print rand(); ?>">все</a></div>
            </div>
            <? 
            
            };
            
            ?>
          </td>
        </tr>
      </table>
    </div>
    
  <?
  };
*/
      ?><table cellspacing="1" class="TableFiles">
      <tr>
      <td class="TFHeader">ID</td><td class="TFHeader">Название файла</td><?

      print "<script>";
      
      $sr=" and (name LIKE '%$search%' or dat LIKE '%$search%' or seq LIKE '%$search%'";
      for($i=1;$i<=$maxpoles;$i++)$sr.=" or pole$i LIKE '%$search%' ";
      $sr.=")";

      $cmd="SELECT $tbl_goods_.* FROM $tbl_goods_ WHERE page_id!=1 $sr ORDER BY name";
      //print $cmd;
      $line = mysql_query($cmd,$link);
      while($s=mysql_fetch_array($line)){

        print "w($s[0],'".substr(eregi_replace("'",'"',strip_tags($s["name"])),0,165)."',".$s["page_id"].",".$s["goods_id"].");";
      
      };

      print "</script></table>";
    

//    };

  };


  ?>

    </div></div></body></html><?


  break;

  case(6):// пользователи

//print "=$admin=";

    if($admin<3)norules();//запрет на действия для гостя
    
    $showhead=-1;
    
    switch($subpageid){

      default: // покажем список пользователей
      case(0):

        include "inc/header.php";


        ?><div id="MainBlock">
  <div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>
        <td class="NavBlockAddress"><a href="goods.php?&pageid=6&rand=<? print rand(); ?>"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">Пользователи и пароли</a></td>
      </tr>
    </table>
  </div>
  <div class="Files">
    <h1>Пользователи и пароли</h1>
    <p>Панель управления &laquo;Пользователи и пароли&raquo; позволяет добавлять пользователей системы управления. Управление пользователями доступно только администратору.</p>
    <table cellspacing="1" class="TableFiles">
      <tr>
        <td class="TFHeader">Имя</td>
        <td class="TFHeader">Последнее посещение системы управления</td>
        <td class="TFHeader">Статус</td>
        <td class="TFHeader">&nbsp;</td>
      </tr><?


/*    
        ?><div id="MainBlock"><div class="FileBlock"> 

<table width="50%" cellpadding="4" cellspacing="00" border="0">
  <tr>
    <td class="cg"><b>Пользователи</b></td>
  </tr>
</table>

<table width="100%" cellpadding="4" cellspacing="1" border="0" bgcolor="#C5C5C5">
  <tr bgcolor="#EEEEEE">
    <td width="50%" class="cw"><b>Ник (имя)</b></td>
    <td width="50%" class="cw"><b>Последнее посещение системы управления</b></td>
    <td width="1" class="cw"><b>Статус</b></td>
  </tr>
<?
*/

$tek=intval($tek);
$mpage=200; 
if ((!$letter)&&(!$search))
   {
    $cmd="SELECT * FROM $tbl_goods_users WHERE login<>'' ORDER BY name LIMIT $tek,$mpage";
    $cmd2="SELECT count(id) FROM $tbl_goods_users";
   }; 

if ($letter)
   {
   $cmd="SELECT * FROM $tbl_goods_users WHERE ='$letter' ORDER BY name LIMIT $tek,$mpage";
   $cmd2="SELECT count(id) FROM $tbl_goods_users";
   }; 

if ($search)
   {
   $cmd="SELECT * FROM $tbl_goods_users WHERE (name LIKE '%$search%') or (login LIKE '%$search%') ORDER BY name LIMIT $tek,$mpage";
   $cmd2="SELECT count(id)FROM $tbl_goods_users WHERE (name LIKE '%$search%') or (login LIKE '%$search%')";
   };

//print $cmd;

$line = mysql_query($cmd,$link);
while($s=mysql_fetch_array($line))
  {

  ?><tr>
        <td><a href="goods.php?pageid=6&id=<? print $s[0]; ?>&rand=<? print rand(); ?>&subpageid=1"><? print $s["name"]; ?></a> (<? print $s["login"]; ?>)</td>
        <td><? print full_dat($s["regtime"],"/"," - ",":"); ?></td>
        <td><?
        
        switch($s["status"]){
        
        case(0): print "Гость";
        break;
        case(1): print "Пользователь";
        break;
        case(2): print "Менеджер";
        break;
        case(3): print "Администратор";
        break;

    };

        ?></td>
        <td class="TFaction"><a href="javascript:{if(window.confirm('Удалить пользователя? Внимание, восстановить пользователя будет невозможно!'))location.href='goods.php?pageid=6&subpageid=3&id=<? print $s[0]; ?>&rand=<? print rand(); ?>'}"><img src="i/del.gif" alt="удалить" width="15" height="15" hspace="4"></a></td>
      </tr><?
/*
?>
  <tr bgcolor="#FFFFFF">
    <td><b><a href="goods.php?pageid=6&id=<? print $s[0]; ?>&rand=<? print rand(); ?>&subpageid=1"><? print $s["login"]; ?> (<? print $s["name"]; ?>)</b></a></td>
    <td><? print full_dat($s["regtime"],"/"," - ",":"); ?></td>
    <td><? 

    switch($s["status"]){
        
        case(0): print "Гость";
        break;
        case(1): print "Пользователь";
        break;
        case(2): print "Менеджер";
        break;
        case(3): print "Администратор";
        break;

    };

     ?></td>
  </tr>
<?
        */

  };

  ?></table>

    <a href="goods.php?pageid=6&subpageid=1&rand=<? print rand(); ?>" class="AddUser">Добавить пользователя</a>
  </div>
 
</div>


</body>
</html><?

/*
?>
</table><img src="i/p.gif" alt="" border="" width="1" height="3">
<br><br>
<a href="goods.php?pageid=6&subpageid=1&rand=<? print rand(); ?>">добавить нового пользователя</a>
<br><br>


        </div></div></body></html><?
*/

      break;


      case(1): // посомтрим на пользователя - отредактируем или добавим нового



      include "inc/header.php";

unset($s);
    
        /*
        ?><div id="MainBlock"><div class="FileBlock"><?
        */

if($id!=""){
  $line = mysql_query("SELECT * FROM $tbl_goods_users WHERE id=$id",$link);
  $s=mysql_fetch_array($line);
};
?>

<div id="MainBlock">
  <div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>
        <td class="NavBlockAddress"><a href="./goods.php?pageid=6&rand=<? print rand(); ?>"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">Пользователи и пароли</a> / <? if($id!=0){ ?><a href="./goods.php?pageid=6&id=<? print $id; ?>&subpageid=1&rand=<? print rand(); ?>"><? print $s["name"]; ?></a><? } else { ?><a href="./goods.php?pageid=6&subpageid=1&rand=<? print rand(); ?>">Добавление нового пользователя</a><? }; ?></td>
      </tr>
    </table>
  </div>


<? if($user_id==$id || intval($id)==0){ ?>

<script>

function pass_gen(){
//ф-я генерит случайный пароль
        
        pass="";
        for(i=0;i<8;i++){

          r=Math.ceil(Math.random()*3);

          if(r==1)pass+=""+String.fromCharCode(65+Math.ceil(Math.random()*24))
          if(r==2)pass+=""+String.fromCharCode(97+Math.ceil(Math.random()*24))
          if(r==3)pass+=""+String.fromCharCode(48+Math.ceil(Math.random()*9))

          //pass+=""+r;

        };

        document.workform.pass_.value=pass;

        alert("Внимание!!! Обязательно запишите пароль на листочек или в отдельный файл на вашем компьютере!");
        alert("Внимание!!! Если Вы нажмете на сохранение информации и Вы изменили пароль для себя, необходимо будет сразу же его ввести!");

};

</script>

<? } else { ?>

<script>

function pass_gen(){
//ф-я генерит случайный пароль
        
        //alert(":".charCodeAt(0));
        pass="";
        for(i=0;i<8;i++){

          r=Math.ceil(Math.random()*3);

          if(r==1)pass+=""+String.fromCharCode(65+Math.ceil(Math.random()*24))
          if(r==2)pass+=""+String.fromCharCode(97+Math.ceil(Math.random()*24))
          if(r==3)pass+=""+String.fromCharCode(48+Math.ceil(Math.random()*9))

          //pass+=""+r;

        };

        document.workform.pass1_.value=pass;
        document.workform.pass2_.value=pass;

        alert("Внимание!!! Обязательно запишите пароль на листочек или в отдельный файль на вашем компьютере!");
        alert("Внимание!!! Если Вы нажмете на сохранение информации и Вы изменили пароль для себя, необходимо будет сразу же его ввести!");

};

</script>

<? }; ?>

<div class="Files">
    <h1><? if($id!=0)print $s["name"]; else print "Новый пользователь"; ?></h1>
    <? if($id!=0){ ?><p>Время последней модификации данных <? print full_dat($s["regtime"]," "," - ",":"); ?></p>
    <? /* ?>
    <a href="goods.php?rand=<? print rand(); ?>&pageid=75&u_id=<? print $id; ?>">посмотреть лог</a>
    <? */ ?>
    <? }; ?>
    
    <form ENCTYPE="multipart/form-data" action="goods.php?<? print rand(); ?>" method="post" name="workform">
  <input type="hidden" name="pageid" value="6">
  <input type="hidden" name="subpageid" value="2">
  <input type="hidden" name="id" value="<? print $id; ?>">

    

    <table cellspacing="1" width="100%">
      <tr>
        <td width="70%">
          <table cellspacing="1" width="100%">
            <tr>
              <td class="FileFormName">Имя</td>
              <td class="FileFormInput"><input type="text" name="name_" class="cf" value="<? print $s["name"]; ?>"></td>
            </tr>

            <tr>
              <td class="FileFormName">Логин</td>
              <td class="FileFormInput"><input type="text" name="login_" class="cf" value="<? print $s["login"]; ?>"></td>
            </tr>
            <? if($user_id==$id || intval($id)==0){ ?>
            <tr>
              <td class="FileFormName">Сменить пароль <a href="javascript:{pass_gen();}">создать случайный</a></td>
              <td class="FileFormInput"><input type="text" name="pass_" class="cf" value="<? print $s["pass"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br></td>
            </tr>
            <? } else { 
            
            $s["pass"]="********";

            ?>
            <tr>
              <td class="FileFormName">Сменить пароль <a href="javascript:{pass_gen();}">создать случайный</a></td>
              <td class="FileFormInput"><input type="text" name="pass1_" class="cf" value="<? print $s["pass"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br></td>
            </tr>
            <tr>
              <td class="FileFormName">Повтор пароля</td>
              <td class="FileFormInput"><input type="text" name="pass2_" class="cf" value="<? print $s["pass"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br></td>
            </tr>
            <? }; ?>
            <? /* ?>
            <tr>
              <td class="FileFormName">Город</td>
              <td class="FileFormInput"><input type="text" name="city" class="cf" value=""></td>
            </tr>
            <? */ ?>
            <tr>
              <td class="FileFormName">Пол:</td>
              <td class="FileFormInput"><select class="cf" name="sex">
              <option value="M" <? if($s["sex"]=="M") print "Selected"; ?>>Мужчина</option>
              <option value="W" <? if($s["sex"]=="W") print "Selected"; ?>>Женщина</option></select></td>
            </tr>
            <tr>
              <td class="FileFormName">ICQ</td>
              <td class="FileFormInput"><input type="text" name="icq" class="cf" value="<? print $s["icq"]; ?>"></td>
            </tr>
            <tr>
              <td class="FileFormName">E-mail</td>
              <td class="FileFormInput"><input type="text" name="email" class="cf" value="<? print $s["email"]; ?>"></td>
            </tr>
       
            <tr>
              <td class="FileFormName">Дата рождения:</td>
              <td class="FileFormInput">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                  <tr>
                    <td>
                    <select name="bd" class="cf">
                      <?

                  for($i=1;$i<32;$i++){
                        if($i==intval(substr($s["bdat"],6,2))) print "<option value='$i' Selected>$i</option>";
                        else print "<option value='$i'>$i</option>";
                  };

                  ?>
                      </select>
                    </td>
                    <td>
                    <select name="bm" class="cf">
                      <?

                  for($i=1;$i<13;$i++){
                        if($i==intval(substr($s["bdat"],4,2))) print "<option value='$i' Selected>".get_month($i)."</option>";
                        else print "<option value='$i'>".get_month($i)."</option>";
                  };

                  ?>
                      </select>
                    </td>
                    <td>
                    <select name="by" class="cf">
                      <?

                  for($i=1900;$i<2001;$i++){
                        if($i==intval(substr($s["bdat"],0,4))) print "<option value='$i' Selected>$i</option>";
                        else print "<option value='$i'>$i</option>";
                  };

                  ?>
                  </select>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td class="FileFormName">Статус</td>
              <td class="FileFormInput"><select name="status" class="cf">
              <option value=0 <? if($s["status"]==0)print "Selected"; ?>>Гость (только просмотр)</option>
              <option value=2 <? if($s["status"]==2)print "Selected"; ?>>Менеджер (полный доступ кроме добавления/изменения пользователей) </option>
              <option value=3 <? if($s["status"]==3)print "Selected"; ?> >Администратор (полный доступ) </option></select></td>
            </tr>
            
          </table>
        </td>
        <td width="30%" align="center" valign="bottom" bgcolor="#E2F1FC">
          <div class="UserPhoto">
<?
            if ($s["pix"]) {?><img src="../showpix.php?id=<? print $s["pix"]; ?>" alt="" border="0" hspace="4"><?} else { print "<center>фотографии</center><center>нет</center>"; };
?>

            <input type="file" name="userfile" class="cf">
            <? if ($s["pix"]) { ?><br><a href="goods.php?pageid=6&subpageid=4&id=<? print $s[0]; ?>&rand=<? print rand(); ?>">удалить фото</a><? }; ?>
          </div>
        </td>
      </tr>
      
    </table>
    

    

    <table cellspacing="1" width="100%">
      <tr>
        <td class="FileFormInput"><b>Дополнительно</b><textarea type="text" name="descr"  class="cf" rows="14"><? print eregi_replace("<",'&#60;',stripslashes($s["descr"])); ?></textarea></td>
      </tr> 
     
      
      <tr>

        <td class="FileFormSubnit"><input type="submit" size="10" value="сохранить"></td>
      </tr>
    </table>
    </form>
  </div>





<? /* ?>
<table cellpadding="4" cellspacing="00" border="0">
  <tr>
<? if(intval($id)!=0){ ?>
    <td class="cg"><b>Информация о пользователе <? print $s["login"]." (".$s["name"].")" ?> </b></td>
<? } else { ?>
    <td class="cg"><b>Добавление нового пользователя</b></td>
<? }; ?>
  </tr>
</table>



<? */ /* ?>

<table width="100%" cellpadding="14" cellspacing="1" border="0" bgcolor="#C5C5C5">
  <form ENCTYPE="multipart/form-data" action="goods.php?<? print rand(); ?>" method="post" name="workform">
  <input type="hidden" name="pageid" value="6">
  <input type="hidden" name="subpageid" value="2">
  <input type="hidden" name="id" value="<? print $id; ?>">
  <tr>
    <td bgcolor="#EEEEEE" valign=top>
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="100%" valign=top>
            Имя:<br>
            <input type="text" name="name_" class="f" value="<? print $s["name"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

            Login:<br>
            <input type="text" name="login_" class="f" value="<? print $s["login"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>
        
<? if($user_id==$id || intval($id)==0){ ?>

            Пароль:<br>
            <input type="text" name="pass_" class="f" value="<? print $s["pass"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

            <input type="button" value="генерация пароля" onClick="pass_gen()"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

<script>

function pass_gen(){
//ф-я генерит случайный пароль
        
        pass="";
        for(i=0;i<8;i++){

          r=Math.ceil(Math.random()*3);

          if(r==1)pass+=""+String.fromCharCode(65+Math.ceil(Math.random()*24))
          if(r==2)pass+=""+String.fromCharCode(97+Math.ceil(Math.random()*24))
          if(r==3)pass+=""+String.fromCharCode(48+Math.ceil(Math.random()*9))

          //pass+=""+r;

        };

        document.workform.pass_.value=pass;

        alert("Внимание!!! Обязательно запишите пароль на листочек или в отдельный файл на вашем компьютере!");
        alert("Внимание!!! Если Вы нажмете на сохранение информации и Вы изменили пароль для себя, необходимо будет сразу же его ввести!");

};

</script>

<? } else { ?>

<script>

function pass_gen(){
//ф-я генерит случайный пароль
        
        //alert(":".charCodeAt(0));
        pass="";
        for(i=0;i<8;i++){

          r=Math.ceil(Math.random()*3);

          if(r==1)pass+=""+String.fromCharCode(65+Math.ceil(Math.random()*24))
          if(r==2)pass+=""+String.fromCharCode(97+Math.ceil(Math.random()*24))
          if(r==3)pass+=""+String.fromCharCode(48+Math.ceil(Math.random()*9))

          //pass+=""+r;

        };

        document.workform.pass1_.value=pass;
        document.workform.pass2_.value=pass;

        alert("Внимание!!! Обязательно запишите пароль на листочек или в отдельный файль на вашем компьютере!");
        alert("Внимание!!! Если Вы нажмете на сохранение информации и Вы изменили пароль для себя, необходимо будет сразу же его ввести!");

};

</script>


            Сменить пароль:<br>
            <input type="text" name="pass1_" class="f" value="********"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>
            
            Повтор:<br>
            <input type="text" name="pass2_" class="f" value="********"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

            <input type="button" value="генерация пароля" onClick="pass_gen()"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

<? }; ?>
            Город:<br>
     <input type="text" name="city" class="f" value="<? print $s["city"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

     Пол:<br>
     <select class="f" name="sex">
       <option value="M" <? if($s["sex"]=="M") print "Selected"; ?>>Мужчина</option>
       <option value="W" <? if($s["sex"]=="W") print "Selected"; ?>>Женщина</option>
     </select><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

     ICQ:<br>
     <input type="text" name="icq" class="f" value="<? print $s["icq"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

     E-mail:<br>
     <input type="text" name="email" class="f" value="<? print $s["email"]; ?>"><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

<? if(intval($id)!=0){ ?>
     Время последней модификации данных: <? print full_dat($s["regtime"],"/"," - ",":"); ?>
     <br>
     <a href="goods.php?rand=<? print rand(); ?>&pageid=75&u_id=<? print $id; ?>">посмотреть лог</a>
<? }; ?>


          </td>
          <td width="1">&nbsp;&nbsp;&nbsp;</td>
          <td>

          Дата рождения:
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td>День
                <select name="bd" class="f">
                  <?

                  for($i=1;$i<32;$i++){
                        if($i==intval(substr($s["bdat"],6,2))) print "<option value='$i' Selected>$i</option>";
                        else print "<option value='$i'>$i</option>";
                  };

                  ?>
                </select>
                </td>
                <td>Месяц
                <select name="bm" class="f">
                  <?

                  for($i=1;$i<13;$i++){
                        if($i==intval(substr($s["bdat"],4,2))) print "<option value='$i' Selected>".get_month($i)."</option>";
                        else print "<option value='$i'>".get_month($i)."</option>";
                  };

                  ?>
                </select>
                </td>
                <td>Год
                <select name="by" class="f">
                  <?

                  for($i=1900;$i<2001;$i++){
                        if($i==intval(substr($s["bdat"],0,4))) print "<option value='$i' Selected>$i</option>";
                        else print "<option value='$i'>$i</option>";
                  };

                  ?>
                </select>
                </td>
              </tr>
            </table>


            <table cellpadding="0" cellspacing="10" border="0" width="200" bgcolor="#D7D7D7">
              <tr>
                <td bgcolor="#CCCCCC" height="200" align="center">
<?
            if ($s["pix"]) {?><img src="../showpix.php?id=<? print $s["pix"]; ?>" alt="" border="0" hspace="4"><?} else { print "<center>фотографии</center><br><center>нет</center>"; };
?>
                </td>
              </tr>
              <tr>
                <td><input type="file" name="userfile" class="f"><? if ($s["pix"]) { ?><br><a href="goods.php?pageid=6&subpageid=4&id=<? print $s[0]; ?>&rand=<? print rand(); ?>">удалить фото</a><? }; ?></td>
              </tr>
            </table>

          </td>
        </tr>
      </table>
    </td>
  </tr>
</table><img src="i/p.gif" alt="" border="0" width="1" height="3"><table width="100%" cellpadding="14" cellspacing="1" border="0" bgcolor="#000000">
  <tr>
    <td bgcolor="#E6E6E6">
     
     Статус:<br>
     <select name="status" class="f">
       <option value=0 <? if($s["status"]==0)print "Selected"; ?>>Гость (только просмотр) </option>
       <option value=2 <? if($s["status"]==2)print "Selected"; ?>>Менеджер (полный доступ кроме добавления/изменения пользователей) </option>
       <option value=3 <? if($s["status"]==3)print "Selected"; ?>>Администратор (полный доступ) </option>
     </select><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

     Дополнительно:
     <textarea type="text" name="descr"  class="f" rows="14"><? print eregi_replace("<",'&#60;',stripslashes($s["descr"])); ?></textarea><br><img src="i/p.gif" width="1" height="4" border="0" alt=""><br>

     <input class="fsmallb" type="submit" size="10" value="сохранить">
    </td>
  </tr>
</form>
</table>

<div align=right><a href="javascript:{if(window.confirm('Удалить пользователя? Внимание, восстановить пользователя будет невозможно!'))location.href='goods.php?pageid=6&subpageid=3&id=<? print $id; ?>&rand=<? print rand(); ?>'}"><b>Удалить пользователя</b></a></div>

<?

*/

?>

</div></div></body></html><?




      break;


      case(2): // изменим инфу о пользователе

        if ($userfile_size>0)
        {
         $line = mysql_query("SELECT * FROM $tbl_goods_users WHERE id=$id",$link);
         $s=mysql_fetch_array($line);
         if ($s["pix"])  // есть ли у юзера картинка
            {
             //$line = mysql_query("DELETE FROM $tbl_pix WHERE id=".$s["pix"],$link);
             
             delete_file_pole($s["pix"]);

            };

         /*
         $fp=fopen($userfile,"r"); 
         $buf=fread($fp,$userfile_size); 
         fclose($fp); 
         $buf=eregi_replace("'",'&#39;',$buf);
         $cmd="INSERT INTO $tbl_pix (pix) VALUES ('$buf')";
         $line = mysql_query($cmd,$link);
         $line = mysql_query("SELECT max(id) FROM $tbl_pix WHERE pix='$buf'",$link);
         $st=mysql_fetch_array($line);
         $pix=$st[0];
         */
         $pix=save_file($userfile_name,$userfile_size,$userfile,0,0,date("Ymd"),"","",0,0,0,0);
         
        };
     
     $by=intval($by);
     $bm=intval($bm);
     $bd=intval($bd);
     if(($by<1900)||($by>date("Y")))$by=1980;
     if($bm<10)$bm="0".$bm;
     if($bd<10)$bd="0".$bd;
     
     $bdat=$by.$bm.$bd;
     
     
     if($id!=0){

       if($pass1_==$pass2_ && $pass1_!="" && $pass1_!="********")$pass_=$pass1_;
       if($id==$user_id || $pass_!=""){ // сам себя
          $cmd="UPDATE $tbl_goods_users SET bdat=$bdat, name='$name_', login='$login_', pass='$pass_', status=".intval($status).", sex='$sex', city='$city', icq=".intval($icq).", email='$email', descr='$descr' WHERE id=$id";
       } else {
          $cmd="UPDATE $tbl_goods_users SET bdat=$bdat, name='$name_', login='$login_', status=".intval($status).", sex='$sex', city='$city', icq=".intval($icq).", email='$email', descr='$descr' WHERE id=$id";
       };
       
       $line = mysql_query($cmd,$link);

       addlog("$cmd","Изменим инфу о пользователе",mysql_error());
       //print $cmd.mysql_error();
       //exit(1);
       
       //addlog("$cmd"."<b>".mysql_error()."</b>","сохраним отредактированные данные пользователя в базу");

       //print mysql_error();

     } else {

       $cmd="INSERT INTO $tbl_goods_users (bdat, name, login, pass, status, sex, city, icq, email, descr) VALUES
                                              ($bdat,'$name_','$login_','$pass_',".intval($status).",'$sex','$city',".intval($icq).",'$email','$descr')";
       $line = mysql_query($cmd,$link);

       addlog("$cmd","Добавим нового пользователя",mysql_error());

       //print $cmd."<br>".mysql_error();
       //addlog("$cmd"."<b>".mysql_error()."</b>","сохраним данные нового пользователя в базу");

     };

     if ($userfile_size>0){
       $cmd="UPDATE $tbl_goods_users SET pix='$pix' WHERE id=$id";
       $line = mysql_query($cmd,$link);
       //addlog("$cmd"."<b>".mysql_error()."</b>","сохраним картинку пользователя в базу");
     };

     generate_users();
     
     header("Location:goods.php?rand=".rand()."&pageid=6");
     exit(1);


      break;

      
      
      case(3): // удалим пользователя

      $line = mysql_query("SELECT * FROM $tbl_goods_users WHERE id=$id",$link);
      $s=mysql_fetch_array($line);
      if ($s["pix"])  // есть ли у юзера картинка
         {
          delete_file_pole($s["pix"]);
         };
      
      $cmd="DELETE FROM $tbl_goods_users WHERE id=$id";
      $line = mysql_query($cmd,$link);

      addlog("$cmd","Удалим пользователя",mysql_error());
      
      generate_users();
     
      header("Location:goods.php?rand=".rand()."&pageid=6");
      exit(1);

      break;


      case(4): // удалим картинку пользователя

      $line = mysql_query("SELECT * FROM $tbl_goods_users WHERE id=$id",$link);
      $s=mysql_fetch_array($line);
      if ($s["pix"])  // есть ли у юзера картинка
         {
          delete_file_pole($s["pix"]);
         };
      
      $line = mysql_query("UPDATE $tbl_goods_users SET pix='' WHERE id=$id",$link);
      
      generate_users();
     
      header("Location:goods.php?rand=".rand()."&pageid=6");
      exit(1);

      break;


    };


  break;

  case(7):// изменим свойства тома

    if($admin==0)norules();//запрет на действия для гостя

    $name=check_str($name);
    if($name=="")$name="noname_".$page_id."_".$fold_id."_".$good_id;
    $descr=check_str($descr);

    $desctop=intval($desctop);
    $icon=check_str($icon);


    if($page_id==0){ // добавим том

      $cmd="INSERT INTO $tbl_pages (icon,desctop,name,descr,dat) VALUES ('$icon',$desctop,'$name','$descr',".date("Ymd").")";
      $line_ = mysql_query($cmd,$link);

      addlog("$cmd","Добавим том",mysql_error());
      
      $line = mysql_query("SELECT max(id) FROM $tbl_pages WHERE name='$name'",$link);
      $s=mysql_fetch_array($line);
      $page_id=$s[0];

    } else { // изменим том

      $cmd="UPDATE $tbl_pages SET icon='$icon',desctop=$desctop,name='$name',descr='$descr' WHERE id=$page_id";
      $line_ = mysql_query($cmd,$link);

      addlog("$cmd","Изменим свойста тома",mysql_error());

    };

    header("location: ./goods.php?showhead=$showhead&pageid=2&action=reload&subpageid=3&page_id=$page_id&rand=".rand());
    exit(1);

  break;

  case(8):// изменим свойства папки

    if($admin==0)norules();//запрет на действия для гостя

    $name=check_str($name);
    if($name=="")$name="noname_".$page_id."_".$fold_id."_".$good_id;
    $descr1=check_str($descr1);
    $descr2=check_str($descr2);
    $descr3=check_str($descr3);
    $descr4=check_str($descr4);
    $descr5=check_str($descr5);
    $opt1=check_str($opt1);
    $opt2=check_str($opt2);
    $opt3=check_str($opt3);
    $opt4=check_str($opt4);
    $opt5=check_str($opt5);


    $more1=check_str($more1);
    $more1_=check_str($more1_);
    $more2=check_str($more2);
    $more2_=check_str($more2_);
    $more3=check_str($more3);
    $more3_=check_str($more3_);
    $more4=check_str($more4);
    $more4_=check_str($more4_);
    $more5=check_str($more5);
    $more5_=check_str($more5_);
    $more6=check_str($more6);
    $more6_=check_str($more6_);
    $more7=check_str($more7);
    $more7_=check_str($more7_);
    $more8=check_str($more8);
    $more8_=check_str($more8_);
    $more9=check_str($more9);
    $more9_=check_str($more9_);
    $more10=check_str($more10);
    $more10_=check_str($more10_);

    $seq=intval(check_str($seq));
    $mpage=intval(check_str($mpage));

    $rem1=check_str($rem1);
    $rem2=check_str($rem2);
    $dat__=check_str($dat__);
    $seq__=check_str($seq__);
    $readonly=check_str($readonly);
    $cansr=check_str($cansr);

    $desctop=intval($desctop);
    $icon=check_str($icon);


    $c="";
    $c1="";
    $c2="";
    for($i=1;$i<=$maxpoles;$i++){
      //eval("\$c.=\",pole$i=\"'\".check_str(\$pole".$i.").\"'\"\";");
      $type=intval($HTTP_POST_VARS["pole".$i."_"]);

      if($type!=4 && $type!=2){
        eval("\$c.=\",pole$i='\".check_str(\$pole".$i.").\"'\";");
        eval("\$c.=\",pole".$i."_='\".check_str(\$pole".$i."_).\"'\";");
        eval("\$c.=\",pole".$i."__='\".check_str(\$pole".$i."__).\"'\";");
        
        eval("\$c1.=\",pole$i\";");
        eval("\$c2.=\",'\".check_str(\$pole".$i.").\"'\";");

        eval("\$c1.=\",pole".$i."_\";");
        eval("\$c1.=\",pole".$i."__\";");

        eval("\$c2.=\",'\".check_str(\$pole".$i."_).\"'\";");
        eval("\$c2.=\",'\".check_str(\$pole".$i."__).\"'\";");
        

      } else { // select
        eval("\$c.=\",pole$i='\".check_str(\$pole".$i."__h).\"'\";");
        eval("\$c.=\",pole".$i."_='\".check_str(\$pole".$i."_).\"'\";");
        eval("\$c.=\",pole".$i."__='\".check_str(\$pole".$i."__).\"'\";");
        eval("\$c1.=\",pole$i\";");
        eval("\$c2.=\",'\".check_str(\$pole".$i."__h).\"'\";");


        eval("\$c1.=\",pole".$i."_\";");
        eval("\$c1.=\",pole".$i."__\";");

        eval("\$c2.=\",'\".check_str(\$pole".$i."_).\"'\";");
        eval("\$c2.=\",'\".check_str(\$pole".$i."__).\"'\";");
        /*
        print $c."<br>";
        print $c1."<br>";
        print $c2."<br>";
        exit(1);
        */
      };

      //eval("\$c.=,pole$i_=\"'\".check_str(\$pole".$i."_).\"'\";");
      //eval("\$c.=,pole$i__=\"'\".check_str(\$pole".$i."__).\"'\";");
      //eval("\$pole".$i."_=check_str(\$pole".$i."_);");
      //eval("\$pole".$i."__=check_str(\$pole".$i."__);");
      //$c.=",pole$i=''";
    };

    //print "-$c-<hr>";
    //print "-$c1-<hr>";
    //print "-$c2-<hr>";
    //exit(1);
    
    
    if($fold_id==0){ // добавим папку

      $cmd="INSERT INTO $tbl_goods (more1_,more2_,more3_,more4_,more5_,more6_,more7_,more8_,more9_,more10_,more1,more2,more3,more4,more5,more6,more7,more8,more9,more10,desctop,icon,seq,mpage,page_id,goods_id,name,opt1,opt2,opt3,opt4,opt5,descr1,descr2,descr3,descr4,descr5,rem1,rem2,dat__,seq__,readonly,cansr $c1) 
            VALUES ('$more1_','$more2_','$more3_','$more4_','$more5_','$more6_','$more7_','$more8_','$more9_','$more10_','$more1','$more2','$more3','$more4','$more5','$more6','$more7','$more8','$more9','$more10',$desctop,'$icon',$seq,$mpage,$page_id,$goods_id,'$name','$opt1','$opt2','$opt3','$opt4','$opt5','$descr1','$descr2','$descr3','$descr4','$descr5','$rem1','$rem2','$dat__','$seq__','$readonly','$cansr' $c2)";
      $line_ = mysql_query($cmd,$link);

      addlog("$cmd","Добавим папку",mysql_error());

      //print $cmd.mysql_error();
      
      $line = mysql_query("SELECT max(id) FROM $tbl_goods WHERE name='$name'",$link);
      $s=mysql_fetch_array($line);
      $fold_id=$s[0];

    } else { // изменим папку

      $cmd="UPDATE $tbl_goods SET more1_='$more1_',more2_='$more2_',more3_='$more3_',more4_='$more4_',more5_='$more5_',more6_='$more6_',more7_='$more7_',more8_='$more8_',more9_='$more9_',more10_='$more10_',more1='$more1',more2='$more2',more3='$more3',more4='$more4',more5='$more5',more6='$more6',more7='$more7',more8='$more8',more9='$more9',more10='$more10',icon='$icon',desctop=$desctop,seq=$seq,mpage=$mpage,name='$name',opt1='$opt1',opt2='$opt2',opt3='$opt3',opt4='$opt4',opt5='$opt5',descr1='$descr1',descr2='$descr2',descr3='$descr3',descr4='$descr4',descr5='$descr5',rem1='$rem1',rem2='$rem2',dat__='$dat__',seq__='$seq__',readonly='$readonly',cansr='$cansr' $c WHERE id=$fold_id";
      $line_ = mysql_query($cmd,$link);

      addlog("$cmd","Изменим свойства папки",mysql_error());

    };
    
    header("location: ./goods.php?showhead=$showhead&pageid=2&action=reload&subpageid=3&page_id=$page_id&fold_id=$fold_id&rand=".rand());
    exit(1);

  break;

  case(9): // добавим файл

    //print "=".$pole1."=";
    //exit(1);

    if($admin==0)norules();//запрет на действия для гостя

    $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE id=$fold_id",$link);
    $s10=mysql_fetch_array($line10);
    
    $name=check_str($name);
    if($name=="")$name="noname_".$page_id."_".$fold_id."_".$good_id;
    $seq=intval(check_str($seq));

    $pole_radio=intval($pole_radio);
    
    $dat=intval(check_str($dat));
    if($dat==0){
      $dat1=intval($dat1);
      $dat2=intval($dat2);
      $dat3=intval($dat3);

      if($dat1<10)$dat1="0".$dat1;
      if($dat2<10)$dat2="0".$dat2;

      $dat=intval($dat3.$dat2.$dat1);

    };

    $readonly=intval($readonly);

    if ($page_id == 2 && $fold_id == 2 && isset($_POST['extern_main_photo']) && !empty($_POST['extern_main_photo'])) {
        $pole5 = $_POST['extern_main_photo'];
        $pole5_size = 1;
    }

    for($i=1;$i<$maxpoles;$i++){
    
      eval("\$s=intval(\$pole".$i."_size);");

      if($s>0){//это файлы



		if ($s10['pole'.$i.'_'] == 99) { // not updated fields
			unset($GLOBALS['pole' . $i]);
			unset($_POST['pole' . $i]);
		}
      
        
        eval("\$c1.=\",pole$i\";");

        eval("\$u=\$pole".$i.";");
        eval("\$u_n=\$pole".$i."_name;");
          if(isset($_POST['extern_main_photo']) && !empty($_POST['extern_main_photo']) && $i == 5) {
            $f = $_POST['extern_main_photo'];
          } else {
            $f=save_file($u_n,$s,$u,0,0,date("Ymd"),"","",0,0,0,0);
          }
        //print "-$f-";
        $c2.=",'$f'";
        eval("\$c.=\",pole$i='\".\$f.\"'\";");

        //print $c;

      }/* elseif($s10["pole".$i."_"]==4) { // select
        eval("\$c2.=\",'\".intval(check_str(\$pole".$i."__h)).\"'\";");
        eval("\$c.=\",pole$i='\".intval(check_str(\$pole".$i."__h)).\"'\";");
      }*//* elseif($s10["pole".$i."_"]==4 || $s10["pole".$i."_"]==2) { // это селекты
        
        eval("\$c1.=\",pole$i\";");
        eval("\$c2.=\",'\".check_str(\$pole".$i.").\"'\";");
        eval("\$c.=\",pole$i='\".check_str(\$pole".$i.").\"'\";");

        print $c1."<br>";
        print $c2."<br>";
        exit(1);

      }*/ elseif($s10["pole".$i."_"]<5) { // это НЕ файлы
        
        eval("\$c1.=\",pole$i\";");
        eval("\$c2.=\",'\".check_str(\$pole".$i.").\"'\";");
        eval("\$c.=\",pole$i='\".check_str(\$pole".$i.").\"'\";");

      } elseif($s10["pole".$i."_"]==5) { // радио батон 

        if($i==$pole_radio){ // этот батон выбран
          eval("\$c1.=\",pole$i\";");
          eval("\$c2.=\",'$i'\";");
          eval("\$c.=\",pole$i='$i'\";");
        } else {
          eval("\$c1.=\",pole$i\";");
          eval("\$c2.=\",''\";");
          eval("\$c.=\",pole$i=''\";");
        };

      } elseif($s10["pole".$i."_"]==6) { // дата

          eval("\$dat1=intval(\$pole".$i."d1);");
          eval("\$dat2=intval(\$pole".$i."d2);");
          eval("\$dat3=intval(\$pole".$i."d3);");
        
          if($dat1<10)$dat1="0".$dat1;
          if($dat2<10)$dat2="0".$dat2;
        
          $dat=intval($dat3.$dat2.$dat1);
        
          eval("\$c1.=\",pole$i\";");
          eval("\$c2.=\",'".$dat."'\";");
          eval("\$c.=\",pole$i='".$dat."'\";");

      } elseif($s10["pole".$i."_"]==7) { // время

          eval("\$dat1=intval(\$pole".$i."t1);");
          eval("\$dat2=intval(\$pole".$i."t2);");
          eval("\$dat3=intval(\$pole".$i."t3);");
        
          if($dat1<10)$dat1="0".$dat1;
          if($dat2<10)$dat2="0".$dat2;
          if($dat3<10)$dat3="0".$dat3;
        
          $dat2=intval($dat1.$dat2.$dat3);
        
          eval("\$c1.=\",pole$i\";");
          eval("\$c2.=\",'".$dat2."'\";");
          eval("\$c.=\",pole$i='".$dat2."'\";");

	}

    };

    //print $c;
    //exit(1);
    
    $ak_news_id = 0;
    
    if($good_id==0){ // новый файл

      $cmd="INSERT INTO $tbl_goods_ (page_id,goods_id,name,seq,dat,readonly $c1) 
            VALUES ($page_id,$fold_id,'$name',$seq,$dat,$readonly $c2)";
      $line_ = mysql_query($cmd,$link);
      $ak_news_id = mysql_insert_id($link);

      addlog("$cmd","Добавим файл",mysql_error());

      //print $cmd.mysql_error();
      //exit(1);

    } else { // изменим файл

      $cmd2="UPDATE $tbl_goods_ SET readonly=$readonly,name='$name',seq=$seq,dat=$dat $c WHERE id=$good_id";
      $line = mysql_query($cmd2,$link);
      $ak_news_id = $good_id;

      addlog("$cmd2","Изменим инфу о файле",mysql_error());

      //print $cmd2.mysql_error();
      //exit(1);

    }
	
	/// NEW IMAGE SAVE
	define('droot', realpath(dirname(__FILE__) . '/../'));
	if ($ak_news_id && $page_id == 2 && $fold_id == 2 && isset($_POST['ak_add_news_images']) && $_POST['ak_add_news_images'] == 1)
    	{
    		if (!empty($_POST['ak_news_image_action']) && isset($_POST['ak_news_image_checkbox']) && count($_POST['ak_news_image_checkbox']))
    		{
    			if ($_POST['ak_news_image_action'] == 'make_first')
    			{
    				$ak_mf = $_POST['ak_news_image_checkbox'][0];
    				
    				mysql_query(sprintf('UPDATE popcornnews_news_images SET seq=seq+1 WHERE news_id = %u'
    					,$ak_news_id
    				));
    				mysql_query(sprintf('UPDATE popcornnews_news_images SET seq = 1 WHERE news_id = %u AND id = %u'
    					,$ak_news_id
    					,$ak_mf
    				));
    			}
    			else if ($_POST['ak_news_image_action'] == 'delete')
    			{
    				foreach ($_POST['ak_news_image_checkbox'] as $ak_ch)
    				{
    					$ak_result = mysql_query(sprintf('SELECT filepath FROM popcornnews_news_images WHERE id = %u'
    						,$ak_ch
    					));
    					list($ak_filepath) = mysql_fetch_row($ak_result);
    					
    					mysql_query(sprintf('DELETE FROM popcornnews_news_images WHERE news_id = %u AND id = %u'
    						,$ak_news_id
    						,$ak_ch
    					));
    					
    					unlink(droot.$ak_filepath);
    				}
    			}
    		}
    		
    		$ddir = substr(strrev($ak_news_id),0,3);
			
		if (!is_dir(droot.'/upload/news_images/'.substr($ddir,0,1)))
		{
			mkdir(droot.'/upload/news_images/'.substr($ddir,0,1));
			chmod(droot.'/upload/news_images/'.substr($ddir,0,1),0777);
		}
		if (!is_dir(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1)))
		{
			mkdir(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1));
			chmod(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1),0777);
		}
		if (!is_dir(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1)))
		{
			mkdir(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1));
			chmod(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1),0777);
		}
		if (!is_dir(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_news_id))
		{
			mkdir(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_news_id);
			chmod(droot.'/upload/news_images/'.substr($ddir,0,1).'/'.substr($ddir,1,1).'/'.substr($ddir,2,1).'/'.$ak_news_id,0777);
		}
    		
    		$max = max(count($_FILES['ak_news_images']['tmp_name']), count($_POST['ak_news_images_name']));
    		for ($i = 0; $i < $max; $i++) {
			$ak_news_image = $_FILES['ak_news_images']['tmp_name'][$i];
			$ak_news_image_name = $_POST['ak_news_images_name'][$i];
			$ak_news_image_caption = $_POST['ak_news_images_caption'][$i];
			
    			if (empty($ak_news_image)) continue;
    			
    			$ak_result = mysql_query(sprintf('SELECT seq+1 FROM popcornnews_news_images WHERE news_id = %u ORDER BY seq DESC LIMIT 1',$ak_news_id));
			list($ak_seq) = mysql_fetch_row($ak_result);
			
			$ak_seq = (!$ak_seq) ? 1 : $ak_seq;
    			
    			$fname = md5(microtime()).'.jpg';
    			
    			$http_ak_dst = sprintf('/upload/news_images/%u/%u/%u/%u/%s',substr($ddir,0,1),substr($ddir,1,1),substr($ddir,2,1),$ak_news_id,$fname);
    			$realpath_ak_dst = droot . $http_ak_dst;
    			move_uploaded_file($ak_news_image, $realpath_ak_dst);
    			
    			exec(sprintf('%s --strip-all "%s"', JPEGOPTIM_BIN, str_replace('"', '\"', $realpath_ak_dst)));
    			
    			//upload_to_v1($realpath_ak_dst, str_replace(droot, '', dirname($realpath_ak_dst)).'/');
    			   			
    			mysql_query(sprintf('INSERT INTO popcornnews_news_images SET timestamp = "%s",news_id = %u,seq = %u,filepath = "%s",name="%s",caption="%s"'
    				,date('Y-m-d H:i:s')
    				,$ak_news_id
    				,$ak_seq
    				,$http_ak_dst
    				,$ak_news_image_name
    				,$ak_news_image_caption
    			));
    		}
	}
	/// \NEW IMAGE SAVE
	
	/// MULTI TAGS ADD
	if ($ak_news_id && $page_id == 2 && $fold_id == 2) {
		// retrive date of new
		$ak_news_date = mysql_fetch_first_column(mysql_sprintf('SELECT newsIntDate FROM popconnews_goods_ WHERE id = %u', $ak_news_id));
		
		// fetch new
		$akPersonsTags = isset($_POST['ak_persons_tags']) ? array_unique(array_map('intval', $_POST['ak_persons_tags'])) : array();
		$akEventsTags = isset($_POST['ak_events_tags']) ? array_unique(array_map('intval', $_POST['ak_events_tags'])) : array();
		
		// fetch old & deleting & update
		$akForUpdate = $akForDelete = array();
		$akTagsNew = array_merge($akPersonsTags, $akEventsTags);
		$akTagsOld = mysql_fetch_all(mysql_sprintf('SELECT * FROM popcornnews_news_tags WHERE nid = %u', $ak_news_id));
		foreach ($akTagsOld as &$akTagOld) {
			// for delete
			if (!in_array($akTagOld['tid'], $akTagsNew)) $akForDelete[] = $akTagOld['tid'];
			// already exist
			if (in_array($akTagOld['tid'], $akTagsNew)) {
				// for update
				$akForUpdate[] = $akTagOld['tid'];
				
				// already exist
				if ($akTagOld['type'] == 'events') {
					unset($akEventsTags[array_search($akTagOld['tid'], $akEventsTags)]);
				} else {
					unset($akPersonsTags[array_search($akTagOld['tid'], $akPersonsTags)]);
				}
			}
		}
		// delete
		if (count($akForDelete) > 0) {
			mysql_sprintf('DELETE FROM popcornnews_news_tags WHERE nid = %u AND tid IN (%s)', $ak_news_id, join(',', $akForDelete));
		}
		// update
		if (count($akForUpdate) > 0) {
			mysql_sprintf('UPDATE popcornnews_news_tags SET news_regtime = "%s" WHERE nid = %u AND tid IN (%s)', $ak_news_date, $ak_news_id, join(',', $akForUpdate));
		}
		
		// add new if exist
		if (count($akPersonsTags) > 0 || count($akEventsTags) > 0) {
			foreach ($akPersonsTags as &$akPersonTag) {
				if (!$akPersonTag) continue; // zero
				mysql_sprintf('INSERT INTO popcornnews_news_tags SET type = "persons", nid = %u, tid = %u, regtime = %u, news_regtime = "%s"', $ak_news_id, $akPersonTag, time(), $ak_news_date);
			}
			foreach ($akEventsTags as &$akEventTag) {
				if (!$akEventTag) continue; // zero
				mysql_sprintf('INSERT INTO popcornnews_news_tags SET type = "events", nid = %u, tid = %u, regtime = %u, news_regtime = "%s"', $ak_news_id, $akEventTag, time(), $ak_news_date);
			}
		}
	}
	/// \MULTI TAGS ADD
	/*columns save*/
	if ($ak_news_id && $page_id == 2 && $fold_id == 2) {
	    $columns = $_POST['columns'];
	    mysql_query('DELETE FROM pn_columns_news_link WHERE nid = '.$ak_news_id);

	    $sql = 'INSERT INTO pn_columns_news_link (`cid`, `nid`) VALUES ';
	    $item = array();
	    foreach ($columns as $c) {
	        $item[] = "({$c}, {$ak_news_id})";
	    }
	    $sql .= implode(',', $item);
	    mysql_query($sql);
	}
	/*------------*/
	if($page_id == 2 && $fold_id == 11 && isset($_POST['t_category'])) {
	    $sql = "UPDATE {$tbl_goods_} SET pole40='".implode(',',$_POST['t_category'])."' WHERE id={$ak_news_id}";
	    mysql_query($sql);
	}	
	/// POLL
	if ($ak_news_id && $page_id == 2 && $fold_id == 2)
    	{
		$akOptions = array_not_empty(array_unique(array_map('strip_tags', $_POST['akPollOptions'])));
		$akOldOptions = mysql_fetch_all(mysql_sprintf('SELECT * FROM popcornnews_news_polls_options WHERE nid = %u', $ak_news_id));
		
		$akDeleteOldOptions = array(); // delete old
		foreach ($akOldOptions as $akOldOption) {
			$k = array_search($akOldOption['title'], $akOptions);
			if ($k === false) {
				$akDeleteOldOptions[] = $akOldOption['id'];
			} else { // drop equil items
				unset($akOptions[$k]);
			}
		}
		// delete old
		if (!empty($akDeleteOldOptions)) {
			mysql_sprintf('DELETE FROM popcornnews_news_polls_options WHERE nid = %u AND id IN (%s)', $ak_news_id, join(',', $akDeleteOldOptions));
		}
		// add new
		if (!empty($akOptions)) {
			foreach ($akOptions as $akOption) {
				mysql_sprintf('INSERT INTO popcornnews_news_polls_options SET nid = %u, createtime = %u, title = "%s"', $ak_news_id, time(), $akOption);
			}
		}
    	}
    	/// \POLL
	
	header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=3&page_id=$page_id&fold_id=$fold_id&rand=".rand());
	exit(1);

  break;

  case(10): // удалим файл или поля у файла

    if($admin==0)norules();//запрет на действия для гостя

    $line10 = mysql_query("SELECT $file FROM $tbl_goods_ WHERE id=$good_id",$link);
    if($s=mysql_fetch_array($line10)){
      
      delete_file_pole($s[0]);
      
      $cmd2="UPDATE $tbl_goods_ SET $file='' WHERE id=$good_id";
      $line = mysql_query($cmd2,$link);

      addlog("$cmd2","Удалим вложение у файла",mysql_error());

    };

    header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=6&page_id=$page_id&fold_id=$fold_id&good_id=$good_id&rand=".rand());
    exit(1);

  break;


  case(13): // выбор селекта: (popup окно) для файла
  case(14): // выбор селекта: (popup окно) для папки



        ?>
<html>
<head>
<META Name="programmer" Content="Shilov Konstantin, mail@shyk.spb.ru">
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<LINK rel="stylesheet" href="styles/global.css" type="text/css" title="main">   
<title>Система управления</title>
<script language="JavaScript"><!--
function get_sel(){
        eval('f=document.workform.dst.value;');
        if(f!=""){
          location.href='./goods.php?rand=<? print rand(); ?>&i=<? print $i; ?>&p_id=<? print $pageid; ?>&pageid=15&f_id='+f;
        } else alert("Внимание!\n\nВы можете выбрать только папку, но не том!");
};
//--></script>
</head>
<body bgcolor="#000000" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table border="0" cellpadding="20" cellspacing="0" height="100%" width="100%" bgcolor="#F9F9F8">
  <tr>
    <form name="workform">
    <td><span class="cg"><b>выбор источника:</b></span><br>
    <select class="cf" name="dst" onChange="get_sel()" style="width: 100%"><option value="">выберите папку</option>
        <?

     print get_three_move($fold_id);

        ?>
    </select><br>
    из этой папки будут браться файлы для выпадающего списка при вводе нового файла
    </td>
    </form>
  </tr>
</table>
</body>
</html>
<?


   break;

   case(15): // 2-й шаг для выбора файлов/папок

     $line10 = mysql_query("SELECT name FROM $tbl_goods WHERE id=$f_id",$link);
     $s=mysql_fetch_array($line10);
     
     ?><script>
opener.document.workform2.pole<? print $i; ?>__h.value="<? print $f_id; ?>";
<? if($p_id==13){ ?>
opener.document.workform2.pole<? print $i; ?>.value='Файл из папки "<? print $s["name"]; ?>"';
<? } else { ?>
opener.document.workform2.pole<? print $i; ?>.value='Папка из папки "<? print $s["name"]; ?>"';
<? }; ?>
window.close();
     </script><?

   break;

   case(16): // удалим файл в корзину

//print "-$fold_id-$id-";
//exit(1);
    if($admin==0)norules();//запрет на действия для гостя

     delete_file_trash($fold_id,$id);

     header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=3&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;


   case(17): // удалим файл из корзины вааще

//print "-$fold_id-$id-";
//exit(1);
    if($admin==0)norules();//запрет на действия для гостя

     delete_file($id);
     /*
     $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=1 and id=$id",$link);
     if($s=mysql_fetch_array($line10)){

       if($s["goods_id"]==0)$s["goods_id"]=$s["goods_id_"];
       delete_file($s["goods_id"],$s[0]);

       //print "=".$s["goods_id"]."=$s[0]=";

     };
     */

     header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=3&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;

   case(18): // копируем файл

    if($admin==0)norules();//запрет на действия для гостя

     $line10 = mysql_query("SELECT page_id FROM $tbl_goods WHERE id=".intval($f_id),$link);
     $s=mysql_fetch_array($line10);

     copy_file($good_id,$copys,$page_id,$fold_id,intval($s[0]),$f_id,check_str($name));
     
     header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=3&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;

   case(19): // переносим файл

    if($admin==0)norules();//запрет на действия для гостя

     $line10 = mysql_query("SELECT page_id FROM $tbl_goods WHERE id=$f_id",$link);
     $s=mysql_fetch_array($line10);

     $cmd2="UPDATE $tbl_goods_ SET goods_id=$f_id,page_id=$s[0] WHERE goods_id=$fold_id and id=$good_id";
     $line = mysql_query($cmd2,$link);

     addlog("$cmd2","Перенесем файл",mysql_error());

//print $cmd2.mysql_error();
//exit(1);
     
     header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=3&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;
   
   case(20): // восстановим файл из корзины

    if($admin==0)norules();//запрет на действия для гостя

     $line10 = mysql_query("SELECT page_id FROM $tbl_goods WHERE id=$f_id",$link);
     $s=mysql_fetch_array($line10);

     $cmd2="UPDATE $tbl_goods_ SET goods_id=$f_id,page_id=$s[0] WHERE id=$good_id";
     $line = mysql_query($cmd2,$link);

     addlog("$cmd2","Восстановим файл из корзины",mysql_error());
     
     header("location: ./goods.php?showhead=$showhead&pageid=2&subpageid=3&page_id=1&rand=".rand());
     exit(1);

   break;
   
   case(21): // переносим папку

    if($admin==0)norules();//запрет на действия для гостя

     $p_id=0;

     if($f_id[0]=="f"){ // копирование в том
       $p_id=substr($f_id,1,strlen($f_id)-1);
       $f_id=0;
     };

     if($f_id!=$fold_id){ // перенесем
       if(check_move($fold_id,$f_id)){ // перенесем
         
         //print "-".check_move($fold_id,$f_id)."-=$fold_id=$f_id=";
         //exit(1);

         move_fold($fold_id,$f_id);

       };
//print $cmd2.mysql_error();
//exit(1);
     };

     header("location: ./goods.php?showhead=$showhead&pageid=2&action=reload&subpageid=3&page_id=$page_id&rand=".rand());
     exit(1);

   break;

   case(22): // копируем папку

    if($admin==0)norules();//запрет на действия для гостя


     $p_id=0;
     //if($f_id!=$fold_id){ // копируем

     if($f_id[0]=="f"){ // копирование в том
       $p_id=substr($f_id,1,strlen($f_id)-1);
       $f_id=0;
     };

     //print "-$fold_id-$f_id-$p_id";
     //exit(1);

     copy_fold($fold_id,$f_id,$copy,$name);
     
     //};

     header("location: ./goods.php?showhead=$showhead&pageid=2&action=reload&subpageid=3&page_id=$page_id&rand=".rand());
     exit(1);

   break;
   
   case(23): // удалим папку в корзину

    if($admin==0)norules();//запрет на действия для гостя

     move_fold_trash($fold_id,0);

     //print "123";
     //exit(1);

     header("location: ./goods.php?showhead=$showhead&pageid=2&action=reload&subpageid=3&page_id=$page_id&rand=".rand());
     exit(1);

   break;

   case(24): // очистим корзину

    if($admin==0)norules();//запрет на действия для гостя

     //move_fold_trash($fold_id,0);

     set_time_limit(0);

     $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=1",$link);
     while($s=mysql_fetch_array($line10)){

       //if($s["goods_id"]==0)$s["goods_id"]=$s["goods_id_"];
       delete_file($s[0]);

     };
     
     $cmd2="DELETE FROM $tbl_goods WHERE page_id=1";
     $line10 = mysql_query($cmd2,$link);

     addlog("$cmd2","Очистим корзину - папки",mysql_error());

     $cmd2="DELETE FROM $tbl_goods_ WHERE page_id=1";
     $line10 = mysql_query($cmd2,$link);
     
     addlog("$cmd2","Очистим корзину - файлы",mysql_error());

     header("location: ./goods.php?showhead=$showhead&pageid=2&action=reload&subpageid=3&page_id=1&rand=".rand());
     exit(1);

   break;

   case(25): // iframe выбора иконки
     
     $dir="icons";

     ?><html><head>
<script language="JavaScript"><!--
function set_file(file,i){
  self.parent.document.workform2.icon.value=file;
  self.parent.document.images["icon"].src="<? print $dir; ?>/"+file;
  if(back_id!=i){
    if(back_id!=-1)document.all["ico"+back_id].className="";
    document.all["ico"+i].className="IconActive";
    //if(document.all["ico"+i].className!="IconActive")document.all["ico"+i].className="IconActive";
    //  else document.all["ico"+i].className="";
  };
  back_id=i;
};
//--></script>
     <link rel="stylesheet" type="text/css" href="styles/global.css"></head><body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF"><div class="IconSelect"><?

     $back_id=-1;
     $i=0;
     if (is_dir($dir)) {
       if ($dh = opendir($dir)) {
           while (($file = readdir($dh)) !== false) {
               if(is_file($dir."/".$file)){
                 print '<a name=ico'.$i.' href="javascript:{set_file('."'".$file."'".','.$i.')}"';
                 if($icon==$file) {
                        print ' class="IconActive"';
                        $back_id=$i;
                 };
                 print '><img src="'.$dir.'/'.$file.'" width="32" height="32"></a>';

                 $i++;
               }
           }
           closedir($dh);
       } else exit(1);
     }

    //<a href="#"><img src="i/fbig.gif" width="32" height="32"></a>



     ?></div><?

     if($back_id!=-1){ ?>
<script language="JavaScript"><!--

back_id=<? print $back_id; ?>

//--></script>
     <?

     };

     ?></body></html><?

   break;


   case(26): // сохраним копирование свойств папки

    if($admin==0)norules();//запрет на действия для гостя
         
     $line10 = mysql_query("SELECT * FROM $tbl_goods WHERE page_id=$page_id and id=$fold_id",$link);
     $s=mysql_fetch_array($line10);
     
     /*
     print "1<pre>";
     print_r($poles);
     print "</pre>";
     print "2<pre>";
     print_r($dir);
     print "</pre>";
     
     print "<br>=$fold_id/$s[0]/".$s["descr5"]."/".$s["more1"]."=";
     */
     $c="";
     for($i=0;$i<count($poles);$i++){
       if($c!="")$c.=",";
       $c.=" $poles[$i]='".$s[$poles[$i]]."' ";
       if(substr($poles[$i],0,4)=="pole"){
          $c.=", ".$poles[$i]."_=".$s[$poles[$i]."_"]." ";
          $c.=", ".$poles[$i]."__=".$s[$poles[$i]."__"]." ";
       };

     };

     //print "<br>-$c-";


     for($i=0;$i<count($dir);$i++){

        $cmd2="UPDATE $tbl_goods SET $c WHERE id=$dir[$i]";
        $line = mysql_query($cmd2,$link);

        //print "<br>$i) ".$cmd2." - ".mysql_error();

        addlog("$cmd2","Применим свойства папки - копирование свойств",mysql_error());

        //print "<hr>$cmd2".mysql_error();
        //exit(1);

     };
     
     header("location: ./goods.php?pageid=2&subpageid=3&showhead=$showhead&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;


   case(27): // очистим папку

    if($admin==0)norules();//запрет на действия для гостя
   
     $line10 = mysql_query("SELECT * FROM $tbl_goods_ WHERE page_id=$page_id and goods_id=$fold_id",$link);
     while($s=mysql_fetch_array($line10)){

       delete_file_trash($fold_id,$s[0]);

     };
     
     header("location: ./goods.php?pageid=2&subpageid=3&showhead=$showhead&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;


   case(28): // прикрепим к папке аттач

    if($admin==0)norules();//запрет на действия для гостя
   

     $name=check_str($name);
     $type=intval($type);

     $f=save_file($userfile_name,$userfile_size,$userfile,$type,0,date("Ymd"),"$name","$name",$fold_id,0,0,0);

     //print "-$userfile_name-$userfile_size-$f-<br>";
     
     //exit(1);

     header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=5&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;


   case(29): // удалим аттач к папке

    if($admin==0)norules();//запрет на действия для гостя
   

     if($good_id==0){ // удалим аттач у папки
       
       $line10 = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id and goods_id=$fold_id",$link);
       if($s=mysql_fetch_array($line10)){
     
         delete_attach($s[0]);
     
       };

     } else {

       $line10 = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id and goods_id_=$good_id",$link);
       if($s=mysql_fetch_array($line10)){
     
         delete_attach($s[0]);
     
       };

     };

     header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=5&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;

   case(35): // удалим аттач к файлу

    if($admin==0)norules();//запрет на действия для гостя
   

     if($good_id==0){ // удалим аттач у папки
       
       $line10 = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id and goods_id=$fold_id",$link);
       if($s=mysql_fetch_array($line10)){
     
         delete_attach($s[0]);
     
       };

     } else {

       $line10 = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id and goods_id_=$good_id",$link);
       if($s=mysql_fetch_array($line10)){
     
         delete_attach($s[0]);
     
       };

     };
                                          //goods.php?showhead=0&pageid=2&subpageid=6&r=0.6010341756170148&good_id=410352&page_id=7&fold_id=1082
     header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=6&page_id=$page_id&fold_id=$fold_id&good_id=".$good_id."&rand=".rand());
     exit(1);

   break;


   case(30): // отредактируем вложение

    if($admin==0)norules();//запрет на действия для гостя
   

     $name=check_str($name);
     $type=intval($type);

     if($userfile_size>0){
       
       delete_attach($file_id);
       $f=save_file($userfile_name,$userfile_size,$userfile,$type,0,date("Ymd"),"$name","$name",$fold_id,0,0,0);
     
     } else {

       $cmd2="UPDATE $tbl_pix SET name='$name',type=$type WHERE id=$file_id";
       $line = mysql_query($cmd2,$link);

       addlog("$cmd2","Отредактируем вложение",mysql_error());

     };

     if($good_id==0)header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=5&page_id=$page_id&fold_id=$fold_id&rand=".rand());
       else header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=6&good_id=$good_id&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);


   break;


   case(31): // зальем аттач к файлу

    if($admin==0)norules();//запрет на действия для гостя
   

     $name=check_str($name);
     $type=intval($type);

     $f=save_file($userfile_name,$userfile_size,$userfile,$type,0,date("Ymd"),"$name","$name",0,$good_id,0,0);

     //print "-$userfile_name-$userfile_size-$f-<br>";
     //print "-$page_id-$fold_id-$good_id-";
     
     //exit(1);

     header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=6&good_id=$good_id&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);


   break;

   
   case(32): // удалим аттач к файлу

    if($admin==0)norules();//запрет на действия для гостя
   

     $line10 = mysql_query("SELECT * FROM $tbl_pix WHERE id=$file_id and goods_id_=$good_id",$link);
     if($s=mysql_fetch_array($line10)){

       delete_attach($s[0]);

     };

     header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=6&good_id=$good_id&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);

   break;


   case(33): // восстановление базы

    if($admin<3)norules();//запрет на действия для гостя

    $showhead=-1;

    set_time_limit(0);
     
     switch($subpageid){

       case(0):
       default: // выбор действия


        include "inc/header.php";


?><div id="MainBlock">
<script>
  function check_name(){
        if(document.forms.workform2.name.value==""){
                alert("Необходимо задать уникальное название точки восстановления!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

</script>

  <div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>
        <td class="NavBlockAddress"><a href="goods.php?pageid=33&rand=<? print rand(); ?>"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">Восстановление сайта</a></td>
      </tr>
    </table>
  </div>
  <div class="Files">
    <h1>Восстановление сайта</h1>
    <p>Создание точек восстановления позволит Вам самостоятельно создавать резервные копии сайта, а также восстановить сайт в случае сбоя. </p>

    <table cellspacing="1" class="TableFiles">
      <tr>
        <td class="TFHeader">Выберите сохраненный сайт для его восстановления</td>
        <td class="TFHeader">Дата сохранения сайта</td>
        <td class="TFHeader">Размер сохранения сайта (Mb)</td>
        <td class="TFHeader">&nbsp;</td>
      </tr>
      <?


$dir="./backup/";
if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if(is_file($dir."/".$file)){
             
             //print "Точка восстановления: <a href='goods.php?&pageid=33&subpageid=4&file=$file&rand=".rand()."'>".$file."</a><br><br>";

      list($name,$dat)=explode(".",$file);

      ?><tr>
        <td><a href='javascript:{if(window.confirm("Восстановить сайт из точки восстановления? Внимание, ее восстановление УНИЧТОЖИТ текущее состояние!"))location.href="goods.php?&pageid=33&subpageid=4&file=<? print $file; ?>&rand=<? print rand(); ?>";};'><? print $name; ?></a></td>
        <td><? print dat_($dat," "); ?></td>
        <td><? print filesize($dir."/".$file)/1000000; ?></td>
        <td class="TFaction"><a href='javascript:{if(window.confirm("Удалить точку восстановления? Внимание, ее восстановление будет невозможно!"))location.href="goods.php?&pageid=33&subpageid=6&file=<? print $file; ?>&rand=<? print rand(); ?>"}'><img src="i/del.gif" alt="удалить" width="15" height="15" hspace="4"></a></td>
      </tr><?

           };
       };
   };
};


      ?>
      

    </table>


    <form name="workform2" method="POST" action="goods.php?rnd=<? print rand(); ?>" ENCTYPE="multipart/form-data" onSubmit="return check_name();">
<input type="hidden" name="pageid" value="33">
<input type="hidden" name="subpageid" value="2">

    <h1>Создать точку восстановления</h1>
    <p>Введите название точки восстановления (Только анг. буквы, цифры и знак подчеркивания "_"). Внимание. Убедитесь, что на сервере есть достаточно свободного дискового пространства для создания точки восстановления. О размере общего и свободного дискового пространства Вы можете узнать у провайдера, обеспечивающего размещение сайта.</p>
    
    <div class="Ftext"><input type="text" name="name" class="cf" value=""></div>
    <div class="FSubmit"><input type="submit" size="10" value="Создать"></div>

    </form>


  </div>
 
</div>

<?


        /*
    
        ?><div id="MainBlock"><div class="FileBlock"> 
        <h1>Создание точку восстановления</h1><a href="./goods.php?&pageid=33&subpageid=1&rand=<? print rand(); ?>">создать точку восстановления</a>
        
        <h1>Восстановить базу</h1><a href="./goods.php?&pageid=33&subpageid=3&rand=<? print rand(); ?>">Восстановить базу</a>

        <h1>Удалить базу</h1><a href="./goods.php?&pageid=33&subpageid=5&rand=<? print rand(); ?>">удалить одну из точек восстановления</a>

        </div></div></body></html><?

        */

        ?></div></body></html><?


       break;
      /*
       case(1): // ввод имени для точки восстановления


        include "inc/header.php";

        ?><div id="MainBlock"><div class="FileBlock"> <h1>Создание точек восстановления</h1>
<script>
  function check_name(){
        if(document.forms.workform2.name.value==""){
                alert("Необходимо задать уникальное название точки восстановления!");
                document.forms.workform2.name.focus();
                return false;
        };
        return true;
  };

</script>
<form name="workform2" method="POST" action="goods.php?rnd=<? print rand(); ?>" ENCTYPE="multipart/form-data" onSubmit="return check_name();">
<input type="hidden" name="pageid" value="33">
<input type="hidden" name="subpageid" value="2">
Введите название точки восстановления (Только анг. буквы, цифры и знак подчеркивания "_"):<br>
<input type="text" class=cf name="name"><br>
<input type="submit" value="создать">
        </form>
        <hr><a href="./goods.php?&pageid=33&rand=<? print rand(); ?>">в начало</a>
        </div></div></body></html><?

       break;
     */
       
       case(2): // создание точки восстановления

         $dat=date("Ymd");
         $name=check_str($name);

         include "inc/header.php";


?>
<script>
flag=true;
function scr(){

        if(flag)scroll(1,10000000);

};

setInterval("scr()",100);

</script>
<div id="MainBlock">

  <div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>
        <td class="NavBlockAddress"><a href="goods.php?pageid=33&rand=<? print rand(); ?>"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">Восстановление сайта</a> / создание точки восстановления</td>
      </tr>
    </table>
  </div>
  <div class="Files">
    <h1>Идет создание точки восстановления</h1>

         Пожалуйста, подождите, не отключайтесь от интернета и не производите действий в системе управления. <br>
         Это может занять несколько минут....
         <br><br>
         <?

    if($name!=""){

      
      if(!file_exists("./backup/$name.$dat")){

        $backup_cmd="";

        print "<h3>Обработка файлов</h3>";

        $cmd="DELETE FROM $tbl_backup";
        $line = mysql_query($cmd,$link);

        $iii=0;

$dir="../upload/";
if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if(is_file($dir."/".$file)){
             
             print "Обрабатывается файл: ".$file."<br>";

             $fp = fopen ($dir."/".$file,"r");
             $txt = addslashes(fread( $fp, filesize( $dir."/".$file )  ));
             //$txt = fread( $fp, filesize( $dir."/".$file )  );
             fclose($fp);

             $backup_cmd.="INSERT INTO $tbl_backup (pix,diskname,dat,backupname) VALUES (".'0x'.bin2hex($txt).",'$file',$dat,'$name');
";

           $iii++;
           if($iii==100){
             if($fp=fopen("./backup/$name.$dat",'a')){
               fputs ($fp,$backup_cmd);
               fclose($fp);
             } else print "<h1>Внимание! Нехватает прав на запись в папку или дисковый лимит исчерпан!</h1>";
             $iii=0;
             $backup_cmd="";
           };
             //$line_ = mysql_query($cmd,$link);

//             print $cmd.mysql_error();
//             exit(1);

           }
       }
       closedir($dh);
   } else exit(1);
}
                /*
                $cmd="SELECT * FROM $tbl_backup";
                $line = mysql_query($cmd,$link);
                while($s=mysql_fetch_array($line)){
                  $backup_cmd.="INSERT INTO $tbl_backup (pix,diskname,dat,backupname) VALUES (".'0x'.bin2hex($s["pix"]).",'".$s["diskname"]."',".$s["dat"].",'".$s["backupname"]."');
";
                };

                $cmd="DELETE FROM $tbl_backup";
                $line = mysql_query($cmd,$link);
                */

             print "<br><br>";

             $cmd="SHOW TABLES";
             $line_ = mysql_query($cmd,$link);
             while($s=mysql_fetch_array($line_)){
               
               if(substr($s[0],0,strlen($project))==$project){
                 print "<h3>Обрабатывается таблица: $s[0]</h3>";
                 
                 unset($ss_);
                 $c="";
                 $ii=0;
                 $cmd="SHOW COLUMNS FROM $s[0]";
                 $line = mysql_query($cmd,$link);
                 while($ss=mysql_fetch_array($line)){
                   if($c!="")$c.=",";
                   $c.="$ss[0]";
                   $ss_[$ii]=$ss;
                   $ii++;
                 };

                 
                 $backup_cmd.="DELETE FROM $s[0];
";


                 $cmd="SELECT * FROM $s[0]";
                 $line = mysql_query($cmd,$link);
                 while($sss=mysql_fetch_array($line)){

                   $c_="";
                   for($i=0;$i<$ii;$i++){
                        if($c_!="")$c_.=",";
                        /*
                        if($ss_[$i][1]=="text" || $ss_[$i][1]=="blob" || $ss_[$i][1]=="longblob") $c_.="'$sss[$i]'";
                          else $c_.=intval($sss[$i]);
                        */

                        if($ss_[$i][1]=="text" || $ss_[$i][1]=="blob" || $ss_[$i][1]=="longblob") {
                          if($sss[$i]!="")$c_.='0x'.bin2hex($sss[$i]);
                           else $c_.="''";
                        } else $c_.=intval($sss[$i]);
                        
                   };

                   $backup_cmd.="INSERT INTO $s[0] ($c) VALUES ($c_);
";

                //print $backup_cmd;
                //exit(1);

                   print "      .";


           $iii++;
           if($iii>=100){
             if($fp=fopen("./backup/$name.$dat",'a')){
               fputs ($fp,$backup_cmd);
               fclose($fp);
             } else print "<h1>Внимание! Нехватает прав на запись в папку или дисковый лимит исчерпан!</h1>";
             $iii=0;
             $backup_cmd="";
           };

                   //print "<hr>"."INSERT INTO $s[0] ($c) VALUES ($c_)";

                 };


               };


               //$iii++;
               //if($iii==100){
                 if($fp=fopen("./backup/$name.$dat",'a')){
                   fputs ($fp,$backup_cmd);
                   fclose($fp);
                 } else print "<h1>Внимание! Нехватает прав на запись в папку или дисковый лимит исчерпан!</h1>";
                 //$iii=0;
                 $backup_cmd="";
               //};



             };
             
             
             if($fp=fopen("./backup/$name.$dat",'a')){
               fputs ($fp,$backup_cmd);
               fclose($fp);
             } else print "<h1>Внимание! Нехватает прав на запись в папку или дисковый лимит исчерпан!</h1>";
             
             /*
             $cmd="SHOW CREATE TABLE $tbl_backup";
             $line_ = mysql_query($cmd,$link);
             $s=mysql_fetch_array($line10);
             */

             /*
             $cmd="SHOW CREATE TABLE $tbl_backup";
             $line_ = mysql_query($cmd,$link);
             $s=mysql_fetch_array($line10);

             $backup_cmd=$s[0];

             $cmd="SELECT * FROM $tbl_backup WHERE dat=$dat and name='$name'";
             $line_ = mysql_query($cmd,$link);
             while($s=mysql_fetch_array($line10)){
               
             };
             */

             print "<hr><h1>Создание точки восстановления завешено.</h1>";

             print '<script>
setTimeout("scroll(1,10000000)",500);
flag=false;
</script>';



        } else print "<h1>Внимание! Точка восстановления с таким именем уже существует!</h1><a href='javascript:{history.back();}'>назад</a>";
      } else print "<h1>Необходимо ввести имя точки восстановления!</h1>";

         
         ?>
         <!--
         <hr><a href="./goods.php?&pageid=33&rand=<? print rand(); ?>">в начало</a>
         -->
         </div></div></body></html><?


       break;

     /*
       case(3): // выбор точки восстановления


        include "inc/header.php";

        ?><div id="MainBlock"><div class="FileBlock"> <h1>Выбор точки восстановления</h1>

<?

$dir="./backup/";
if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if(is_file($dir."/".$file)){
             
             print "Точка восстановления: <a href='goods.php?&pageid=33&subpageid=4&file=$file&rand=".rand()."'>".$file."</a><br><br>";

           };
       };
   };
};

?>
        <hr><a href="./goods.php?&pageid=33&rand=<? print rand(); ?>">в начало</a>
        </div></div></body></html><?

       break;
     */

       case(4): // восстановим из бакапа


        include "inc/header.php";

?><div id="MainBlock">

  <div class="NavBlock">
    <table cellspacing="0" width="100%">
      <tr>
        <td class="NavBlockAddress"><a href="goods.php?pageid=33&rand=<? print rand(); ?>"><img src="i/fsmall.gif" width="15" height="13" hspace="4" style="margin-top:2px;">Восстановление сайта</a> / восстановление данных</td>
      </tr>
    </table>
  </div>
  <div class="Files">
    <h1>Идет процесс восстановления</h1>
<script>
flag=true;
function scr(){

        if(flag)scroll(1,10000000);

};

setInterval("scr()",100);

</script>

<?
/*
        ?><div id="MainBlock"><div class="FileBlock"> <h1>Идет процесс восстановления</h1>

<?
*/

        $dir="./backup/";

      if($fp = fopen ($dir."/".$file,"r")){
        
        $cmd="DELETE FROM $tbl_backup";
        $line = mysql_query($cmd,$link);

        //$txt = stripslashes(fread( $fp, filesize( $dir."/".$file )  ));
        //$txt = fread( $fp, filesize( $dir."/".$file )  );
        $cmd="";
        while(!feof($fp)){
          $cmd=fgets($fp);
          $line = mysql_query($cmd."",$link);
          if(mysql_error()!="")print "<hr>".$cmd.mysql_error()."<br>";
          //      exit(1);
          //};
          /*
          if(mysql_error()==""){
                $cmd="";
                print "   .";
          } else print "   ,";
          */
        };

        fclose($fp);
        /*
        $cmd="SHOW TABLES";
        $line_ = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line_)){
          
          if(substr($s[0],0,strlen($project))==$project){

            $cmd="DELETE FROM $s[0]";
            $line = mysql_query($cmd,$link);

            //print $cmd."<br>";

            print "1.".mysql_error()."<br>";

          };

        };
        */
        //$txt=eregi_replace(");INSERT INTO ","",$txt);
        
        //$cmd=stripslashes($txt);

        ///$cmd="DELETE FROM $tbl_backup";
        ///$line = mysql_query($cmd,$link);

        //print "2.".mysql_error()."<br>";
/*
        $cmd=$txt;
        $line = mysql_query($cmd,$link);
        print "2.".mysql_error()."<br>";
exit(1);
*/
        /* ///
        $m=explode("
",$txt);

        //print "=".count($m)."=<br>";
        
        for($i=0;$i<count($m);$i++){
          $line = mysql_query($m[$i]."",$link);
          //print "3.".$m[$i]."<b>".mysql_error()."</b><hr><br>";
          print "       .";
        };
        //print "3.".mysql_error()."<br>";
        ///
        */

        $cmd="SELECT * FROM $tbl_backup";
        $line = mysql_query($cmd,$link);
        while($s=mysql_fetch_array($line)){

             if($fp=fopen("../upload/".$s["diskname"],'w')){
               fputs ($fp,stripslashes($s["pix"]));
               fclose($fp);
             } else print "<h1>Внимание! Нехватает прав на запись в папку или дисковый лимит исчерпан!</h1>";



        };


        $cmd="DELETE FROM $tbl_backup";
        $line = mysql_query($cmd,$link);


        print "<hr><h1>Процесс восстановления завешен.</h1>";

             print '<script>
setTimeout("scroll(1,10000000)",500);
</script>';

      
      } else print "<h1>ошибка чтения дампа базы!</h1>";



?>      <!--
        <hr><a href="./goods.php?&pageid=33&rand=<? print rand(); ?>">в начало</a>
        -->
<script>
setTimeout("scroll(1,10000000)",500);
flag=false;
</script>
        </div></div></body></html><?

       break;

       /*
       case(5): // выбор точки восстановления для восстановления


        include "inc/header.php";

        ?><div id="MainBlock"><div class="FileBlock"> <h1>Удаление точки восстановления</h1>

<?

$dir="./backup/";
if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if(is_file($dir."/".$file)){
             
             ?>Точка восстановления: <a href='javascript:{if(window.confirm("Удалить точку восстановления? Внимание, ее восстановление будет невозможно!"))location.href="goods.php?&pageid=33&subpageid=6&file=<? print $file; ?>&rand=<? print rand(); ?>"}'>"<? print $file; ?>" - удалить</a><br><br><?

           };
       };
   };
};
?>
        <hr><a href="./goods.php?&pageid=33&rand=<? print rand(); ?>">в начало</a>
        </div></div></body></html><?

       break;
       */


       case(6): // удаление точки восстановления


         @unlink("./backup/$file");

         header("location: ./goods.php?pageid=33&rand=".rand());
         exit(1);


       break;


     };


   break;


   case(34): // установим доступ на папку

//print "123";   
//exit(1);

     if($admin<3)norules();//запрет на действия для гостя
        

     $cmd="DELETE FROM $tbl_gus WHERE fold_id=$fold_id and page_id=$page_id";
     $line_ = mysql_query($cmd,$link);

     for($i=0;$i<count($ids);$i++){
      
        $cmd="INSERT INTO $tbl_gus (access,user_id,fold_id,page_id) VALUES (1,$ids[$i],$fold_id,$page_id)";
        $line_ = mysql_query($cmd,$link);

        addlog("$cmd","Установление прав доступа на папку",mysql_error());

     };


     header("location: ./goods.php?pageid=2&showhead=$showhead&subpageid=20&page_id=$page_id&fold_id=$fold_id&rand=".rand());
     exit(1);
   
   break;


};


?>