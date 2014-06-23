<?php

$html = '<span class="style_title"><b>Понедельник</b> 23 Июня</span>';

preg_match_all('/(\d+)\s(.*)/is', $html, $m, PREG_SET_ORDER);


$date = date('Y').$m[0][2].$m[0][1];

echo $date;

//print '<pre>'.print_r($m,true).'</pre>';