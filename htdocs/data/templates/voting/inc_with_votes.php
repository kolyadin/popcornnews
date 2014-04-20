<?
if ($d['data']){
   foreach ($d['data'] as $value){
?>
						<dl class="voty" id="<?=$value['id'];?>">
							<dd><?=$value['name'];?></dd>
<?
	foreach ($value['voting'] as $vals){
?>
							<dt class="votes<?=$int;?>">
								<a class="decor-voty" href="<?=$this->getStaticPath('/upload/_395_500_90_' . $vals['picture'])?>" title="<?=$vals['id'];?>"><img src="/i/0.gif" /></a>
								<div class="ava">
								   <img class="ava" alt="" src="<?=$this->getStaticPath('/upload/_115_145_90_' . $vals['picture'])?>" />
								</div>
								<span class="name"><?=$vals['name'];?></span>
								<a class="vote" href="javascript: void(0)" onclick="year_results_vote(<?=$vals['id'];?>, <?=$value['id'];?>);">
								   <img src="/i/vote-button.jpg" />
								</a>
							</dt>
<?
	}
?>
						</dl>
<?
   }
}
?>