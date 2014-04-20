<div class="irh irhRShip">
	<div class="irhContainer">
		<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/links" class="replacer">связи</a></p>
		<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/links"><?=$d['num_links']?></a></span>
	</div>
</div>
<div class="groupsContainer equalsContainer">
	<?
    $limits = ($d['num_films'] == 0) ? 6 : ($d['num_films'] > 3) ? 6: 3;
    $links = $p['query']->get('links', array('person'=> $d['person']['id']), null, 0, $limits,
                                array('a.pole1', 'a.pole2'), false, true);
    foreach ($links as $i => $link) {
		$link_id = $link['person1'] == $d['person']['id'] ? $link['person2'] : $link['person1'];
		$person = array_shift($p['query']->get('persons', array('id'=>$link_id), null, 0, 1));
	?>
	<dl>
		<dt><a<?=$d['rel_other']?> href="/persons/<?=$handler->Name2URL($person['eng_name']);?>"><?if ($person['main_photo']) {?><img alt="<?=$person['name']?>" src="<?=$this->getStaticPath('/upload/_100_100_80_' . $person['main_photo'])?>" /><?}?></a></dt>
		<dd><a<?=$d['rel_other']?> href="/persons/<?=$handler->Name2URL($person['eng_name']);?>"><?=$person['name']?></a></dd>
	</dl>
	<?if (($i + 1) % ($d['num_films'] > 0 ? 3 : 6) == 0) {?><div class="divider"></div><?}?>
	<?}?>
</div>