<?if ($d['my']) {?>
<div class="status">
	<div class="status_msg"><?=$d['user_status'] && $d['user_status']['deleted'] != 'y' ? $d['user_status']['status'] : 'Изменить...'?></div>
	<div class="btn_status_history"></div>
</div>
<?} elseif (isset($d['user_status']['status'])) {?>
<div class="status">
	<div class="status_msg"><?=$d['user_status']['status']?></div>
	<div class="btn_status_history"></div>
</div>
<?}?>
