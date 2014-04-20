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
                                        <div class="victorina">
                                             <h1><?=$d['data']['name'];?></h1>
                                             <div class="score">
                                                <?if (isset($d['data']['anwser_in_percents'])){?>
                                                <p class="your-score">Твой результат:</p>
                                                <p class="score"><?=$d['data']['score'];?></p>
                                                <?}else{?>
                                                <p class="score" style="font-size: 30px;"><?=$d['data']['score']['name']?></p>
                                                <p class="your-score"><?=$d['data']['score']['description']?></p>
                                                <?}?>
                                             </div>
                                        </div>
				</div>
				<?$this->_render('inc_right_column.twilight');?>
			</div>
<?$this->_render('inc_footer.twilight');?>