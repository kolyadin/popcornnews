<?$this->_render('inc_header', 
    array(
    	'title' => 'your.style - Правила', 
    	'header' => 'your.style', 
    	'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . $d['cuser']['nick'] . '" class="avaProfile">', 
    	'header_small' => '', 
    	'css' => 'ys.css?d=26.03.12', 
    	'js' => 'YourStyle.js?d=26.03.12',
        'yourstyleRating' => $d['yourStyleUserRating'],
    ));    
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a href="/yourstyle">your.style</a></li>
			<li><a href="/yourstyle/sets">сеты</a></li>
			<li><a href="/yourstyle/groups">вещи</a></li>
			<li><a href="/yourstyle/stars">звезды</a></li>
			<li><a href="/yourstyle/brands">бренды</a></li>
			<!--<li><a href="/yourstyle/editor" class="newWindow" target="_blank">создать</a></li>-->
			<li><a href="/yourstyle/editor" target="_blank">создать</a></li>
			<li class="active"><a href="/yourstyle/rules">правила</a></li>
		</ul>

		<div>
			<h2>Правила</h2>
			<div class="simpleText rules">
			    <ul>
			        <li>В разделы с одеждой можно добавлять только фотографии вещей на белом фоне. Добавлять фотографии с посторонними изображениями, надписями и т. д. запрещено.<br /><br /></li>
		    	    <li>Обязательно указывать бренд той вещи, которую вы хотите добавить. Вещи без бренда или с подписью «Не знаю», «На каждый день», «Вечеринка» и т. д. будут удаляться.<br /><br /></li>
	    		    <li>Каждая вещь должна добавляться в соответствующий раздел: платья — к платьям, туфли — к туфлям, сумки — к сумкам и т. д.<br /><br /></li>
    			    <li>Если вы хотите добавить в свой сет картинку, которая не соответствует вышеуказанным правилам, необходимо поставить галочку напротив строки «Не публиковать». Так картинка будет доступна вам, но не попадет в общий список вещей.</li>
			    </ul>
			</div>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>