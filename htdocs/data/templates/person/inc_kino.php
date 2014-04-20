<div class="irh irhFilmography">
	<div class="irhContainer">
		<p class="header_replacer"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/kino" class="replacer">фильмография</a></p>
		<span class="counter"><a rel="nofollow" href="/persons/<?=$handler->Name2URL($d['person']['eng_name']);?>/kino"><?=$d['num_films']?></a></span>
	</div>
	<div class="filmsAtPerson">
		<?
        $films = $p['query']->get('kino_films', array('person'=>$d['person']['name']), array('cdate desc'), 0, 8);
        foreach ($films as $i => $film) {?>
		<dl class="new">
			<dt>
				<noindex><a<?=$d['rel_other']?> href="http://www.kinoafisha.info/movies/<?=$film['id']?>" rel="nofollow"><?=$film['name']?></a><br />
				<?=$film['orig_name']?>
                </noindex>
			</dt>
			<dd>
				<strong><?=$film['cdate']?></strong>год
			</dd>
		</dl>
		<?}?>
	</div>
</div>