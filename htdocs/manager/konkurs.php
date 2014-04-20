<?php
// файл выставление за конкурсы в темах наград
include "inc/connect.php";
$text=$content='';
$id=intval($id);
$action=intval($action);
$page=intval($page);
$page=($page>1?$page:($page!=-1?1:-1));

$WHO=72408; // тег угадай кто

$mpage=50;
$tbl_comments="popconnews_comments";
$tbl_goods_="popconnews_goods_";
$tbl_winners="popconnews_winners";

function my_pages($cnt,$url="/",$sufurl=''){
// ПрАвильная функция формирования списка страниц
global $page;
$text='';
$cnt=ceil($cnt);
$page_show=20;
if($cnt<=$page_show){
   for ($i=1;$i<=$cnt;$i++)
        $text.='<div class="FilePage'.($page!=$i?'':'Active').'"><a href="'.$url.$i.$sufurl.'">'.$i.'</a></div>
        ';
}
elseif($page<=(($page_show/2)+1)){
   for ($i=1;$i<=$page_show;$i++)
        $text.='<div class="FilePage'.($page!=$i?'':'Active').'"><a href="'.$url.$i.$sufurl.'">'.$i.'</a></div>
        ';
   $text.='<div class="FilePage"><a href="'.$url.$i.$sufurl.'">...</a></div>';
   $text.=($i<$cnt)?'<div class="FilePage"><a href="'.$url.$cnt.$sufurl.'">&raquo;</a></div>':'';
}
elseif(($cnt-($page_show/2))<=$page){
   $text.=(($page-($page_show/2))>1)?'<div class="FilePage"><a href="'.$url."1".$sufurl.'">&laquo;</a></div>':'';
   $text.='<div class="FilePage"><a href="'.$url.($cnt-$page_show).$sufurl.'">...</a></div>
   ';
   for ($i=($cnt-$page_show);$i<$cnt;$i++)
        $text.='<div class="FilePage'.($page!=($i+1)?' class="active"':'').'"><a href="'.$url.($i+1).$sufurl.'">'.($i+1).'</a></div>
        ';
}
else{
   $text.=(($page-($page_show/2)-1)>1)?'<div class="FilePage"><a href="'.$url."1".$sufurl.'">&laquo;</a></div>':'';
   $text.='<div class="FilePage"><a href="'.$url.($page-($page_show/2)-1).$sufurl.'">...</a></div>
   ';
   for ($i=($page-($page_show/2));$i<=($page+($page_show/2));$i++)
        $text.='<div class="FilePage'.($page!=$i?'':'Active').'"><a href="'.$url.$i.$sufurl.'">'.$i.'</a></div>
        ';
   $text.='<div class="FilePage"><a href="'.$url.($page+($page_show/2)+1).$sufurl.'">...</a></div>
   ';
   $text.=($i<$cnt)?'<div class="FilePage"><a href="'.$url.$cnt.$sufurl.'">&raquo;</a></div>':'';
}
return '<div class="FilesPages"><div class="FilePageDescr">страницы</div>'.$text.'<div class="FilePage'.($page==-1?'Active':'').'"><a href="'.$url.'-1'.$sufurl.'">все</a></div></div>';
}

function r_time($dat, $what,$str=0){
// возвращает дату в нормальном формате в зависимости от  формата what
// если нужно чтоб месяйц выводился в буквеном формате тогда в $what должна быть "M" и если русская то $str 1 или 2
$data='';
$arr=array(
array("/Jan/","/Feb/","/Mar/","/Apr/","/May/","/Jun/","/Jul/","/Aug/","/Sep/","/Oct/","/Nov/","/Dec/"),
array("январь","февраль","март","апрель","май","июнь","июль","август","сентябрь","октябрь","ноябрь","декабрь"),
array("января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря")
);
$dat=preg_replace("/[^\d]/","",$dat);

$l=strlen($dat);
for($i=$l;$i<14;$i++)
$dat.=0;
$data=date($what, mktime(substr($dat,8,2), substr($dat,10,2), substr($dat,12,2), substr($dat,4,2), substr($dat,6,2), substr($dat,0,4)));

if($str!=0)
$data=preg_replace($arr[0],$arr[$str],$data);

return $data;
}

function TableFilesNavigation($content){

 return '<div class="FilesNavigation"><table cellspacing="0" width="100%" class="FilesNavigationTable"><tr><td valign="bottom">'.$content.'</td></tr></table></div>';
}

function TableFiles($body,$header='',$footer=''){
	$text=$header_='';
	foreach($header as $head)
	    $header_.='<td class="TFHeader">'.$head.'</td>';
	$text='<table cellspacing="1" class="TableFiles">
	'.($header_!=''?'<tr>'.$header_.'</tr>':'').'
	'.$body.'
	'.($footer!=''?$footer:'').'
	</table>';
	return $text;
}

switch($action){
	default:
	case 0:
	    $cmd="select * from $tbl_goods_ where goods_id=2 and page_id=2 and (pole27=$WHO or pole28=$WHO or pole29=$WHO)";
	    $line=mysql_query ($cmd,$link);
	    if($cnt=mysql_num_rows($line)){
            $limit=($page==-1?'':'limit '.($page>1?(($page-1)*$mpage).",$mpage":$mpage));
	        $cmd="select a.*, b.winner from $tbl_goods_ a left join (select topic_id, count(id) winner from $tbl_winners where 1 group by topic_id )b on b.topic_id=a.id where goods_id=2 and page_id=2 and (pole27=$WHO or pole28=$WHO or pole29=$WHO) order by id desc ".$limit;
	        $line=mysql_query ($cmd,$link);
            $i=0;
	        while($s=mysql_fetch_assoc($line)){
                $i++;
	            $content.='<tr'.($i%2==0?'':' class="row2"').'>
	            	<td>'.$s["id"].'</td>
	            	<td><a href="?action=1&id='.$s["id"].'">'.$s["name"].'</a> '.strip_tags($s["pole1"]).'</td>
	            	<td>'.$s["pole3"].'</td>
	            	<td>'.$s["pole16"].'</td>
	            	<td>'.$s["winner"].'</td>
	            </tr>
	            ';
	        }
	        if($cnt>$mpage){
               $text=TableFilesNavigation(my_pages(ceil($cnt/$mpage),"?page="));
            }
            $text.=TableFiles($content, array('ID', 'Название', 'Дата новости', 'Комментариев', 'Победителей'));
	    }


	break;

	case 1:
        $cmd="select * from $tbl_goods_ where id='$id' and goods_id=2 and page_id=2";
        $line=mysql_query ($cmd,$link);

        if($s=mysql_fetch_assoc($line)){
        	$text.='<div id="MainBlock">
	          <div class="NavBlock">
	            <table cellspacing="0" width="100%">
	              <tr><td valign="top" class="NavBlockAddress"><a href="?">Список конкурсов</a> / Ответы по конкурсу "'.$s["name"].'"</td></tr>
	            </table>
	          </div>
	        </div>';
	        $text.='&nbsp;<b>'.$s["pole1"].'</b>';
		  
		  $cmd = sprintf(
			    'SELECT a.*, b.id winner, user.city city FROM %s a ' .
			    'LEFT JOIN (SELECT * FROM %s WHERE topic_id = %d) b ON b.uid = a.pole8 ' .
			    'LEFT JOIN popkorn_users user ON a.pole8 = user.id ' .
			    'WHERE a.pole5 = %d ' .
			    'ORDER by a.id',
			    $tbl_comments, $tbl_winners, $id, $id
		  );

//		  $cmd="select a.*, b.id winner from $tbl_comments a left join (select * from $tbl_winners where topic_id='$id') b on b.uid=a.pole8 where a.pole5='$id' order by a.id ";
	        $line=mysql_query ($cmd,$link);
		  $i = 0;
	        while($s=mysql_fetch_assoc($line)){
	            $i++;
	            $content.='<tr'.($i%2==0?'':' class="row2"').'>
	                <td>'.$s["id"].'</td>
	                <td>'.$s["name"].'</td>
	                <td>'.$s["city"].'</td>
	                <td>'.$s["pole3"].'</td>
	                <td>'.$s["pole4"].'</td>
	                <td>'.$s["pole1"].'</td>
	                <td>'.$s["pole10"].'</td>
	                <td><input type="checkbox" name="win[]" value="'.$s["pole8"].'"'.($s["winner"]==''?'':' checked').'></td>
	                <td><input type="checkbox" name="nowin[]" value="'.$s["pole8"].'"'.($s["winner"]!=''?'':' disabled').'></td>
	            </tr>
	            ';
	        }
	        $text.='<form action="?action=2&id='.$id.'" name="worform" method="post">';
            $text.=TableFiles($content, array('ID', 'Имя', 'Город', 'Комментарии', 'E-mail', 'Дата камента', 'Редактировалось', 'Наградить', 'Отменить'),'<tr><td colspan="6" align="right"><input type="reset" value="Отменить изменения"></td><td colspan="2"><input type="submit" value="Выполнить"></td></tr>');
	        $text.='</form>';
	    }
	    else{
            header("location: ?");
            exit;
        }

	break;

	case 2:
        foreach($_POST["win"] as $key=>$value){
            $value=intval($value);
            $cmd="select * from $tbl_winners where topic_id='$id' and uid='".$value."'";
            $line=mysql_query ($cmd,$link);
            if(!$s=mysql_fetch_assoc($line)){
                $cmd="insert into $tbl_winners (topic_id,uid,cdate) values ('$id','$value','".time()."')";
                mysql_query ($cmd,$link);
            }
        }
        foreach($_POST["nowin"] as $key=>$value){
            $value=intval($value);
            $cmd="select * from $tbl_winners where topic_id='$id' and uid='".$value."'";
            $line=mysql_query ($cmd,$link);
            if($s=mysql_fetch_assoc($line)){
                $cmd="delete from $tbl_winners where topic_id='$id' and uid='$value'";
                mysql_query ($cmd,$link);
            }
        }
        header("location: ?action=1&id=$id");
        exit;


	break;

	case 3:

	break;

	case 4:

	break;
}


?>
<html>
<head>
<title>Система управления сайтом "TRAFFIC"</title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<meta Name="author" Content="Shilov Konstantin, sky@traffic.spb.ru">
<meta NAME="description" CONTENT="">
<meta NAME="keywords" CONTENT=''>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<LINK rel="stylesheet" type="text/css" href="styles/global.css">

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


.TableFiles tr.row2 td {background-color:#E0F9FF;}
--></style>

</head>
<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF">
<?=$text;?>
</body>
</html>