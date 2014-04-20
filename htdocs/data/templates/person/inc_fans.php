<div class="irh irhFans">
	<div class="irhContainer">
		<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans" class="replacer">поклонники</a></p>
		<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans"><?=$d['num_fans']?></a></span>
	</div>
</div>
<div class="fansContainer equalsContainer">
	<?foreach ($p['query']->get('person_fans', array('gid'=>$d['person']['id']), null, 0, $d['num_facts'] > 0 ? 9 : 3) as $i => $user) {?>
	<dl>
		<dt>
			<a<?=$d['rel_other']?> rel="nofollow" href="/profile/<?=$user['id']?>"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara'], true))?>" /></a>
		</dt>
		<dd><a<?=$d['rel_other']?> rel="nofollow" href="/profile/<?=$user['id']?>"><?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a></dd>
	</dl>
	<?}?>
</div>
<p class="more"><strong>»щешь поклонников <a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/fans/local">в своем городе?</a></strong></p>
