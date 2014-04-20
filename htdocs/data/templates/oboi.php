<?
$this->_render('inc_header',array(
'title'=>'Обои, скачать обои на рабочий стол ',
'header'=>'Обои','top_code'=>'*','header_small'=>'Все обои'));
?>
			<div id="contentWrapper" class="twoCols">
			<script>
			function open_puz(n){
				var cfg = "height=600,width=700,scrollbars=no,toolbar=no,menubar=no,resizable=yes,location=no,status=no";
				var OpenWindow=window.open("/inc/puzl.php?id="+n, "puznewwin", config=cfg);
			}
			</script>
				<div id="content">
					<h2>Обои</h2>
					<ul class="imagesList wpList">
						<?
						  $walls = $p['query']->get('wallpapers',null,array('id'),null,null);
						  foreach ($walls as $i => $wall){
							$img=($wall['img1024'] ? $wall['img1024'] : ($wall['img1280'] ? $wall['img1280'] : $wall['img1600']));
							$wall['site']='pop';
						?>
						<li><a class="img"><img src="<?=$this->getStaticPath($wall['site']=='pop' ? '/upload/_80_80_90_'.$img : '/kinoupload/_80_80_80_'.$img) ?>" /></a>
							<?if (!empty($wall['img1024'])) {?><a target="_blank" href="/wallpapers/<?=$wall['site']?>/<?=$wall['id']?>/1024">1024</a><br><?}?>
							<?if (!empty($wall['img1280'])) {?><a target="_blank" href="/wallpapers/<?=$wall['site']?>/<?=$wall['id']?>/1280">1280</a><br><?}?>
							<?if (!empty($wall['img1600'])) {?><a target="_blank" href="/wallpapers/<?=$wall['site']?>/<?=$wall['id']?>/1600">1600</a><br><?}?>
						</li>
						<? if (($i+1)%5==0 && $i!=0) { ?><li class="divider"></li> <?}?>
						<?}?>
						
					</ul>
				</div>
			<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>