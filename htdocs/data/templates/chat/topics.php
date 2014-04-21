<?
$this->_render('inc_header',
	array(
	    'title'=>$d['theme']['name'],
	    'header'=>$d['theme']['name'],
	    'top_code'=>'<img src="/i/chat_ico.png">',
	    'header_small'=>'Общаемся на свободные темы'
	)
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active"><a href="/chat/theme/<?=$d['theme']['id']?>/">все обсуждения</a></li>
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/messages">все комментарии</a></li>
			<li><a href="/chat/theme/<?=$d['theme']['id']?>/post">создать тему</a></li>
		</ul>
		<?if ($d['num'] > 0) {?>
		<table class="personTalks">
			<tr>
				<th class="theme">Тема, автор и <a href="/chat/theme/<?=$d['theme']['id']?>/page/<?=$d['page']?>/order/<?=($d['order'] != 'cdate_desc'?'cdate_desc':'cdate')?>">дата создания</a></th>
				<th class="rating"><a href="/chat/theme/<?=$d['theme']['id']?>/page/<?=$d['page']?>/order/<?=($d['order'] != 'rating_desc'?'rating_desc':'rating')?>">Рейтинг</a></th>
				<th class="comments"><a href="/chat/theme/<?=$d['theme']['id']?>/page/<?=$d['page']?>/order/<?=($d['order'] != 'comment_desc'?'comment_desc':'comment')?>">Комментарии</a></th>
				<th class="last" style="text-align: left;"><a href="/chat/theme/<?=$d['theme']['id']?>/page/<?=$d['page']?>/order/<?=($d['order'] != 'ldate_desc'?'ldate_desc':'ldate')?>">Последнее сообщение</a></th>
			</tr>
			<?foreach ($d['topics'] as $topic) {?>
			<tr>
				<td class="theme">
					<a class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($topic['author_user_avatara']))?>" /></a>
					<div class="details">
						<h3><a href="/chat/theme/<?=$d['theme']['id']?>/topic/<?=$topic['id']?>"><?=(!empty($topic['name']) ?  $topic['name'] : (substr($topic['content'], 0, 200) . '...'))?></a></h3>
						Автор: <a class="pc-user" rel="nofollow" href="/profile/<?=$topic['author_user_id']?>"><?=$topic['author_user_nick']?></a>, <span class="date"><?=$p['date']->unixtime($topic['cdate'], '%d %F %Y, %H:%i')?></span>
					</div>
				</td>
				<td class="rating"><span class="high"><?=$topic['rating']?></span></td>
				<td class="comments"><span class="new"><?=intval($topic['comment'])?></span></td>
				<td class="last" style="white-space: normal;">
				<?=(intval($topic['last_comment']) > 0 ? ('<span class="date">' . $p['date']->unixtime($topic['ldate'], '%d %F %Y, %H:%i') . '</span><a rel="nofollow" href="/profile/' . $topic['last_msg_user_id'] . '" class="pc-user">' . $topic['last_msg_user_nick'] . '</a></td>'):'&nbsp;')?>
			</tr>
			<?}?>
		</table>
		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
			<?foreach ($p['pager']->make($d['page'], $d['pages'], 10) as $i => $pi) { ?>
				<li>
				<?if (!isset($pi['current'])) {?>
				<a href="/chat/theme/<?=$d['theme']['id']?>/page/<?=$pi['link']?>/order/<?=str_replace(" ", "_", $d['order'])?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
				</li>
			<?}?>
			</ul>
		</div>
		<?} else { ?>
		<strong class="no_info">Пока обсуждений нет. <a href="/chat/theme/<?=$d['theme']['id']?>/post">Создай свое !</a></strong>
	<?}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
