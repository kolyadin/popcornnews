<?php

define ('ABC_BAD_DATE', -1);

class ExcelDateUtil{


/*
 * return 1900 Date as integer TIMESTAMP.
 * for UNIX date must be
 *
 */

function xls2tstamp($date) {
	$date=$date>25568?$date:25569;
	/*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
   		$ofs=(70 * 365 + 17+2) * 86400;
  		 return ($date * 86400) - $ofs;
}

function getDateArray($xls_date){
    $ret = array();

    // leap year bug
    if ($xls_date == 60) {

        $ret['day']   = 29;
        $ret['month'] = 2;
        $ret['year']  = 1900;
        return $ret;

    } else if ($xls_date < 60) {
        // 29-02-1900 bug
        $xls_date++;
    }

    // Modified Julian to DMY calculation with an addition of 2415019
    $l = $xls_date + 68569 + 2415019;
    $n = (int)(( 4 * $l ) / 146097);
    $l = $l - (int)(( 146097 * $n + 3 ) / 4);
    $i = (int)(( 4000 * ( $l + 1 ) ) / 1461001);
    $l = $l - (int)(( 1461 * $i ) / 4) + 31;
    $j = (int)(( 80 * $l ) / 2447);
    $ret['day'] = $l - (int)(( 2447 * $j ) / 80);
    $l = (int)($j / 11);
    $ret['month'] = $j + 2 - ( 12 * $l );
    $ret['year'] = 100 * ( $n - 49 ) + $i + $l;

    return $ret;
}



function isInternalDateFormat($format) {
    $retval =false;

    switch(format) {
    // Internal Date Formats as described on page 427 in
    // Microsoft Excel Dev's Kit...
        case 0x0e:
        case 0x0f:
        case 0x10:
        case 0x11:
        case 0x12:
        case 0x13:
        case 0x14:
        case 0x15:
        case 0x16:
        case 0x2d:
        case 0x2e:
        case 0x2f:
        // Additional internal date formats found by inspection
        // Using Excel v.X 10.1.0 (Mac)
        case 0xa4:
        case 0xa5:
        case 0xa6:
        case 0xa7:
        case 0xa8:
        case 0xa9:
        case 0xaa:
        case 0xab:
        case 0xac:
        case 0xad:
        $retval = true; break;
        default: $retval = false; break;
    }
         return $retval;
}

}

?>