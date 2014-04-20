<?
$this->_render('inc_header', array('title'=>$d['info']['name'], 'header'=>$d['info']['name'], 'top_code'=>'*', 'header_small'=>''));?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<div class="simpleText">
			<p><?=$d['info']['content']?></p>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>