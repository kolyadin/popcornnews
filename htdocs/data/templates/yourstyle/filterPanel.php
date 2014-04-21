<?php 
//var_dump($d['filters']);
$sel = ' selected="selected"';
$f = $d['filters'];
$rgDefault = ($f['rootGroup'] == 0) ? $sel : '';
$rgCurrent = $f['rootGroup'];

$grDefault = ($f['group'] == 0 && $f['rootGroup'] == 0) ? $sel : '';
$grRootDefault = ($f['group'] == 0 && $f['rootGroup'] != 0) ? $sel : '';
$groupCurrent = $f['group'];

$brandDefault = ($f['brand'] == 0) ? $sel : '';
$brandCurrent = $f['brand'];

$colorDefault = ($f['color'] == 0) ? $sel : '';
$colorCurrent = $f['color'];

$action_path = isset($d['action_path']) ? $d['action_path'] : '/yourstyle/tiles/';
?>
<form id="ys_filter" class="ys_filter" action="<?=$action_path;?>" method="get" autocomplete="off">
    <label class="independent">
        <span class="h">Группа</span>
        <select name="rootGroup">
            <option value="0"<?=$rgDefault;?>>Все</option>
            <?php foreach ($d['allGroups'] as $rg) {
                $selected = ($rgCurrent == $rg['id']) ? $sel : '';
            ?>
                <option value="<?=$rg['id'];?>"<?=$selected;?>><?=$rg['title'];?></option>
            <?php } ?>
        </select>
    </label>
    <label class="dependent">
        <span class="h">Подгруппа</span>
        <select name="group">
            <optgroup label="Все">
                <option value="0"<?=$grDefault;?>>Все</option>
            </optgroup>
            <?php foreach ($d['allGroups'] as $rg) { ?>
            <optgroup label="<?=$rg['title'];?>">
            	<option value="0"<?=$grRootDefault;?>>Все</option>
            	<?php foreach ($rg['groups'] as $group) {
            	    $selected = ($group['id'] == $groupCurrent && $rg['id'] == $rgCurrent) ? $sel : '';
            	?>
            	<option value="<?=$group['id'];?>"<?=$selected;?>><?=$group['title'];?></option>
            	<?php } ?>
            </optgroup>
            <?php } ?>
        </select>
    </label>
    <label>
        <span class="h">Бренд</span>
        <select name="brand">
            <option value="0"<?=$brandDefault;?>>Все</option>
            <?php foreach ($d['brands'] as $brand) {
                $selected = ($brand['id'] == $brandCurrent) ? $sel : ''; 
            ?>
            <option value="<?=$brand['id'];?>"<?=$selected;?>><?=$brand['title'];?></option>
            <?php } ?>
        </select>
    </label>
    <label>
        <span class="h">Цвет</span>
        <select name="color">
            <option value="0"<?=$colorDefault;?>>Все</option>
            <?php foreach ($d['colors'] as $color) {
                $selected = ($color['en'] == $colorCurrent) ? $sel : ''; 
            ?>
            <option value="<?=$color['en'];?>"<?=$selected;?>><?=$color['ru'];?></option>
            <?php } ?>
        </select>
    </label>
    <label class="send">
        <span class="h">&nbsp;</span>
        <input type="submit" value="Выбрать" />
    </label>
</form>
<script type="text/javascript" src="/js/depSelect.js"></script>
<script type="text/javascript">new DepSelect({id: 'ys_filter'});</script>
