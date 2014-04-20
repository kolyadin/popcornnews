<div class="irh irhFanfic">
	<div class="irhContainer">
		<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics" class="replacer">фанфики</a></p>
		<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics"><?=$d['num_fanfics']?></a></span>
	</div>
</div>
<ul class="simpleFanfics">
	<?foreach ($p['query']->get('fanfics', array('person'=>$d['person']['id']), array('time_create'), 0, 1) as $i => $fanfic) {?>
	<li>
		<p class="entry"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fanfics"><?=$this->limit_text($fanfic['announce'])?><span class="counter"> (<?=$fanfic['num_comments']?>)</span></a></p>
		<div class="meta">
			<div class="rating">
		       Понравилось?
				<span class="like">Да (<?=$fanfic['num_like'];?>)</span>
				<span class="dislike">Нет (<?=$fanfic['num_dislike'];?>)</span>
			</div>
			<span class="date">
		       Автор:
				<a style="font-weight: bold;" rel="nofollow" href="/profile/<?=$fanfic['user_id']?>" class="pc-user"><?=htmlspecialchars($fanfic['user_nick'], ENT_IGNORE, 'cp1251', false);?></a>,
				<noindex><?=$p['date']->unixtime(strtotime($fanfic['time_create']), '%Y %F %d, %H:%i');?></noindex>
			</span>
		</div>
	</li>
	<?}?>
</ul>