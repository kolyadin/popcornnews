var thisI = 0;

var KaElement = {
	init : function()
	{
		//Добавляем возможность прокрутки элементов с файлами
		$('.file-area').bind('mousewheel', function(event,delta) {
			var pos = $(this).scrollLeft();
			
			if (delta > 0) pos -= 60;
			else           pos += 60; 
			
			$(this).scrollLeft(pos);
			
			event.preventDefault();
		});

		//Добавляем возможность перетаскивания для файлов
		$('.file-area-items').sortable({
			axis : 'x',
			items : 'td',
			grid: [ 20, 0 ],
			opacity : 0.5,
			stop : function(event,ui)
			{
				
			}
		}).disableSelection();

        $('.files-vertical-drag').sortable({
            axis : 'y',
            items : '.file-card',
            grid: [ 0, 30 ],
            opacity : 0.5,
            cursor: 'move',
            stop : function(event,ui)
            {

            }
        }).disableSelection();


        $('.fancybox').fancybox({
            openEffect  : 'elastic',
            closeEffect : 'elastic'
        });

	},
	add : function(type,obj) {


	},
    options : {},
    setOptions : function(name,settings) {
        this.options[name] = settings;
    },
    removeImage : function (name,obj) {

        var options = this.options[name];

        $( "#dialog-confirm" ).dialog({
            resizable: false,
            draggable: false,
            position : {my:'left top', at:'left bottom', of:obj},
            height:220,
            modal: true,
            buttons: {
                'Точно': function() {
                    $(this).dialog( "close" );
                    options.multiple ? $(obj).parents('div.file').remove() : $(obj).parents('.file-area').remove();

                   var parentObject = $(obj).parents('fieldset');

                    if (parentObject.find('.file-area').length)
                    {
                        $('#file_upload_'+name).hide();
                    }
                    else
                    {
                        $('#file_upload_'+name).show();
                    }

                    //options['_file_upload_button'].uploadify('disable', false);
                },
                'Отмена': function() {
                    $(this).dialog( "close" );
                }
            }
        });



        return false;
    },
    confirmCrop : function(name,imageSource,imageId) {

        var options = this.options[name];

        var thumbWidth = options.thumbWidth ? options.thumbWidth : 100;

        var htmlCard = '<div class="file-area"><table class="file-area-items"><tr><td>'+
            '<div class="file">'+
            '<div class="remove"><a href="#" onclick="KaElement.removeImage(\''+options['name']+'\',this);return false;" title="Удалить"><img width="15" src="https://dl.dropbox.com/u/343077/2/1354041989_Remove.png" /></a></div>'+
            '<a href="%s" target="_blank" class="fancybox"><img src="%s" style="max-width:'+ thumbWidth +'px;" /></a>'+
            '</div>'+
        '</td></tr></table></div>';

        var size = [$('#fancyboxImage').css('width'),$('#fancyboxImage').css('height')];

        var box = $('input[name='+ options.name +'_coords]').parents('fieldset');

        $.post(
            '/editor/cropImage'
            ,{ 'coords' : $(sprintf('input[name=%s_coords]',options.name)).val(), 'imageSource' : imageSource, 'size' : size, 'imageId' : imageId }
            ,function(data){
                box.append(sprintf(htmlCard,data['file'],data['file']));


                $('#file_upload_'+name).hide();


                $.modal.close();
            }
            ,'json'
        );
    },
    'InsertOneImage' : function(options) {
        var htmlCard = '<div class="file-area"><table class="file-area-items"><tr><td>'+
            '<div class="file">'+
            '<input type="hidden" name="imageId" value="'+ options['name'] +'_id" />'+
            '<div class="remove"><a href="#" onclick="KaElement.removeImage(\''+options['name']+'\',this);return false;" title="Удалить"><img width="15" src="https://dl.dropbox.com/u/343077/2/1354041989_Remove.png" /></a></div>'+
            '<a href="%s" target="_blank" class="fancybox"><img src="%s" style="max-width:'+ options['thumbWidth'] +'px;" /></a>'+
            '</div>'+
            '</td></tr></table></div>';

        document.write(sprintf(htmlCard
            ,options['file']['source']
            ,options['file']['thumb']
        ));

        $(sprintf('input[name=%s]',options['name'])).val(options['file']['source']);

        $('#file_upload_'+options['name']).hide();


        //$(sprintf('#file_upload_%s', options['name'])).hide();
    },
    'OneImage' : function(options) {
        var htmlCard = ['<td>',
            '<div class="file">',
                '<div class="remove"><a href="#" onclick="removePoster(this);return false;" title="Удалить постер"><img width="15" src="https://dl.dropbox.com/u/343077/2/1354041989_Remove.png" /></a></div>',
                '<a href="%s" class="fancybox"><img src="%s" width="100" /></a>',
            '</div>',
        '</td>'].join('');


        var inputFileName = sprintf('file_upload_%s', options['name']);

        var htmlFile = sprintf('<input type="file" multiple="true" name="%s" id="%s"><div id="file_placeholder_%s"></div>'
            ,options['name']
            ,inputFileName
            ,options['name']
        );

        document.write(htmlFile);

        if (options['crop'])
        {
            document.write( sprintf('<input type="hidden" name="%s_coords" />' , options['name']) );
            document.write( sprintf('<input type="hidden" name="%s" />'        , options['name']) );
        }

        options['_file_upload_button'] = $('#'+inputFileName);

        this.setOptions(options['name'],options);

        $('#'+inputFileName).uploadify({
            'queueID'       : 'file-queue',
            'buttonText'    : (options['buttonValue'] ? options['buttonValue'] : 'Добавить&nbsp;файлы'),
            'buttonClass'   : 'upload-button',
            'height'        : '20',
            'multi'         : false,
            'width'         : (options['buttonLength'] ? options['buttonLength'] : 120),
            'formData'      : {
                'timestamp' : uploadifyTimestamp,
                'token'     : uploadifyToken
            },
            'swf'      : '/editor/uploadify.swf',
            'uploader' : '/editor/uploadImage/',

            'onUploadSuccess' : function(file,dataJSON,response) {
                var json = $.parseJSON(dataJSON);

                var modalHtml = '<div id="cropBox" style="padding:10px;background:#fff;border:2px solid #000;display:inline-block;">' +
                    '<div style="font-size:16px;margin-bottom:10px;">Выберите область</div>' +
                    '<img id="fancyboxImage" style="max-width:800px;max-height:600px;" src="'+json['imageSource']+'">' +
                    '<p class="fancyHint"><input style="font-size:18px;" onclick="KaElement.confirmCrop(\''+ options['name'] +'\',\''+ json['imageSource'] +'\',\''+ json['imageId'] +'\');" type="button" value="Сохранить" />&nbsp;<input style="font-size:18px;" type="button" value="Отменить" onclick="$.modal.close();" /></p></div>';

                var modal = $.modal(modalHtml,{
                    'autoResize' : true,
                    'onShow'     : function(){
                        $('#fancyboxImage').Jcrop({
                            aspectRatio : options['crop'][0]/options['crop'][1],
                            setSelect   : [0,0,options['crop'][0],options['crop'][1]],
                            minSize     : [options['crop'][0],options['crop'][1]],
                            onSelect    : function(c){
                                $("#simplemodal-container").css({
                                    'width'  : $('#cropBox').width()+'px',
                                    'height' : $('#cropBox').height()+'px'
                                });

                                modal.setPosition();

                                var humanCoords = sprintf('%u,%u;%u,%u', c.x, c.y, c.w, c.h);

                                $(sprintf('input[name=%s]',options['name'])).val(json['imageSource']);
                                $(sprintf('input[name=%s_coords]',options['name'])).val(humanCoords);
                            }
                        });
                    }
                });
            }
        });

        this.init();
    },
    'insertFile' : function(options) {

        var htmlCard = '<div class="file-area"><table class="file-area-items"><tr><td>'+
            '<div class="file">'+
            '<input type="hidden" name="%s" value="%s" />'+
            '<div class="remove"><a href="#" onclick="KaElement.removeImage(\''+options['name']+'\',this);return false;" title="Удалить"><img width="15" src="https://dl.dropbox.com/u/343077/2/1354041989_Remove.png" /></a></div>'+
            '<a href="%s" target="_blank" class="fancybox"><img src="%s" style="max-width:'+ options['thumbMaxWidth'] +'px;" /></a>'+
            '</div>'+
            '</td></tr></table></div>';

        document.write(sprintf(htmlCard,options['name'],options['file']['source'],options['file']['source'],options['file']['thumb']));

    },
    file : function(options) {

        var htmlCard = '<td>'+
            '<div class="file">'+
            '<input type="hidden" name="%s" value="%s" />'+
            '<div class="remove"><a href="#" onclick="removePoster(this);return false;" title="Удалить постер"><img width="15" src="https://dl.dropbox.com/u/343077/2/1354041989_Remove.png" /></a></div>'+
            '<a href="%s" class="fancybox"><img src="%s" width="100" /></a>'+
            '</div>'+
        '</td>';


        var htmlThumbVertical = '' +
            '<div class="file-card" style="margin-top:10px;">' +
                '<div style="width:200px;float:left;"><img src="%s" style="max-width:200px;" /></div>' +
                '<div style="width:380px;float:left;margin-left:10px;">' +
                    options['extra'] +
                    '<p><input type="button" class="warning" value="Убрать фотографию" onclick="$(this).parents(\'.file-card\').remove();" /></p>' +
                '</div>' +
                '<div class="clear"></div>' +
            '</div>';

        var inputFileName = sprintf('file_upload_%s', options['name']);

        var htmlFile = sprintf('<input type="file" multiple="true" name="%s" id="%s"><div id="file_placeholder_%s"></div>'
            ,options['name']
            ,inputFileName
            ,options['name']
        );

        document.write(htmlFile);

        if (options.useCrop)
        {
            document.write(sprintf('<input type="hidden" name="%s_coords" />',options['name']));
            document.write(sprintf('<input type="hidden" name="%s" />',options['name']));
        }

        options['_file_upload_button'] = $('#'+inputFileName);

        this.setOptions(options['name'],options);

        if (!options['useCrop'])
        {
            $(sprintf('#file_placeholder_%s',options['name'])).append('<div class="files-vertical-drag" style="margin-top:10px;"></div>');
        }

        $('#'+inputFileName).uploadify({
            'queueID'       : 'file-queue',
            'buttonText'    : (options['title'] ? options['title'] : 'Добавить&nbsp;файлы'),
            'buttonClass'   : 'upload-button',
            'height'        : '20',
            'multi'         : options['multiple'] ? true : false,
            'width'         : (options['width'] ? options['width'] : 120),
            'formData'      : {
                'timestamp' : uploadifyTimestamp,
                'token'     : uploadifyToken
            },
            'swf'      : '/editor/uploadify.swf',
            'uploader' : '/editor/uploadImage/',

            'onUploadSuccess' : function(file,dataJSON,response) {
                var json = $.parseJSON(dataJSON);

                if (options['useCrop'])
                {
                    var modalHtml = '<div id="cropBox" style="padding:10px;background:#fff;border:2px solid #000;display:inline-block;">' +
                        '<div style="font-size:16px;margin-bottom:10px;">Выберите область</div>' +
                        '<img id="fancyboxImage" style="max-width:800px;max-height:600px;" src="'+json['imageSource']+'">' +
                        '<p class="fancyHint"><input style="font-size:18px;" onclick="KaElement.confirmCrop(\''+ options['name'] +'\',\''+ json['imageSource'] +'\');" type="button" value="Сохранить" />&nbsp;<input style="font-size:18px;" type="button" value="Отменить" onclick="$.modal.close();" /></p></div>';

                    var modal = $.modal(modalHtml,{
                        'autoResize' : true,
                        'onShow'     : function(){
                            $('#fancyboxImage').Jcrop({
                                aspectRatio : options['useCrop']['aspectRatio'][0]/options['useCrop']['aspectRatio'][1],
                                setSelect   : [0,0,options['useCrop']['aspectRatio'][0],options['useCrop']['aspectRatio'][1]],
                                minSize     : [options['useCrop']['aspectRatio'][0],options['useCrop']['aspectRatio'][1]],
                                onSelect    : function(c){
                                    $("#simplemodal-container").css({
                                        'width'  : $('#cropBox').width()+'px',
                                        'height' : $('#cropBox').height()+'px'
                                    });

                                    modal.setPosition();

                                    var humanCoords = sprintf('%u,%u;%u,%u', c.x, c.y, c.w, c.h);

                                    $(sprintf('input[name=%s]',options['name'])).val(json['imageSource']);
                                    $(sprintf('input[name=%s_coords]',options['name'])).val(humanCoords);
                                }
                            });
                        }
                    });
                }
                else
                {

                    $(sprintf('#file_placeholder_%s',options['name']))
                        .find('.files-vertical-drag')
                        .append(sprintf(htmlThumbVertical,json['imageSource']))
                        .find('.file-card')
                        .append(sprintf('<input type="hidden" name="%s[%u]" value="%s" />'
                            ,options['name']
                            ,json['imgId']
                            ,json['imageSource']
                        ));
                }
            }
        });

        if (!options['useCrop'])
        {
            $(sprintf('#file_placeholder_%s',options['name'])).append('</div>');
        }

        this.init();
    },
    'SelectElement' : function(options){

        var selectElementHtml = '<p><select name="%s" class="left-select">%s</select><input data-tooltip="Убрать" type="button" value="-" class="simple-button del-one" /></p>';
        var optionSelected    = (typeof options['selectedValue'] != 'undefined') ? options['selectedValue']               : null;
        var optionsHtml       = (typeof options['firstBlank']    != 'undefined') ? '<option value="">-выберите-</option>' : '';

        if (typeof options['jsonUrl'] != 'undefined')
        {
            $.ajax({
                'url' : options['jsonUrl'],
                'cache' : true,
                'async' : false,
                'dataType' : 'json',
                'success' : function(response) {
                    for (x in response)
                    {
                        optionsHtml += sprintf('<option value="%u"%s>%s</option>'
                            ,response[x].id
                            ,optionSelected
                                ? response[x].id == optionSelected
                                    ? ' selected="true"'
                                    : ''
                                : ''
                            ,response[x].name
                        );
                    }
                }
            });
        }

        var finalHtml = sprintf(selectElementHtml, options['name'], optionsHtml);

        if (typeof options['parentContainer'] == 'undefined')
        {
            document.write(finalHtml);
        }
        else
        {
            $(finalHtml).insertBefore(options['parentContainer'].find('p:last'));
        }

        if (typeof options['onAfterInsert'] != 'undefined')
        {
            options['onAfterInsert']();
        }
    },
    'InsertText' : function(options){

        //array attr helper
        var insertAttr = function(array){
            var out = [];

            for (x in array){
                out.push(sprintf('%s=%s ', x, array[x]));
            }

            return out.join(' ');
        }

        //console.log(options['parentObj']);

        var el = '<p><input type="text" data-element-type="text" class="left-text" :attr :autocomplete />:extra :delButton</p>';

        el = el.replace(':attr', typeof options['attr'] != 'undefined' ? insertAttr(options['attr']) : '' );

        el = el.replace(':extra', typeof options['extra'] != 'undefined' ? options['extra'] : '' );

        el = el.replace(':autocomplete', typeof options['autocomplete'] != 'undefined' ? sprintf('data-autocomplete="%s"',options['autocomplete']) : '' );

        el = el.replace(':delButton', typeof options['autocomplete'] != 'undefined' ? '' : '<input data-tooltip="Убрать" type="button" value="-" class="simple-button del-one" />' );

        el = $(el);

        if (typeof options['parentObj'] != 'undefined'){
            el.insertBefore(options['parentObj'].find('p:last'));
        } else{
            document.write('<p>'+el.html()+'</p>');
        }

        //callback
        if (typeof options['afterInsert'] != 'undefined'){

            //alert(options['parentObj']);

            options['afterInsert'](options['parentObj']);

        }

    },
	newTextElement : function(options)
	{
		if (options['type'] == 'textarea')
		{
			thisI++;
			
			var elHtml = sprintf('<p><textarea name="%s[]" id="textarea_%u">%s</textarea>'
				,options['name']
				,thisI
				,(typeof options['value'] == 'undefined') ? '' : options['value']
			);
			
			if (typeof options['obj'] == 'undefined')
			{
				document.write(elHtml);
			}
			else
			{
				$(elHtml).insertBefore(options['obj'].find('p:last'));
			}
			
			CKEDITOR.replace( 'textarea_'+thisI, {
				toolbar : 'toolbarVerySimple',
				height : '100px'
			});
		}
		else if (options['type'] == 'text')
		{
			var elHtml = sprintf('<p><input type="text" name="%s[]" class="left-text" %s %s /><input data-tooltip="Убрать" type="button" value="-" class="simple-button del-one" />'
				,options['name']
				,(typeof options['autocomplete'] == 'undefined')
					? ''
					: sprintf('data-autocomplete="%s"',options['autocomplete'])
				,(typeof options['value'] == 'undefined')
					? ''
					: sprintf('value="%s"',options['value'])
			);
			
			if (typeof options['obj'] == 'undefined')
			{
				document.write(elHtml);
			}
			else
			{
				$(elHtml).insertBefore(options['obj'].find('p:last'));
			}
		}
        else if (options['type'] == 'select')
        {
            var htmlOptions = '';
            var selectedOption = null;

            if (typeof options['selected'] != 'undefined')
            {
                selectedOption = parseInt(options['selected']);
            }

            if (options['first_blank'] == 'true')
            {
                htmlOptions += '<option value="">-выберите-</option>';
            }

            $.ajax({
                'url' : options['json_url'],
                'async' : false,
                'dataType' : 'json',
                'cache'    : true,
                'success' : function(response) {

                    for (x in response)
                    {
                        htmlOptions += sprintf('<option value="%u"%s>%s</option>'
                            ,response[x].id
                            ,response[x].id == selectedOption
                                ? ' selected="true"'
                                : ''
                            ,response[x].name
                        );
                    }
                }
            });

            var elHtml = sprintf('<p><select name="%s[]" class="left-select">%s</select><input data-tooltip="Убрать" type="button" value="-" class="simple-button del-one" />'
                ,options['name']
                ,htmlOptions
            );

            if (typeof options['obj'] == 'undefined')
            {
                document.write(elHtml);
            }
            else
            {
                $(elHtml).insertBefore(options['obj'].find('p:last'));
            }
        }
		
		if (typeof options['success'] != 'undefined')
		{
			options['success']();
		}
	}
};

function confirmFancyCrop()
{
    $.fancybox.close();


}

$(function(){
	var contextArea = $('.admin-panel .content-panel');
	
	var tmpl = [];
	
	tmpl['file-element'] = 
	'<td>'+
		'<div class="file">'+
			'<input type="hidden" name="%s" value="%s" />'+
			'<div class="remove"><a href="#" onclick="removePoster(this);return false;" title="Удалить постер"><img width="15" src="https://dl.dropbox.com/u/343077/2/1354041989_Remove.png" /></a></div>'+
			'<a href="%s" class="fancybox"><img src="%s" width="100" /><img src="%s" style=""/></a>'+
		'</div>'+
	'</td>';
	
	$('.element div[data-element-type=files]',contextArea).each(function(){
		thisI++;
		
		var filesHtml = '<div class="file-area"><table class="file-area-items"><tr></tr></table></div>';
		var localContext = $(this).find('div[data-type=image]');
		var fileContext = $(this);
		
		var boxName = $(this).data('elementName')+'[]';
		
		//Уже загруженные файлы
		if (localContext.length > 0)
		{
			$(this).prepend(filesHtml);
			
			var rowContainer = $(this).find('div.file-area tr');
			
			localContext.each(function(){
				rowContainer
				.prepend(sprintf(tmpl['file-element']
					,boxName
					,$(this).data('fileBig')
					,$(this).attr('data-file-big')
					,$(this).attr('data-file-thumb')
				))
				
				$(this).remove();
			});
		}
		
		KaElement.init();
		
		//Добавляем возможность загрузки файлов
		if (!$(this).find('div.file-area').length)
		{
			$(this).append(filesHtml);
		}
		
		$(this).append('<input type="file" multiple="true" id="file_upload_'+thisI+'">');

		$('#file_upload_'+thisI).uploadify({
			'queueID'  : 'file-queue',
			'buttonText'   : 'Добавить&nbsp;файлы',
			'buttonClass' : 'upload-button',
			'height' : '20',
			'formData'     : {
				'timestamp' : uploadifyTimestamp,
				'token'     : uploadifyToken
			},
			'swf'      : '/editor/uploadify.swf',
			'uploader' : '/editor/uploadify.php',
			'onUploadSuccess' : function(file,dataJSON,response)
			{
				var json = $.parseJSON(dataJSON);

                var modalHtml = '<div id="cropBox" style="padding:10px;background:#fff;border:2px solid #000;display:inline-block;">' +
                    '<div style="font-size:16px;margin-bottom:10px;">Выберите область</div>' +
                    '<img id="fancyboxImage" src="'+json['fileBig']+'">' +
                    '<p class="fancyHint"><input style="font-size:18px;" type="button" value="Сохранить" />&nbsp;<input style="font-size:18px;" type="button" value="Отменить" onclick="$.modal.close();" /></p></div>';

                var modal = $.modal(modalHtml,{
                    autoResize : true,
                    'onShow'     : function(){
                        $('#fancyboxImage').Jcrop({
                            aspectRatio : 645/345,
                            setSelect   : [0,0,645,345],
                            onSelect    : function(c){
                                $("#simplemodal-container").css({
                                    'width'  : $('#cropBox').width()+'px',
                                    'height' : $('#cropBox').height()+'px'
                                });

                                modal.setPosition();

                                var humanCoords = sprintf('%u,%u;%u,%u', c.x, c.y, c.w, c.h);

                                if ($('input[name=main_photo_coords]').length > 0)
                                {
                                    $('input[name=main_photo_coords]').val(humanCoords);
                                }
                                else
                                {
                                    $('#content-form').prepend('<input type="hidden" name="main_photo_coords" value="'+humanCoords+'" />');
                                }
                            }
                        });
                    }
                });


				$('div.file-area tr',fileContext)
				.append(sprintf(tmpl['file-element']
					,boxName
					,json['fileBig']
					,json['fileBig']
					,json['fileThumb']
				));
			},
			'onQueueComplete' : function(queueData)
			{
				KaElement.init();
			}
		});
	});


});