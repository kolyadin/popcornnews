<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title></title>
<meta content="text/html; charset=windows-1251" http-equiv="Content-Type">
<META NAME="description" CONTENT="">
<META NAME="keywords" CONTENT="">
<META NAME="ask" CONTENT="">
<META HTTP-EQUIV="keywords" CONTENT="">
<link rel="stylesheet" type="text/css" href="styles/global.css">
<style>

.ContextMenu a:hover { font-size:11px; color:#FFFFFF; background:#000080;}


</style>

<SCRIPT language="JavaScript"><!--

  
  function shmore(){

          if(document.getElementById('more').style.display=="none")document.getElementById('more').style.display="block";
            else document.getElementById('more').style.display="none";

  };

  function shmore_(){

          if(document.getElementById('more_').style.display=="none")document.getElementById('more_').style.display="block";
            else document.getElementById('more_').style.display="none";

  };

  function shmore_2(){

          if(document.getElementById('more_2').style.display=="none")document.getElementById('more_2').style.display="block";
            else document.getElementById('more_2').style.display="none";

  };
  <?

  if($action=="reload") print 'self.parent.reload_frame();';
  if($action=="reloadnew") print 'self.parent.left1.location.href="./goods.php?page_id='.$page_id.'&showhead=<? print $showhead; ?>&pageid=2&subpageid=1&rand='.rand().'";';

  ?>
 //--></script>

<SCRIPT language="JavaScript"><!--
function d_g(id){
        <? if($page_id!=1){ ?>
        var loc='goods.php?pageid=16&showhead=<? print $showhead; ?>&rand=<? print rand(); ?>&fold_id=<? print $fold_id; ?>&page_id=<? print $page_id; ?>&id='+id;
        <? } else { ?>
        var loc='goods.php?pageid=17&showhead=<? print $showhead; ?>&rand=<? print rand(); ?>&fold_id=<? print $fold_id; ?>&page_id=<? print $page_id; ?>&id='+id;
        <? }; ?>
        if(window.confirm("Удалить файл?")) document.location=loc;
};

<?

  //if($action=="reload") print 'self.parent.reload_frame();';

  ?>
 //--></script>

<SCRIPT language="JavaScript"><!--
/*
  function shmore(){

          if(document.getElementById('more').style.display=="none")document.getElementById('more').style.display="block";
            else document.getElementById('more').style.display="none";
  };
*/

var show_navi=0;

 //--></script>




</head><? /*
<!--
<body onClick="self.parent.self.parent.changeZI(self.parent.window.name);" onMouseMove="self.parent.self.parent.godrag2(self.parent.window.name,window.event.x,window.event.y);event.cancelBubble=true;event.returnValue=false;">
-->
<?
*/

//$showhead=-1;

if($showhead!=-10){

?>
<body
	onClick="<?if($pageid==2 && $subpageid==3) echo "hidecontext();"; ?> try {<?if ($showhead >= 0){ ?>self.parent.<?}?>self.parent.changeZI(<? if($showhead>=0){ ?>self.parent.<? }; ?>window.name);} catch (e) {}"
	<?if ($showhead==0){ ?>onLoad='setTimeout(function () {try {self.parent.set_active_fold(<?=(int)$page_id?>,<?=(int)$fold_id?>); } catch (e) {} },100);'<?}?>
>
<?

//$showhead=-1;

?>

<? if($showhead==1 || $showhead==-2){ ?>
<script language="JavaScript"><!--

function shownavi(){
  
  if(NavFrame.style.display!='block')NavFrame.style.display='block'; 
      else {
        NavFrame.style.display='none';
  };

  /*
  if(show_navi==0){
    show_navi=2;
    NavFrame.style.display='block'; 

    setTimeout('show_navi=2;',20);

      
  } else show_navi=0;
  //alert(show_navi);
  */
};

//--></script>
<div id="NavFrame"><iframe src="./goods.php?showhead=<? print $showhead; ?>&pageid=2&subpageid=13&page_id=<? print $page_id; ?>&rand=<? print rand(); ?>" scrolling="auto" id="NFrame" frameborder="0"></iframe></div>
<? }; ?>

<? } else print "<body>"; ?>