<a href="?section=main">Обновить кэш</a><br />
<a href="?section=twiligth">Обновить кэш для тега сумерки</a><br />
<a href="?section=potter">Обновить кэш для тега гари поттер</a><br />
<a href="?section=sex">Обновить кэш для тега секс в большом</a><br />
<?
die('Не нужно!');
if (isset($_GET['section'])){
   switch ($_GET['section']){
	 case 'main':
	   $status = unlink($_SERVER['DOCUMENT_ROOT'].'/data/var/gen/right.tmp');
	   break;
	 case 'twiligth';
	   $status = unlink($_SERVER['DOCUMENT_ROOT'].'/data/var/gen/right_twilight.tmp');
	   break;
	 case 'potter';
	   $status = unlink($_SERVER['DOCUMENT_ROOT'].'/data/var/gen/right_potter.tmp');
	   break;
	 case 'sex';
	   $status = unlink($_SERVER['DOCUMENT_ROOT'].'/data/var/gen/right_sex.tmp');
	   break;
   }

   if ($status) echo 'Кеш для выбранного раздела удален!';
   else echo 'Ошибка при удаление кеша в данном разделе!';
}
/*
<form action="/" method="post" target="ifr">
<input type="hidden" name="reloadcache" value="1" />
<input type="submit" value="Обновить кэш для главной" />
</form>
<script>
var ale=false;
</script>
<iframe id="irf" name="ifr" onload="ale=!ale;if(!ale)alert('Кэш для главной обновлён');"></iframe>
*/
?>