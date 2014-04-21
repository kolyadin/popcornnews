<div class="update">
	<?=$d['item']['title']?>
</div>
<dl>
	<dt><a rel="nofollow" href="/profile/<?=$d['item']['userInfo']['id']?>"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['item']['userInfo']['avatara']))?>" /></a></dt>
	<dd>
		<a class="article" href="/community/group/<?=$d['item']['gid']?>/album/<?=$d['item']['id']?>"><span>Новый альбом</span> «<?=$d['item']['title']?>»</a>
		<?$rating = $p['rating']->_class($d['item']['userInfo']['rating']);?>
		<div class="userRating <?=$rating['class']?>" title="<?=$d['item']['userInfo']['rating']?>">
			<div class="rating <?=$rating['stars']?>"></div>
			<span><?=$rating['name']?></span>
		</div>
		<a class="nick" rel="nofollow" href="/profile/<?=$d['item']['userInfo']['id']?>"><?=$d['item']['userInfo']['nick']?></a><?=$p['date']->unixtime($d['item']['createtime'], '%d %F %Y, %H:%i')?>
	</dd>
</dl>