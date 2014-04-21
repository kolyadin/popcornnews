<?$this->_render('../inc_header',
                 array(
                      'title' => 'Итоги 2012 года попкорнnews',
                      'header' => 'Итоги 2012 года попкорнnews'));?>
			<div id="contentWrapper" class="twoCols">
				
				<div id="content">
				<!--voting of the year-->
					<div class="voty" id="voty">
						<p class="h">В преддверии нового года мы хотели бы подвести итоги уходящего. И выяснить, кто по мнению наших пользователей достоин звания самых-самых. Выберите кандидата в каждой категории, нажав на кнопку «голосовать» под фотографией. Чтобы увеличить изображение, просто кликните на него.
Итоги будут подведены в конце декабря. Каждый пользователь может голосовать один раз в день.</p>
<?
if ($d['data']){
	foreach ($d['data'] as $value) {
		if (!$value['is_voted']) {
?>
						<dl class="voty" id="<?=$value['id'];?>">
							<dd><?=$value['name'];?></dd>
<?
			foreach ($value['voting'] as $vals){
                $hack = '';
                if($vals['id'] == 139092) {
                    $hack = ' style="font-size:11px;" ';
                }
?>
							<dt class="votes<?=$int;?>">
								<a class="decor-voty" href="<?=$this->getStaticPath('/upload/_395_500_90_' . $vals['picture'])?>" title="<?=$vals['id'];?>"><img src="/i/0.gif" /></a>
								<div class="ava">
								   <img class="ava" src="<?=$this->getStaticPath('/upload/_115_145_90_' . $vals['picture'])?>" />
								</div>
								<span class="name"<?=$hack;?>><?=$vals['name'];?></span>
								<a class="vote" href="javascript: void(0)" onclick="year_results_vote(<?=$vals['id'];?>, <?=$value['id'];?>);">
								   <img src="/i/vote-button.jpg" />
								</a>
							</dt>
<?
			}
?>
						</dl>
<?
		} else {
?>
						<dl class="voted">
							<dd><?=$value['name'];?></dd>
<?
	$int = 100;
	foreach ($value['voting'] as $vals){
?>
							<dt class="votes<?=$int;?>">
								<a class="decor-voty" href="<?=$this->getStaticPath('/upload/_395_500_90_' . $vals['picture'])?>" title="<?=$vals['id'];?>"><img src="/i/0.gif" /></a>
								<div class="ava">
								   <img class="ava" src="<?=$this->getStaticPath('/upload/_115_145_90_' . $vals['picture'])?>" />
								</div>
								<span class="name"><?=$vals['name'];?></span>
								<div class="votes"><?=round($value['per_vote']*$vals['votes'], 1);?>% голосов</div>
							</dt>
<?
	   $int -= 20;
	}
	unset($int);
?>
						</dl>
<?
		}
	}
}
?>
					</div>
				<!--\\voting of the year\\-->
				</div>
				<script type="text/javascript" src="/js/popup.js"></script>
<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>