<?
$default = !isset($d['field']['default']) ? '' : $d['field']['default'];

if (is_array($d['editor']) && !empty($d['editor']) && count($d['editor']) == 1) {
	$indx = preg_replace("/(.+::)(.+)/", "\\2", $d['key']);
	$value = $d['editor'][0][$d['key']];
}
elseif (is_array($d['editor']) && count($d['editor']) > 1) {
	$indx = preg_replace("/(.+::)(.+)/", "\\2", $d['key']);
	$indx = preg_replace("/(_\d+)/", "", $indx);
	$value = $d['editor'][$indx];
} else {
	$value = '';
}

if (!isset($d['field']['view_as']) || $d['field']['view_as'] != 'hidden') {
	if ($d['field']['type'] == 'text_field') {
		?>
<div class="FName">
	<h5><?=$d['name']?></h5>
	<script>window.t_fields['<?=$d['key']?>']='<?=$d['key']?>_';</script>
	<table cellspacing="1" width="100%">
		<tr>
			<td class="FInput"><input class="Fcf" type="text" style="width:100%;" name="<?=$d['key']?>" value="<?=($value ? ($value) : $default)?>" onChange="change_pole('<?=$d['key']?>')" onPaste="change_pole('<?=$d['key']?>')" onKeyUp="change_pole('<?=$d['key']?>')" tabindex=1></td>
			<td class="FStat"><input class="Fcfreadonly" type="text" value="" readonly="readonly" title="Количество символов" name="<?=$d['key']?>_"></td>
		</tr>
	</table>
</div>
		<?} elseif ($d['field']['type'] == 'list') {
		$vlist = explode("|", $d['field']['values']);
		if (!isset($d['field']['readonly']) || !$d['field']['readonly']) {?>
<div class="FName">
	<h5><?=$d['name']?></h5>
	<table cellspacing="1" width="100%">
		<tr>
			<td class="FInput">
				<select name="<?=$d['key']?>" class="Fcf" style="width:100%;">
			<? foreach ($vlist as $i =>$val) {
									$vindex = explode(":", $val);
									?>
					<option value="<?=$vindex[0]?>" <?if (trim($value) == $vindex[0]) {?>selected<?}?>><?=$vindex[1]?></option>
				<?}?>
				</select>
			</td>
		</tr>
	</table>
</div>
			<?} else { ?>
			<? foreach ($vlist as $i =>$val) {
				$vindex = explode(":", $val);
				if (trim($value) == $vindex[0]) {
					?>
<div class="FName">
	<h5><?=$d['name']?></h5>
	<input type="text" value="<?=$vindex[1]?>" readonly="true">
</div>
					<?}
			}
		}
	} elseif ($d['field']['type'] == 'dblist') {
		if (isset($d['field']['where'])) {
			$list = $p['query']->get($d['field']['class'], $d['field']['where'], $d['field']['sort'], 0, 500);
		} else {
			$list = $p['query']->get($d['field']['class'], null, $d['field']['sort'], 0, 500);
		}
		?>
<div class="FName">
	<h5><?=$d['name']?></h5>
	<table cellspacing="1" width="100%">
		<tr>
			<td class="FInput">
				<select class="Fcf" style="width:100%;" <?if (isset($d['field']['multi'])) {?>multiple="multiple" size="<?=$d['$field']['multi']?>" name="<?=$d['$key']?>[]"<?} else {?>name="<?=$d['key']?>"<?}?>>
		<?if ($d['field']['null_value']) {?><option value=''></option><?}
							$index = $d['field']['param'];
							if (!isset($d['field']['multi'])) {
								foreach ($list as $i => $val) {
									$vals = explode(',', $d['field']['show_values']);
									$result = $d['field']['mask'];
									foreach ($vals as $indx => $item) {
										$result = str_replace("%" . $indx, $val[$item], $result);
									}
									?>
					<option value="<?=$val[$index]?>" <?if ($value == $val[$index]) {?>selected<?}?>><?=$result?></option>
									<?}
		} else {
								$vals = explode(",", $value);
								$vls = explode(',', $d['field']['show_values']);
								foreach ($list as $i => $val) {
									$result = $d['field']['mask'];
									foreach ($vls as $indx => $item) {
										$result = str_replace("%" . $indx, $val[$item], $result);
										?>
					<option value="<?=$val[$index]?>" <?if (in_array($val[$index], $vals)) {?>selected<?}?>><?=$result?></option>
										<?
									}
								}
		}
							?>
				</select>
			</td>
		</tr>
	</table>
</div>
		<?
	} elseif ($d['field']['type'] == 'checkbox') {
		?>
<div class="FName"><p><input type="checkbox" name="<?=$d['key']?>" value="1" <?if ((!$value&& $default) || $value == '1') {?>checked<?}?>><?=$d['name']?></p></div>
		<?
	}
} else { ?>
<input type="hidden" name="<?=$d['key']?>" value="<?=$value?>">
			<?}?>