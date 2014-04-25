<?
$this->_render('inc_header',
	array('title'=>'Выиграй встречу с Анджелиной Джоли')
);
?>
<div id="contentWrapper" class="twoCols">
	<div id="content" class="content-contest">
		<div class="work-container">
			<?if ($d['work']['big_image']) {?>
			<img src="<?=$this->getStaticPath('/upload/contest/' . $d['work']['big_image'])?>" alt="" />
			<?} elseif ($d['work']['video']) {?>
			<?=$d['work']['video']?>
			<?}?>
			
			<p><?=$d['work']['description']?></p>
			
			<div class="voted-block">
				<a id="cw_<?=$d['work']['id']?>" class="voted" href="#" onclick="return false;">
					<?=$d['work']['rating'] . ' ' . $p['declension']->get($d['work']['rating'], 'голос', 'голоса', 'голосов')?>
				</a>
				<?if ($this->isModer()) {?>
				<a class="voted" style="top: 25px;" onclick="return confirm('Вы действительно хотите удалить работу?');" href="/contest/delete/work/<?=$d['work']['id']?>">Удалить</a>
				<?}?>

				<img src="<?=$this->getStaticPath($this->getUserAvatar($d['work']['uavatara']))?>" class="ava" alt="" />
				<div class="meta">
					<span class="meta-info"><a rel="nofollow" href="/profile/<?=$d['work']['uid']?>"><?=$d['work']['unick']?></a><?=$p['date']->unixtime($d['work']['regtime'], '%d %F %Y, %H:%i')?></span>
					<?$rating = $p['rating']->_class($d['work']['urating']);?>
					<div class="userRating <?=$rating['class']?>">
						<div class="rating <?=$rating['stars']?>"></div>
						<span><?=$d['work']['urating']?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- new -->
		<div class="contest-list">
			<h2><img src="/i/contest/h2-new-works.gif" alt="" /></h2>
			<ul>
				<?foreach ($d['works_new'] as &$work) {?>
				<li>
					<div class="item">
						<a href="/contest/work/<?=$work['id']?>"><img src="<?=$this->getStaticPath('/upload/contest/' . $work['small_image'])?>" alt="" /></a>
					</div>
					<span><a rel="nofollow" href="/profile/<?=$work['uid']?>"><?=$work['unick']?></a></span>
					<a href="/contest/work/<?=$work['id']?>"><?=$work['rating'] . ' ' . $p['declension']->get($work['rating'], 'голос', 'голоса', 'голосов')?></a>
				</li>
				<?}?>
			</ul>
		</div>
		<!-- \new -->
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>