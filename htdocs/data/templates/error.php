<? $this->_render('inc_header',array('title'=>$d['error']['title'],'header'=>$d['error']['title'],'top_code'=>'','header_small'=>''));?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
					<div class="systemMessage">
						<h4><?=$d['error']['title']?></h4>
						<p><?=$d['error']['msg']?></p>
						<? if (!empty($d['error']['link'])) {?>
							<br><?=$d['error']['link']?>
						<?}?>
					</div>	
				</div>
				<?$this->_render('inc_right_column');?>
			</div>
<?$this->_render('inc_footer');?>