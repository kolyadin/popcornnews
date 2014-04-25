<?
if ($d['data']){
   foreach ($d['data'] as $value){
?>
						<dl class="voted">
							<dd><?=($d['info'] ? '<span style="color: #999999;">'.$d['info'].'</span><br />'.$value['name'] : $value['name']);?></dd>
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
?>