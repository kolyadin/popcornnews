<?php


define('EXCEL_FONT_RID',0x31);

define('XF_SCRIPT_NONE',0);
define('XF_SCRIPT_SUPERSCRIPT',1);
define('XF_SCRIPT_SUBSCRIPT',2);

define('XF_UNDERLINE_NONE',0x0);
define('XF_UNDERLINE_SINGLE',0x1);
define('XF_UNDERLINE_DOUBLE',0x2);
define('XF_UNDERLINE_SINGLE_ACCOUNTING',0x3);
define('XF_UNDERLINE_DOUBLE_ACCOUNTING',0x4);

define('XF_STYLE_ITALIC', 0x2);
define('XF_STYLE_STRIKEOUT', 0x8);

define('XF_BOLDNESS_REGULAR',0x190);
define('XF_BOLDNESS_BOLD',0x2BC);


class ExcelFont {


 function basicFontRecord() {
    return  array('size'     => 10,
                    'script'   => XF_SCRIPT_NONE,
                    'undeline' => XF_UNDERLINE_NONE,
                    'italic'   => false,
                    'strikeout'=> false,
                    'bold'     => false,
                    'boldness' => XF_BOLDNESS_REGULAR,
                    'palete'   => 0,
                    'name'     => 'Arial');
 }

 function getFontRecord(&$wb,$ptr) {

    $retval = array('size'     => 0,
                    'script'   => XF_SCRIPT_NONE,
                    'undeline' => XF_UNDERLINE_NONE,
                    'italic'   => false,
                    'strikeout'=> false,
                    'bold'     => false,
                    'boldness' => XF_BOLDNESS_REGULAR,
                    'palete'   => 0,
                    'name'     => '');
    $retval['size'] = (ord($wb[$ptr])+ 256*ord($wb[$ptr+1]))/20;
    $style=ord($wb[$ptr+2]);
    if (($style & XF_STYLE_ITALIC) != 0) {
        $retval['italic'] = true;
    }
    if (($style & XF_STYLE_STRIKEOUT) != 0) {
        $retval['strikeout'] = true;
    }
    $retval['palete'] = ord($wb[$ptr+4])+256*ord($wb[$ptr+5]);

    $retval['boldness'] = ord($wb[$ptr+6])+256*ord($wb[$ptr+7]);
    $retval['bold'] = $retval['boldness'] == XF_BOLDNESS_REGULAR ? false:true;
    $retval['script'] =  ord($wb[$ptr+8])+256*ord($wb[$ptr+9]);
    $retval['underline'] = ord($wb[$ptr+10]);

    $length = ord($wb[$ptr+14]);
    if($length >0) {
        if(ord($wb[$ptr+15]) == 0) { // Compressed Unicode
            $retval['name'] = substr($wb,$ptr+16,$length);
        } else { // Uncompressed Unicode
            $retval['name'] = ExcelFont::getUnicodeString($wb,$ptr+15,$length);
        }

    }


    return $retval;
 }

 function toString(&$record,$index) {
    $retval = sprintf("Font Index = %d \nFont Size =%d\nItalic = %s\nStrikeoout=%s\nPalete=%s\nBoldness = %s Bold=%s\n Script = %d\n Underline = %d\n FontName=%s<hr>",
                $index,
                $record['size'],
                $record['italic']    == true?"true":"false",
                $record['strikeout'] == true?"true":"false",
                $record['palete'],
                $record['boldness'],
                $record['bold'] == true?"true":"false",
                $record['script'],
                $record['underline'],
                $record['name']
                );

    return $retval;

 }


 function getUnicodeString(&$string,$offset,$length) {
        $bstring = "";
        $index   = $offset + 1;   // start with low bits.

        for ($k = 0; $k < $length; $k++)
        {
            $bstring = $bstring.$string[$index];
            $index        += 2;
        }
        return substr($bstring,0,$length);
 }

 function ExcelToCSS($rec, $app_font=true, $app_size=true, $app_italic=true, $app_bold=true){
    $ret = "";
    if($app_font==true){
        $ret = $ret."font-family:".$rec['name']."; ";
    }
    if($app_size==true){
        $ret = $ret."font-size:".$rec['size']."pt; ";
    }
    if($app_bold==true){
        if($rec['bold']==true){
            $ret = $ret."font-weight:bold; ";
        } else {
            $ret = $ret."font-weight:normal; ";
        }
    }
    if($app_italic==true){
        if($rec['italic']==true){
            $ret = $ret."font-style:italic; ";
        } else {
            $ret = $ret."font-style:normal; ";
        }
    }

    return $ret;
 }

}





?>