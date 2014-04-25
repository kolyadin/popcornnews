<?php
/**
 * @param array - array из добавляемых строк в QueryString, например: array('begin', '30')
 * @delete array - array из елементов которые надо удалить, например: array('begin', '30')
 */
function noRepeatGet($array = array(), $delete = array()){
  $sGet = '?';

  $aGet = array_merge($_GET, $array);
  for ($i=0; $i<count($delete); $i++){ // чистим
    foreach ($aGet as $key => $val){
      if ($key == $delete[$i]) unset($aGet[$key]);
    }
  }

  foreach ($aGet as $key => $val) $sGet .= $key . '=' . $val . '&';
  $sGet = substr($sGet, 0, -1);
  return $sGet;
}

/**
 * Вывод страниц
 * @param count - кол-во результатов
 * @param per_page - кол-во результатов на страницу
 * @param pages - кол-во страниц для которых нужно показать ссылки
 * @param begin - ключ в $_GET который отвечает за начало отсчета
 * @param end - ключ в $_GET который отвечает за кол-во результатов на страницу
 */
function getPages($count, $per_page, $pages = 10, $begin = 'begin', $end = 'end'){
  $count1 = $count2 = $count;
  if ($count > $per_page){
    $i = 1; // номеровка станиц
    $j = 0; // перемотка на $per_page вперед
    $m = 1; // отсчет $pages страциц
    echo 'Страницы :';
    if ($_GET[$begin] != 0) echo "\t\t\t" . '<a href="' . noRepeatGet(array($begin => 0, $end => $per_page)) . '"> << </a>' . "\n"; // первая страница
    if (isset($_GET[$begin]) && $_GET[$begin] > (($per_page*$pages)/2)){ // если уже выбрали какую-то страницу то показываем, только 10 перед этой таблицей, и 10 после страниц
      $j = round($_GET[$begin]-($pages/2)*$per_page)-$per_page;
      $i = round(($_GET[$begin]/$per_page)-($pages/2));
    }
    while ($count > 0 && $j < $count1){
      if ($_GET[$begin] == $j) echo "\t\t\t" . '<b>' . $i . '</b>&nbsp;' . "\n"; //выводим текущий номер страницы
      else echo "\t\t\t" . '<a href="' . noRepeatGet(array($begin => $j, $end => $per_page)) . '">' . $i . '&nbsp;</a>' . "\n";
      $count-=$per_page;
      $j+=$per_page;
      $i++;
      $m++;
      if ($m > $pages){
		  $j = $count2-$per_page;
		  $jFold = $j % $per_page;
		  if ($jFold != 0) $j = $j + ($per_page - $jFold); // равняем границу до числа кратному $per_page, чтобы подсветку не сбивать у страниц
		  echo "\t\t\t" . '<a href="' . noRepeatGet(array($begin => $j, $end => $per_page)) . '"> >> </a>' . "\n"; // последная страница
		  break;
      }
    }
  }
}

?>