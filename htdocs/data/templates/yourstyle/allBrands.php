<?$this->_render('inc_header', 
    array(
    	'title' => 'your.style - Бренды', 
    	'header' => 'your.style', 
    	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 
    	'header_small' => '', 
    	'css' => 'ys.css?d=26.03.12', 
    	'js' => 'YourStyle.js?d=26.03.12',
        'yourstyleRating' => $d['yourStyleUserRating'],
    )
);?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li class="active"><a href="/yourstyle/brands">бренды</a></li>
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li><a href="/yourstyle/rules">правила</a></li>
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a href="/yourstyle/brands">все бренды</a></li>
			<li><a href="/yourstyle/brands/top">популярные</a></li>
		</ul>
		<h2>Все бренды</h2>

		<div class="b-cols b-cols-3 b-ys-content">
			<?foreach ($d['brandsByNames3Cols'] as $starsByNames) {?>
			<div class="b-cols__col">
				<?foreach ($starsByNames as $letter => $stars) {?>
				<dl class="b-alpha-list b-stars-list">
					<dt class="b-alpha-list__letter"><?=$letter?></dt>
					<?foreach ($stars as $star) {?>
					<dd><a href="/yourstyle/brands/<?=$star['id'];?>"><?=$star['title'];?></a></dd>
					<?}?>
				</dl>
				<?}?>
			</div>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>