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
		<?if (isset($d['error'])) {?><h1><?=$d['error'];?></h1><?}?>
		<div id="list">
			<?
			foreach ($d['data'] as $key => $value) {
				?>
			<div class="victorina">
				<h1>
					<a href="/vote/<?=$value['id'];?>">
							<?=$value['name'];?>
					</a>
				</h1>
				<div class="vote">
					<table>
							<?
							foreach ($value as $field => $val) {
								if (!empty($val) && strpos($field, '_percent') == false && substr($field, 4) > 0 && substr($field, 4) < 30) {
									?>
						<tr>
							<td class="percent"><?=$value[$field.'_percent'];?></td>
							<td>
								<div>
									<span><?=$val;?></span>
									<div class="voting" style="width: <?=$value[$field.'_percent'];?>%;"></div>
								</div>
							</td>
						</tr>
									<?
								}
							}
							?>
					</table>
				</div>
			</div>
				<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column.twilight');?>
</div>
<?$this->_render('inc_footer.twilight');?>