<?

//header("Pragma: public"); 

// программа показывает файл, без размеров.
// формат вызова:
// showpix.php?id=HR1awY.jpg&mode=save
// mode=save - значит будет предложено сохранить

//error_reporting(0);


$pictures="file"; // файлы в 
//$pictures="base"; // файлы в базе



$id=eregi_replace("../","",$id);
$id=eregi_replace("/","",$id);


switch(intval($type)){

  case(15): // file
    $fe="txt";
    $ct="text/plain";
    $fn="file";
  break;

  case(16): // word
    $fe="doc";
    $ct="application/msword";
    $fn="document";
  break;

  case(6): // excel
    $fe="xls";
    $ct="application/vnd.ms-excel";
    $fn="table";
  break;

  default:
  case(7): // jpg/gif
    $fe="jpg";
    $ct="image/jpeg";
    $fn="picture";
  break;

  case(8): // swf
    $fe="swf";
    $ct="application/x-shockwave-flash";
    $fn="flash";
  break;

  case(9): // pdf
    $fe="pdf";
    $ct="application/pdf";
    $fn="document";
  break;

  case(10): // avi
    $fe="avi";
    $ct="video/avi";
    $fn="video";
  break;

  case(11): // mpeg
    $fe="mpeg";
    $ct="video/avi";
    $fn="video";
  break;

  case(12): // вав
    $fe="wav";
    $ct="audio/x-wav";
    $fn="sound";
  break;

  case(13): // mp3
    $fe="mp3";
    $ct="audio/mpeg";
    $fn="sound";
  break;

  case(14): // zip
    $fe="zip";
    $ct="application/zip";
    $fn="archive";
  break;

};



if($pictures!="file"){ // храним картинки в базе
        
        include "inc/connect.php";
        
        $cmd="SELECT * FROM $tbl_pix WHERE id=".$id;
        $line1 = mysql_query($cmd,$link);
        if($ss=mysql_fetch_array($line1)){
          $t=get_pix_types($ss["type"]);

                mysql_close();
        
                if($mode=="save"){
                      Header("Content-type: application\n");
                      Header("Content-type: $t\n");
                      header("Content-Disposition: attachment; filename=".$ss["fizname"]."\n");
                } else {
                      Header("Content-type: $t\n");
                };
                
                print $ss["pix"];
                exit(1);
        } else { // ничего нет
      
                Header("Content-type: image/gif\n");
                $fn="del.gif";
                $fp=fopen ("manager/i/".$fn, "r");
                $buf=fread($fp,filesize("manager/i/".$fn));
                fclose($fp);
                print $buf;

        };

} else {

        if(@$fp=fopen ("upload/".$id, "r")){
         
                
                if($mode=="save"){
                      Header("Content-type: application\n");
                      Header("Content-type: $t\n");
                      header("Content-Disposition: attachment; filename=".$id."\n");
                      //if($filename=="")header("Content-Disposition: attachment; filename=".$fn.".".$fe."\n");
                      //  else header("Content-Disposition: attachment; filename=".$filename."\n");
                } else {
                      Header("Content-type: $t\n");
                };
                
                $buf=fread($fp,filesize("upload/".$id));
                fclose($fp);
                print $buf;
                exit(1);

        } else { // ничего нет
      
                Header("Content-type: image/gif\n");
                $fn="del.gif";
                $fp=fopen ("manager/i/".$fn, "r");
                $buf=fread($fp,filesize("manager/i/".$fn));
                fclose($fp);
                print $buf;

        };

};

?>
