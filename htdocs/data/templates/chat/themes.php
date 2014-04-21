<?
$this->_render('inc_header',
	array(
	    'title'=>'Болталка',
	    'header'=>'Болталка',
	    'top_code'=>'<img src="/i/chat_ico.png">',
	    'header_small'=>'Общаемся на свободные темы'
	)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<table class="chatThemes">
			<tr>
				<th class="theme">Тема</th>
				<th class="last">Последнее сообщение</th>
			</tr>
			<?foreach ($d['themes'] as $theme) {?>
			<tr>
				<td class="theme">
					<div class="details">
						<h3><a href="/chat/theme/<?=$theme['id']?>"><?=$theme['name']?></a></h3>
						<span class="date">Обсуждений: <?=$theme['topics']?></span>
					</div>
				</td>
				<td class="last">
					<?if ($theme['last_update']) {?>
					<span class="date"><?=$p['date']->unixtime($theme['last_update']['cdate'], '%d %F %Y, %H:%i');?></span>
					<a rel="nofollow" href="/profile/<?=$theme['last_update']['user_id']?>" class="pc-user"><?=$theme['last_update']['user_nick']?></a>
					<?} else {?>
					&nbsp;
					<?}?>
				</td>
			</tr>
			<?}?>
		</table>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>