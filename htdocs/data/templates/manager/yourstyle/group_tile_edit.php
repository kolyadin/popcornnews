<?=$this->_render('inc_header');?>
<script type="text/javascript">
$(document).ready(function(){
	//console.log($('input.priority'));
    regenPriority();	

	$('a.up').click(function(){
		var clr = ($(this).parent().attr('id')).replace('color_', '');

		var elem = $('input[value=' + clr + ']');
		if(elem != []) {
			var prev = elem.prev();
			if(prev != []) {
				prev.before(elem);
				regenPriority();
			}
		}
	return false;
	});
	
	$('a.down').click(function(){
		var clr = ($(this).parent().attr('id')).replace('color_', '');

		var elem = $('input[value=' + clr + ']');
		if(elem != []) {
			var next = elem.next();
			if(next != []) {
				next.after(elem);
				regenPriority();
			}
		}
		
		return false;
	});

	$('input:checkbox').click(function(){
		var clr = $(this).parent().attr('id').replace('color_', '');
		if($(this).attr('checked')) {
			$('input[name^=priority]:last').after('<input type="hidden" name="priority[]" value="' + clr + '" class="priority" x-webkit-speech="">');
		} else {
			$('input[value=' + clr + ']').remove();
		}
		regenPriority();
	});
});

function regenPriority() {
	var current_last = null;
	$('a.up, a.down').show();
	$('div[id^=color]:not(:has(input:checked)) a.up').hide();
	$('div[id^=color]:not(:has(input:checked)) a.down').hide();
	$('input.priority').each(function(id, item){
		var elem = $('#color_' + $(item).val());
		if(id == 0) {
			var first = $('div[id^=color]:first');
			if(first.attr('id') != elem.attr('id')) {
			    first.before(elem);
			}			
			current_last = elem;
			$('a.up',elem).hide();
		} else {
			current_last.after(elem);
		}
		current_last = elem;
	});
	$('div[id^=color]:has(input:checked):last a.down').hide();
}
</script>
<form method="post">
	<input type="hidden" name="type" value="yourstyle" />
	<input type="hidden" name="action" value="editGroupsTile" />
	<input type="hidden" name="tid" value="<?=$d['tile']['id']?>" />
	<input type="hidden" name="referer" value="<?=!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null?>" />

	<table cellspacing="1" class="TableFiles">
		<tr>
			<td class="TFHeader">Бренд</td>
			<td><input type="text" name="brand" value="<?=$d['tile']['brand']?>" /></td>			
		</tr>
		<tr>
			<td class="TFHeader">Превью</td>
			<td><img src="<?=$p['ys']::getWwwUploadTilesPath($d['tile']['gid'], $d['tile']['image'], '300x300')?>" alt="" /></td>
		</tr>
		<tr>
			<td class="TFHeader">Описание</td>
			<td><input type="text" name="description" value="<?=$d['tile']['description']?>" /></td>
		</tr>
		<tr>
			<td class="TFHeader">Группа</td>
			<td>
				<select name="gid">
					<?foreach ($d['rootGroups'] as $rootGroup) {?>
					<optgroup label="<?=$rootGroup['title']?>">
						<?foreach ($rootGroup['groups'] as $group) {?>
						<option value="<?=$group['id']?>"<?=$group['id'] == $d['tile']['gid'] ? ' selected="selected"' : null?>><?=$group['title']?></option>
						<?}?>
					</optgroup>
					<?}?>
				</select>
			</td>
		</tr>
		<tr>
		    <td class="TFHeader">Цвета</td>
		    <td><?php
		    foreach ($d['colors'] as $hex => $c) {
		        echo '<div id="color_'.str_replace('#', '', $hex).'">';
		        echo '<a href="#" class="up">+</a>&nbsp;<a href="#" class="down">-</a>&nbsp;&nbsp;';
		        echo '<span style="display:inline-block;width:16px;height:16px;background-color:'.$hex.'">&nbsp;</span>';
		        echo '&nbsp;<input type="checkbox" name="colors['.$hex.']" id="colors['.$hex.']"'.($c['have'] ? ' checked="checked"' : '').' />';
		        echo '&nbsp;<label for="colors['.$hex.']">'.$c['ru'].'</label>';
		        echo '</div>';
		    }
		    foreach ($d['tileColors'] as $c) {
		        $c['color'] = str_replace('#', '', $c['color']);
		        echo '<input type="hidden" name="priority[]" value="'.$c['color'].'" class="priority" />';
		    }
		    ?>
		    <a href="?type=yourstyle&action=editGroupsTile&gen=true&tid=<?=$d['tile']['id'];?>" id="detect_colors">определить цвета</a>
		    </td>
		</tr>
		<tr>
			<td class="TFHeader"><input type="submit" value="Отправить" /></td>
		</tr>
	</table>
</form>
<?=$this->_render('inc_footer');?>