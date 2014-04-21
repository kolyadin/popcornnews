function removePoster(obj)
{
	$( "#dialog-confirm" ).dialog({
		resizable: false,
		draggable: false,
        position : {my:'left top', at:'left bottom', of:obj},
		height:220,
		modal: true,
		buttons: {
			'Точно': function() {
				$(this).dialog( "close" );
				$(obj).parents('div.file').parent().remove();
			},
			'Отмена': function() {
				$(this).dialog( "close" );
			}
		}
	});
	
	return false;
}



$(function(){
	if ($('#editor1').length)
	{
		CKEDITOR.replace( 'editor1', {
			filebrowserBrowseUrl: '/browser/browse.php?type=Images',
			filebrowserUploadUrl: '/uploader/upload.php?type=Files'
		});
	}
});
