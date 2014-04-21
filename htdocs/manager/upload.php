<?

include "inc/connect.php";

?>
<html>
<head>
<title>wait....</title>
<link rel="STYLESHEET" type="text/css" href="styles/global.css">
</head>
<body><p class="contentjust">
<?

$f="";

$text=stripslashes($content);

$f=save_file($userfile_name,$userfile_size,$userfile,7,0,date("Ymd"),"","Картинка к тексту",0,0,0,0);

//print "=$f=";

if($f!="") {
        
   if($pictures=="base")$text='<img src="/showpix.php?id='.$f.'" border="0" alt="" class="UploadImg">'.$text;
     else $text='<img src="'.$filepath.$f.'" border="0" alt="" class="UploadImg">'.$text;

};


?>
<form method="post" name="skyform" action="editor.php" ENCTYPE="multipart/form-data">
<input type="hidden" name="text" value='<? print $text; ?>'>
<input type="hidden" name="field" value='<? print $field; ?>'>
<? if(($action!="done")&&($action!="preview")){ ?>
<script>
document.forms.skyform.submit();
</script>
<? } elseif($action=="preview"){

$content=stripslashes($content);
print $content."<br clear='all'><br><br><input type='submit' value='Edit' class='fsmallb'>";

 }; ?>
 </form>
</p></body>
</html>