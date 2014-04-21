<?php
include "../inc/connect.php";
$tbl_cities="popcornnews_cities";
$tbl_countries="popcornnews_countries";

foreach($_POST["skip_ids"] as $key=>$value){
	$cmd="update $tbl_cities set skip='1' where id=".intval($value);
	mysql_query ($cmd,$link);
}
header("location: ./admin.php?type=cities&country_id=".$_POST["country_id"]);
exit;

?>