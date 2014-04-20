<div class="irh irhFact">
	<div class="irhContainer">
		<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts" class="replacer">факты</a></p>
		<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts"><?=$d['num_facts']?></a></span>
	</div>
</div>
<ul class="simpleFacts<?=$d['num_facts'] > 0 ? '' : ' empty'?>">
	<?foreach ($p['query']->get('facts', array('person'=>$d['person']['id'], 'enabled'=>1), array('cdate desc'), 0, 2) as $i => $fact) {
		$rel = array_shift($p['query']->get('fact_props', array('fid'=>$fact['id'], 'rubric'=>1), null, null, null));
		$lik = array_shift($p['query']->get('fact_props', array('fid'=>$fact['id'], 'rubric'=>2), null, null, null));
	?>
	<li>
		<p class="entry"><a href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts"><?=$this->limit_text($fact['content'], 300)?></a></p>
		<p class="meta">
			<strong><?=(int) $rel['rating']?>%</strong> верят
			<strong><?=(int) $lik['rating']?>%</strong> нравится
		</p>
	</li>
	<?}?>
	<li class="more">
		<strong>Есть факты? <a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/facts/post">Присылай!</a></strong>
	</li>
</ul>
