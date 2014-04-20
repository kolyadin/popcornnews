
<?if (SHOW_ADS) {?>
<?/* 10.12.2010 OPTIMIZATION FOR YANDEX SEO
<noindex>
	<div class="sbDiv irh irhFriends">
		<!-- MarketNews Start -->
		<div id="MarketGid1495"><center>Загрузка...</center></div>
		<!-- MarketNews End -->

		<!-- bof RedTram N4P -->
		<div id="rtn4p_neb_ai">Загрузка...</div>
		<!-- eof RedTram N4P -->
	</div>
</noindex>
*/?>

<div class="sbDiv irh irhFriends links">
	<?
	// sape links
	if (!defined('_SAPE_USER')) {
		define('_SAPE_USER', 'a7d699e621f5eef4d2c235a4ae19fdb9');
	}
	require_once($_SERVER['DOCUMENT_ROOT'] . '/sape/sape.php');
	$o['force_show_code'] = true;
	$sape = new SAPE_client($o);
	echo $sape->return_links();
	?>
</div>
<?}?>
