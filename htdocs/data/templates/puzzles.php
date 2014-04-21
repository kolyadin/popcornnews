<?
$this->_render('inc_header',array('title'=>'Пазлы','header'=>'Все пазлы','top_code'=>'*','header_small'=>''));
?>
			<div id="contentWrapper" class="twoCols">
			<script>
			function open_puz(n){
				var cfg = "height=600,width=700,scrollbars=no,toolbar=no,menubar=no,resizable=yes,location=no,status=no";
				var OpenWindow=window.open("/inc/puzl.php?id="+n, "puznewwin", config=cfg);
			}
			</script>
				<div id="content">
					<h2>Пазлы</h2>
					<ul class="imagesList puzzlesList">
					<?
					    $puzzles = $p['query']->get('puzzles',null,array('id'),null,null);
				    
						foreach ($puzzles as $i => $puzzle){
						    $puzzleImage = (empty($puzzle['small_image']) ? $puzzle['big_image'] : $puzzle['small_image']);
						?>
						<li><a class="img" href="#" onClick="open_puz('<?=str_replace("'", "\'", $puzzle['big_image'])?>'); return false;"><img src="<?=$this->getStaticPath('/upload/_75_75_100_' . $puzzleImage)?>" /></a></li>
						<? if (($i+1)%5==0 && $i!=0) { ?><li class="divider"></li> <?}?>
						<?}
					?>
					</ul>
				</div>
			<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>