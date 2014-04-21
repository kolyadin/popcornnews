<?
$poll = $p['query']->get_query(sprintf('SELECT * FROM %s WHERE goods_id = 66 AND page_id = 2 AND pole31 != "" ORDER BY id DESC', TBL_GOODS_));
$poll = $poll[0];
if (!empty($poll)) {
?>
<div class="sbDiv irh irhPoll">
	<div class="sbDiv irh" id="poll">
		<p class="header_replacer">опрос
			<span class="replacer"></span>
		</p>
		<div id="error"></div>
		<div id="questions">
			<p class="discuss_header"><?=$poll['name']?></p>
			<form name="poll" method="post" action="" onsubmit="return poll_submit(this);">
				<input type="hidden" name="id" value="<?=$poll['id']?>" />
				<ul class="poll">
					<?
					foreach ($poll as $key => $value) {
						if (!empty($value) && substr($key, 4) > 0 && substr($key, 4) < 30) {
							printf('<li><label><input type="radio" name="anwser" value="%d" />%s</label></li>' . "\n", substr($key, 4), $value);
						}
					}
					?>
				</ul>
				<input type="submit" class="submit" />
			</form>
		</div>
	</div>
</div>
<?}?>