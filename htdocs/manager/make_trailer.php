<?php
set_time_limit(0);

include "../inc/connect.php";

ini_set("upload_max_filesize ", "10000000");



$root = $HTTP_SERVER_VARS['DOCUMENT_ROOT'];


$uploaddir = $root . '/video/';

$filmgoods = 3; // папка с персонами
$trailersgoods = 14; //папкка с трейлерами
$step = intval($step);

//clear string
function cl($sourse) {
	$rez = trim(eregi_replace("'", "\"", htmlspecialchars(strip_tags($sourse))));
	if (!$rez
		)$rez = '&nbsp;';return $rez;
}

//с переносами
function cl1($sourse) {
	$rez = trim(eregi_replace("'", "\"", htmlspecialchars(strip_tags($sourse))));
	if (!$rez
		)$rez = '&nbsp;';return nl2br($rez);
}

if ($ac == 'del') {
	$cmd = "select * from $tbl_goods_ where id=" . $id_del . " AND goods_id=" . $trailersgoods;
	$line = mysql_query($cmd, $link);
	if ($s = mysql_fetch_assoc($line)) {
		$cmd = "delete from $tbl_goods_ where id=" . $s["id"];
		mysql_query($cmd, $link);

		if ($s["pole2"] != '' && file_exists($uploaddir . $s["pole2"]))
			unlink($uploaddir . $s["pole2"]);
		if ($s["pole3"] != '' && file_exists($uploaddir . $s["pole3"]))
			unlink($uploaddir . $s["pole3"]);
		if ($s["pole4"] != '' && file_exists($uploaddir . $s["pole4"]))
			unlink($uploaddir . $s["pole4"]);
		if ($s["pole5"] != '' && file_exists($uploaddir . $s["pole5"]))
			unlink($uploaddir . $s["pole5"]);
	}
	header("location: http://www.popcornnews.ru/manager/make_trailer.php?filmid=" . $filmid . "&step=1");
	exit;
}
if ($ac == 'show' && $id_show = intval($id_show)) {

	$cmd = "select * from $tbl_goods_ where id=" . $id_show . " AND goods_id=" . $trailersgoods;
	$line = mysql_query($cmd, $link);
	if ($s = mysql_fetch_assoc($line)) {
		$cmd = "update $tbl_goods_ set pole11='" . ($s["pole11"] != '' ? '' : 'Yes') . "' where id=" . $s["id"];

		mysql_query($cmd, $link);
	}

	header("location: http://www.popcornnews.ru/manager/make_trailer.php?filmid=" . $filmid . "&step=1");
	exit;
}



switch ($step) {

	case 0:

		$cmd = "SELECT a.id, a.name, b.cnt FROM $tbl_goods_ a left join (select pole1, count(*) cnt  from $tbl_goods_ where goods_id='" . $trailersgoods . "' and page_id=2 group by pole1 ) b on b.pole1=a.id WHERE a.goods_id=" . $filmgoods . " ORDER BY a.name";
		$line = mysql_query($cmd, $link);
		while ($s = mysql_fetch_assoc($line)) {
			$filmlist.='<option value="' . $s["id"] . '">' . $s["name"] . ($s["cnt"] != '' ? ' (' . $s["cnt"] . ')' : '') . '</option>';
		}

		$text = '
     <form action="make_trailer.php" method="post" name="f1">
      <input type="hidden" name="step" value="1">
     <h1>Выберите персону </h1>
     <select name="filmid">
      ' . $filmlist . '
     </select>
      <br>
      <input type="submit" value="Далее >>">
     </select>
     </form>
     ';
		break;



	case 1:
		$cmd = "SELECT * FROM $tbl_goods_ WHERE goods_id=" . $trailersgoods . " AND pole1=" . $filmid . " AND page_id=2 order by id ";
		$sql = mysql_query($cmd, $link);

		if (!mysql_num_rows($sql)) {
			$trailersCont = 'Ничего не найдено';
		} else {
			while ($s = mysql_fetch_assoc($sql)) {
				$list.='<tr' . ($s['pole11'] != '' ? ' class="hidden"' : '') . '>
          <td>
			' . ($s["pole3"] != '' ? '<a href="#" onclick="window.open(\'/trailer.php?fid=' . $filmid . '&cur=' . $s["id"] . '\',null,\'height=480,width=480,status=no,toolbar=no,menubar=no,location=no\');return false;" target="_blank"><img src="/video/' . $s["pole3"] . '"></a>' : '<a href="#" onclick="window.open(\'/trailer.php?fid=' . $filmid . '&cur=' . $s["id"] . '\',null,\'height=480,width=480,status=no,toolbar=no,menubar=no,location=no\');return false;" target="_blank">EMBED</a>') . '
          </td>
          <td>
          <a href="#" onclick="window.open(\'/trailer.php?fid=' . $filmid . '&cur=' . $s["id"] . '\',null,\'height=480,width=480,status=no,toolbar=no,menubar=no,location=no\');return false;" target="_blank">' . $s['name'] . '</a><br>' . ($s['pole2'] != '' ? ' (VIDEO)' : ' (EMBED)') . '
          </td>
          <td>
          ' . ($s['pole11'] != '' ? '<a href="make_trailer.php?ac=show&id_show=' . $s["id"] . '&filmid=' . $filmid . '&' . rand() . '">показывать</a>' : '<a href="make_trailer.php?ac=show&id_show=' . $s["id"] . '&filmid=' . $filmid . '&' . rand() . '">скрыть</a>') . '
          </td>
          <td>
          <a href="#" onClick="if(confirm(\'Хотите удалить трейлер?\')){location.href=\'make_trailer.php?ac=del&id_del=' . $s["id"] . '&filmid=' . $filmid . '\'}else{return false;}"><img src="/i/delete.gif" border="0" style="margin-left:15px" align="absmiddle"></a>
          </td>
          </tr>';
			}
			$list = '<table border="0" cellspacing="0" cellpadding="0" class="tbl">
        ' . $list . '
        </table>';
		}

		$query = "SELECT name FROM $tbl_goods_ WHERE id=" . $filmid . " AND goods_id=" . $filmgoods;
		$q = mysql_query($query, $link);


		$text.='
     <form action="make_trailer.php" method="post" name="f1">
      <input type="hidden" name="step" value="20">
      <input type="hidden" name="filmname" value="' . htmlspecialchars(mysql_result($q, 0), ENT_QUOTES) . '">
      <input type="hidden" name="filmid" value="' . $filmid . '">
     <h1>Персона : ' . mysql_result($q, 0) . '</h1>
      ' . $trailersCont . '
      <br>
      <br>
      ' . $list . '
       <br><br>
      <input type="submit" value="Добавить трейлер>>">
      </form>
     ';
		break;

	case 20://добавление трейлера или ссылки
		$text.='
     <form name="f1"  enctype="multipart/form-data" method="POST" action="make_trailer.php" onSubmit="if(document.f1.trname.value!=\'\'){this.submit();}else{alert(\'Пожалуйста укажите название для клипа!\')}return false;">
      <input type="hidden" name="step" value="21">
      <input type="hidden" name="filmid" value="' . $filmid . '">
     <h1>Персона : ' . $_POST['filmname'] . '</h1>
      Введите название<br>
      <input name="trname" type="text">
      <br>
      укажите фаил с видео<br>
      <input type="file" name="userfile"><br>
      <br>
      или введите embed<br>
      <textarea name="embed"></textarea>
      <br><br>
      <input type="submit" value="Далее >>">
     </form>
    ';

		break;

	case 21:// сохранение трейлера или ссылки

		$fPre = ''; //имя flv файла

		if (isSet($_FILES['userfile']) && $_FILES['userfile']['name']) {//


			if ($_FILES['userfile']['name'] && $_FILES['userfile']['error']) {
				echo $_FILES['userfile']['error'];
				echo var_dump($_FILES);
				echo '<hr>';
				echo var_dump($_POST);
				exit(1);
			}
			if ($_FILES['userfile']['name'] && $_FILES['userfile']['tmp_name']) {
				$s = explode(".", $_FILES['userfile']['name']);
				$ftype = $s[count($s) - 1];

				while (file_exists($uploaddir . $un)) {
					$fPre = rand(0, 10000000) . $ftype;
					$un = $fPre . "." . $ftype;
				}

				$uploadfile = $uploaddir . $un;


				if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					set_time_limit(600);
					//определим фпс
					$sysStr = '/usr/local/bin/mplayer -frames 0 "' . $uploadfile . '"';
					if ($sysStr)$a = `$sysStr`;

					$s1 = explode('VIDEO:', $a);
					$s = $s1[1];


					$s2 = explode(' fps', $s);
					$s = $s2[0];

					$trailerInfo = $s;

					/*
					$s3 = explode(' ', $s);
					$trailerFps = trim($s3[count($s3) - 1]);
					if (!$trailerFps) {
						echo 'Невозможно определить fps <hr>' . $a;
						exit(1);
					}
					*/

					if ($ftype != 'flv') {
						$sysStr = '/usr/local/bin/mencoder ' . $uploadfile . ' -o ' . $uploaddir . $fPre . '_.flv -of lavf -lavcopts vcodec=flv:vbitrate=512:mbd=2:mv0:trell:v4mv:cbp:last_pred=3 -oac mp3lame -lameopts abr:br=56 -srate 22050 -vf scale=580:-3 -ovc lavc -sws 2';
						$newFilename = $fPre . '_.flv';
					} else {
						$newFilename = $fPre . '.' . $ftype;
					}
					if ($sysStr) $a = `$sysStr`;

					//делаем картинку
					$sysStr = '/usr/local/bin/mplayer ' . $uploadfile . ' -ss 00:00:10 -frames 1 -ao null -vo jpeg:quality=100:smooth=0:outdir=\'' . $uploaddir . '\' -vf scale=84:84';
					$a = `$sysStr`;
					if (file_exists($uploaddir . '00000001.jpg')) {
						$picfile = $fPre . '.jpg';
						$renFl = rename($uploaddir . '00000001.jpg', $uploaddir . $picfile);
					} else {
						echo '<b style="color:red">Неудалось создать превьюшку</b> ' . $uploadfile; /* exit(1); */
						$renFl = true;
					}

					/* big==================================== */
					/*
					  //делаем картинку большую
					  $sysStr='/usr/local/bin/mplayer '.$uploadfile.' -ss 00:00:10 -frames 1 -ao null -vo jpeg:quality=100:smooth=0:outdir=\''.$uploaddir.'\' -vf scale=150:150';
					  $a=`$sysStr`;

					  if(file_exists($uploaddir.'00000001.jpg'))
					  {
					  $picfile2='big_'.$fPre.'.jpg';
					  $renFl=rename($uploaddir.'00000001.jpg',$uploaddir.$picfile2);
					  }
					  else {echo '<b style="color:red">Неудалось создать превьюшку</b> '.$uploadfile;exit(1);}
					 */
					/* ====================================big */

					//пишем в базу если все хорошо
					if ($renFl === TRUE) {
						$query = sprintf(
							'INSERT INTO %s SET page_id = 2, goods_id = %u, name = "%s", pole1 = "%s", pole2 = "%s", pole3 = "%s", pole4 = "%s", pole5 = "%s"',
							$tbl_goods_, $trailersgoods, cl($trname), $filmid, $newFilename, $picfile, $fPre, $picfile2
						);
						// $query = "INSERT INTO $tbl_goods_ (page_id,goods_id,name,pole1,pole2,pole3,pole4,pole5) VALUES (2," . $trailersgoods . ",'" . cl($trname) . "','" . $filmid . "','" . $fPre . '_.flv' . "','" . $picfile . "','" . $fPre . ".flv','" . $picfile2 . "')";
						$q = mysql_query($query, $link) or die(mysql_error());

						header("location: make_trailer.php?step=1&filmid=" . $filmid);
						exit(1);
					} else {
						echo 'Ошибка при записи или операции с файлами';
						exit(1);
					}
				} else {
					echo '<b style="color:red">неудалось переместить фаил!!! move_uploaded_file(' . $_FILES['userfile']['tmp_name'] . ',' . $uploadfile . ')</b>
             <a href="javascript:{history.back();}">Вернуться назад</a>';
					exit(1);
				}
			} else {
				echo '<b style="color:red">неудалось получить переменные файла!!! name=' . $_FILES['userfile']['name'] . '  tmp_name=' . $_FILES['userfile']['tmp_name'] . '  </b>
             <a href="javascript:{history.back();}">Вернуться назад</a>';
				exit(1);
			}

			if (!headers_sent()) {
				header("location: make_trailer.php?step=1&filmid=" . $filmid);
				exit;
			} else {
				printf('<a href="?step=1&filmid=%u">Назад</a>' . $filmid);
			}
		} elseif (!empty($_POST["embed"])) {
			$query = "INSERT INTO $tbl_goods_ (page_id,goods_id,name,pole1,pole6) VALUES (2," . $trailersgoods . ",'" . cl($trname) . "','" . $filmid . "','" . mysql_real_escape_string(stripslashes($_POST["embed"])) . "')";
			$q = mysql_query($query, $link) or die(mysql_error());

			header("location: make_trailer.php?step=1&filmid=" . $filmid);
			exit(1);
		} else {
			echo '<b style="color:red">не задан файл</b> <a href="javascript:{history.back();}">Вернуться назад</a>';
			exit(1);
		}


		break;
}
?>


<html>
	<head>
		<script src="/js/AC_RunActiveContent.js" language="javascript"></script>
		<script type="text/javascript" src="/js/show_flash.js"></script>
		<style>
			table {border:0px solid #fff}
			td {padding: 4px 5px 4px 5px; border-bottom: 1px #bbb solid;}
			.tbl td {background:#fde;}
			.tbl tr.hidden td {background:#eee}
			img {border:0px solid #fff}
		</style>
	</head>
	<body style="font-family:Arial;font-size:12px">
		<a href="./make_trailer.php" title="перейти к выбору фильма"><img src="./i/move.gif" style="float:right;margin-top:-10px;border:none" width="32" height="32" align="absmiddle"></a>
		<hr style="clear:all">
  <?=$text
?>
	</body>
</html>