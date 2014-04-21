<?$this->_render('inc_header.twilight',array('title'=>'Викторины','header'=>$head,'top_code'=>'','header_small'=>''));?>
			<div id="contentWrapper" class="twoCols">
				<div id="content">
				<!--	<div class="fon"></div>-->
					<div class="h-no-shadow">
						<div class="h">
							<h1>
								<span class="logo"><a href="/"></a></span>
								<span class="h1">сага</span>
							</h1>
						</div>
					</div>
                                        <div>
                                           <h1 style="color: #ffffff;"><?=$d['error'];?></h1>
                                        </div>
				</div>
				<?$this->_render('inc_right_column.twilight');?>
			</div>
<?$this->_render('inc_footer.twilight');?>