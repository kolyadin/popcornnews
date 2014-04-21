<?php

include "inc/connect.php";

// общее
$text=""; // текст и в африке текст
$maket="title.php"; // макет по умолчанию
$roothead=""; // Заголовок меню
$title="попкорнnews";
$clouds=""; // облака справа
$clouds2="";
$right_arch=""; // блок со ссылками на архив новостей

if (isset($_POST['im'])){
  $acts = $_POST['im']; // не модерированные фотки
  $location = 'moder_images.php';
  $ratio = 2;
  $moderated = 1;
}elseif (isset($_POST['aim'])){
  $acts = $_POST['aim']; // отмодерированные фотки
  $location = 'already_moder_images.php';
  $ratio = -2;
  $moderated = 0;
}else die();

$del_ids=$mod_ids=array();
foreach ($acts as $id => $action)
{
 if ($action==1 || $action==0) $mod_ids[]=$id; // разрешение запрещение
 elseif ($action==2) $del_ids[]=$id; // удаление
}

if (!empty($del_ids))
{
	$q="SELECT filename FROM popkorn_user_pix WHERE id IN (".join($del_ids,',').")";
	$result=mysql_query($q,$link);
	$dir=$_SERVER['DOCUMENT_ROOT'].'/upload';
	$o_dir=new vpa_del_file($dir);
	while($row=mysql_fetch_array($result,MYSQL_ASSOC))
	{
		$rem=$o_dir->del_by_mask($row['filename']);
	}
	mysql_query('DELETE FROM popkorn_user_pix WHERE id IN ('.join($del_ids,',').')',$link);
}
if (!empty($mod_ids))
{
    $rating=$flag=$user_old=0;
    $q="SELECT pix.uid, user.nick, user.sex, user.rating FROM popkorn_user_pix pix left join popkorn_users user on user.id=pix.uid WHERE pix.id IN (".join($mod_ids,',').") order by pix.uid";
    $result=mysql_query($q,$link);
    while($row=mysql_fetch_array($result,MYSQL_ASSOC))
    {
      if($user_old!=$row['uid']){
	$user_old=$row['uid'];
	$rating=$row['rating'];
	$flag=0;
      }
      $rating+=2;
      if($row['rating']<100&&$rating>=100&&$flag==0){
	raiting_check(array('id'=>$row['uid'],'nick'=>$row['nick'],'sex'=>$row['sex']));
	$flag=1;
      }
      $q='UPDATE popkorn_users SET rating=rating+' . $ratio . ' WHERE id=' . $row['uid'];
      mysql_query($q,$link);
    }
    mysql_query('UPDATE popkorn_user_pix SET moderated=' . $moderated . ' WHERE id IN ('.join($mod_ids,',').')',$link);
}
header ('Location: ' . $location);

function raiting_check($user){
// отсылка пользователю сообщения что у него 100 и он может чтото делать
    global $link;

    $msg=($user['sex']!=''?($user['sex']==1?'Уважаемый ':'Уважаемая '):'').$user['nick'].'! Ваш рейтинг достиг 100 баллов, и теперь Вы можете сами создавать темы в обсуждениях, писать факты о звездах и голосовать за комментарии.';
    $admin=57;
    $cmd="insert into popkorn_user_msgs (aid,uid,name,content,cdate,private,pid,del_aid) values ('".$admin."','".$user['id']."','".substr($msg,0,64)."','".$msg."','".time()."',1,0,1)";
    mysql_query ($cmd,$link);
}


$o_dir->close();

class vpa_del_file {
	var $path;
	var $mask;
	var $res;

	function vpa_del_file($path)
	{
		$this->path=$path;
		if (file_exists($this->path) && is_dir($this->path))
		{
			$this->res=dir($this->path);
		}
	}

	function del_by_mask($mask)
	{
		$this->res->rewind();
		$i=0;
		while (false !== ($entry = $this->res->read()))
		{
			if (strpos($entry,$mask)!==false)
			{
				$f=$this->path.'/'.$entry;
				@unlink($f);
				$i++;
			}
		}
		return $i;
	}

	function close()
	{
		$this->res->close();
	}
}
?>
