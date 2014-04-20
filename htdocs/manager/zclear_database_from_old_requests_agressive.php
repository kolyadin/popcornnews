<?php
/**
 * Ќ≈ ”ƒјЋя“№!!!!!!!111
 * „удо-мега-юдо скрипт, который каждые п€ть минут запускаетс€ из крона, 
 * коннектитс€ к базе данных, смотрит процесслист и, если вдруг видит, 
 * что какой-то запрос висит больше 4 минут (240 секунд) кил€ет его, тем
 * самым снижа€ веро€тность забивани€ базы повисшими запросами.
 */
function SendMail($mail,$title,$msg,$from,$from_mail){
	$mail='
	<'.$mail.'>';
	$headers="From: =?windows-1251?B?".base64_encode($from)."?= <".$from_mail.">\n";
	$headers.='MIME-Version: 1.0'."\n";
	$headers.='Content-Type: text/html; charset="windows-1251"'."\n";
	$headers.='Content-Transfer-Encoding: 8bit'."\n\n";
	$title="=?windows-1251?B?".base64_encode($title)."?=";
	return  mail($mail,$title,$msg,$headers);
}




$link=mysql_connect(':/tmp/mysql-kino.sock','root','nhfaxbr');
if(!$link){
	$filename="/data/sites/popcornnews.ru/htdocs/manager/zclear_database.txt";
	$text=file_get_contents($filename);
	$text=intval($text);
	if($text==10){ //это если в дес€тый раз все плохо...
		SendMail("shyk@nwgsm.ru,azat@traf.spb.ru","245_popcorn_alarm","alarm on ".date("Y-m-d H:i:s")."\n\n".mysql_error(),"popcorn_alarm","alarm@popcornnews.ru");
	}
	echo 'error';
	$text++;
	if (is_writable($filename)){
		if (!$handle = fopen($filename, 'w')) {
			exit;
		}
		if (fwrite($handle, $text) === FALSE) {
			exit;
		}
		fclose($handle);
	}
}else{
	$filename="/data/sites/popcornnews.ru/htdocs/manager/zclear_database.txt";
	$text=1;
	if (is_writable($filename)){
		if (!$handle = fopen($filename, 'w')) {
			exit;
		}
		if (fwrite($handle, $text) === FALSE) {
			exit;
		}
		fclose($handle);
	}
	$q=mysql_query("SHOW PROCESSLIST");
	while($s=mysql_fetch_assoc($q)){
		if($s['Time']>30){
			$sql="kill ".$s['Id'];
			mysql_query($sql);
		}
	}
}
mysql_close($link);
?>
