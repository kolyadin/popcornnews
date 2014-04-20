<div class="smiles hiddenSmiles hiddenBlock">
	<span class="header"></span>
	<div class="smilesContainer">
		<ul>
<?foreach ($p['smiles']->tpl_smiles as $smile_name => &$smile_file) {?>
			<li><img onclick="return{text: '<?=$smile_name?>'}" hspace="2" vspace="2" border="0" alt="" src="<?=$smile_file?>" /></li>
<?}?>
		</ul>
	</div>
</div>