<?
$this->_render('inc_header',
	array(
	    'title' => 'Вопросы администрации',
	    'header' => 'Вопросы администрации',
	    'top_code' => '>',
	)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<table class="personTalks">
			<tr>
				<th class="theme">Тема, автор</th>
			</tr>
			<?foreach ($d['list'] as $i => $theme) {?>
				<tr>
					<td class="theme<?=(!$theme['atime'] ? ' new' : null)?>" id="<?=$theme['id']?>">
						<a class="ava"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($theme['user_avatara']))?>" /></a>
						<div class="details">
							<h3><a href="/ask/<?=$theme['id']?>"><?=$theme['name']?></a></h3>
							Автор:
							<a class="pc-user" rel="nofollow" href="/profile/<?=$theme['user_id']?>"><?=$theme['user_nick']?></a>,
							<span class="date"><?=$p['date']->unixtime($theme['qtime'], '%d %F %Y, %H:%i')?></span>
							<?if ($this->canAnwser()) {?>, <a class="pc-user" onclick="delete_msg(<?=$theme['id']?>, 'ask'); return false;" href="#">удалить</a><?}?>
							<?if ($theme['atime']) {?><br />Ответ:<span class="date"><?=$p['date']->unixtime($theme['atime'], '%d %F %Y, %H:%i')?></span><?}?>
						</div>
					</td>
				</tr>
			<?}?>
		</table>
		<?if ($d['num'] > $d['per_page']) {?>
		<div>
			<div class="paginator smaller">
				<p class="pages">Страницы:</p>
				<ul>
					<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
					<li>
						<?if (!isset($pi['current'])) {?>
						<a href="/ask/page/<?=$pi['link']?>"><?=$pi['text']?></a>
						<?} else {?>
						<?=$pi['text']?>
						<?}?>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<?}?>
		<h4 class="ask"><strong class="no_info"><a href="/ask/add">Задать свой вопрос</a></strong></h4>
	</div>
<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>