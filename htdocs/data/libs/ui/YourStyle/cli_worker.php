<?php

function RGBtoLab($red, $green, $blue) {
    $x = $y = $z = 0;

    $r = $red / 255;
    $g = $green / 255;
    $b = $blue / 255;

    $r = ($r > 0.04045) ? pow((($r + 0.055) / 1.055), 2.4) : $r / 12.92;
    $g = ($g > 0.04045) ? pow((($g + 0.055) / 1.055), 2.4) : $g / 12.92;
    $b = ($b > 0.04045) ? pow((($b + 0.055) / 1.055), 2.4) : $b / 12.92;

    $r = $r * 100;
    $g = $g * 100;
    $b = $b * 100;

    $x = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
    $y = $r * 0.2426 + $g * 0.7152 + $b * 0.0722;
    $z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;
     
    $l = $a = $lb = 0;

    $x = $x / 95.047;
    $y = $y / 100;
    $z = $z / 108.883;

    $x = ($x > 0.008856) ? pow($x, 1/3) : ($x * 7.787) + (16 / 116);
    $y = ($y > 0.008856) ? pow($y, 1/3) : ($y * 7.787) + (16 / 116);
    $z = ($z > 0.008856) ? pow($z, 1/3) : ($z * 7.787) + (16 / 116);

    $l = (116 * $y) - 16;
    $a = 500 * ($x - $y);
    $lb = 200 * ($y - $z);

    return array('l' => $l, 'a' => $a, 'b' => $lb);
}

function colorLength($lab1, $lab2) {
    return sqrt(pow($lab1['l'] - $lab2['l'], 2) + pow($lab1['a'] - $lab2['a'], 2) + pow($lab1['b'] - $lab2['b'], 2));
}

$memcache = new Memcache();
$memcache->connect('unix:///var/run/memcached/memcached.sock',null);

$memcache_key = $_SERVER['argv'][1];
$color_key = $_SERVER['argv'][2];
$mc_color_key = $memcache_key."_color_".$color_key;

$result = array();
//$memcache->set($memcache_key."_color_".$color_key."_result", $result, 0, 60*60);

$currentColor = $memcache->get($memcache_key."_color_".$color_key);

$histogram = $memcache->get($memcache_key."_histogram");

$wm = $memcache->get($memcache_key."_wm");
$w = (count($currentColor['range']) / $wm);

$result['e'] = 0;
$result['count'] = 0;
$result['c'] = 0;
//$result['total'] = array_keys($currentColor);

foreach ($currentColor['range'] as $item) {
    foreach ($histogram as $match) {
        $lab = RGBtoLab($match['r'], $match['g'], $match['b']);
        $e = colorLength($item, $lab);
        if($e < 15) {
            $result['e'] += $e;
            $result['count'] += $match['count']*$w;
            $result['c']++;
        }
    }
}

$de = ($result['c'] != 0) ? $result['e'] / $result['c'] : 0;
$result['cc'] = ($result['count'] != 0) ? ($de / $result['count']) : 0;
$result['hex'] = $color_key;

$memcache->set($memcache_key."_color_".$color_key."_result", $result, 0, 60*60);

//print $color_key."_result";