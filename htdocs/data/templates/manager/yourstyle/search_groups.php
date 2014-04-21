<?=$this->_render('inc_header');?>
<table cellspacing="1" class="TableFiles">
	<tr>
		<td class="TFHeader">Название</td>
	</tr>
	<?foreach ($d['groups'] as $group) {?>
	<tr>
		<td><a href="?type=yourstyle&action=groupsTiles&gid=<?=$group['id']?>"><?=$group['title']?></a></td>
	</tr>
	<?}?>
</table>
<?=$this->_render('inc_footer');?>