<div class="update">
	<?foreach ($d['item']['images'] as $image) {?>
	<a href="/community/group/<?=$d['item']['gid']?>/album/<?=$d['item']['aid']?>/#img<?=$image['id']?>"><img class="photo" alt="" src="<?=$this->getStaticPath(Community::getWWWAlbumPhotoPath($d['item']['aid'], $image['image']))?>" /></a>
	<?}?>
</div>
<dl>
	<dt><a rel="nofollow" href="/profile/<?=$d['item']['userInfo']['id']?>"><img alt="" src="<?=$this->getStaticPath($this->getUserAvatar($d['item']['userInfo']['avatara']))?>" /></a></dt>
	<dd>
		<a class="article" href="/community/group/<?=$d['item']['gid']?>/album/<?=$d['item']['aid']?>/"><span>ֲ אכüבמלו</span> «<?=$d['item']['albumInfo']['title']?>»</a>
		<?$rating = $p['rating']->_class($d['item']['userInfo']['rating']);?>
		<div class="userRating <?=$rating['class']?>" title="<?=$d['item']['userInfo']['rating']?>">
			<div class="rating <?=$rating['stars']?>"></div>
			<span><?=$rating['name']?></span>
		</div>
		<a class="nick" rel="nofollow" href="/profile/<?=$d['item']['userInfo']['id']?>"><?=htmlspecialchars($d['item']['userInfo']['nick'], ENT_IGNORE, 'cp1251', false);?></a><?=$p['date']->unixtime($d['item']['createtime'], '%d %F %Y, %H:%i')?>
	</dd>
</dl>