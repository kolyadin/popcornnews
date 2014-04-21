<?

//покажем phpinfo
if($_SERVER["QUERY_STRING"]=="php"){
  print phpinfo();
  exit(1);
};


//почистим всякое
$text=stripslashes($text);
$text=eregi_replace("
",'', $text);


?>
<html>
<head>



<script language="JavaScript"><!--

function save(){

 var s=workform.FCKeditor1.value;

 re=/\r\n/gm;
 s=s.replace(re," ");
 s=s.replace("manager/","");


 opener.document.forms.workform2.<? print $field; ?>.value=s;
 opener.document.forms.workform2.<? print $field; ?>.focus();
 window.close();

};


function check(){
  setTimeout("save()",1);
  return true;
};


//--></script>

</head>
<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<form onSubmit="return check();" style="margin:0;padding:0;" name="workform" method=POST>
    <div>
    <input type="hidden" id="FCKeditor1" name="FCKeditor1" value='<? print $text; ?>' style="display:none" />
    <input type="hidden" id="FCKeditor1___Config" value="" style="display:none" />
    <iframe id="FCKeditor1___Frame" src="fckeditor/editor/fckeditor.html?InstanceName=FCKeditor1&amp;Toolbar=Default" width="100%" height="100%" frameborder="0" scrolling="no"></iframe></div>
    <input type="hidden" name="field" value="<? print $field; ?>">
    </form></body>
</html>
