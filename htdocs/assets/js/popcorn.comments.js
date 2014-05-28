
$(function () {


    var messageBox = $('.b-new-message-box__message');
    var defVal = messageBox.attr('data-default-value');

    if (navigator.userAgent.match(/opera/i)) {
        var st;

        messageBox.on('focus',function () {
            st = setTimeout(function () {
                messageBox.blur().focus();
                clearInterval(st);
            }, 10);
        }).focusout(function () {
            clearInterval(st);
        });
    }

    messageBox
        .css('color', '#999')
        .text(defVal)
        .focusin(function () {
            if (!$(this).text() || $(this).text() == defVal) {
                $(this).text('').css('color', '#000');
            }
        })
        .focusout(function () {
            if (!$(this).text()) {
                $(this).text(defVal).css('color', '#999');
            }
        })
    ;

    /**
     * Сброс формы отправки в изначальное состояние
     */
    var resetNewCommentBox = function () {
        $('.b-new-message-box')
            .attr({
                'data-reply': 0,
                'data-level-id': 0
            })
            .find('.b-new-message-box__message').text('Новый комментарий...')
            .end()
            .find('.b-new-message-box__attach').html('')
            .end()
            .find('.b-new-message-box__message-container').removeClass('b-new-message-box__message-container__attach-tab')
        ;
    };

    var unBlockNewCommentEdit = function (callback) {
        $('.b-new-message-box').stop().animate({opacity: 1}, 'fast', function () {
            $(this)
                .find('.b-new-message-box__message').css('cursor', 'default').attr('contenteditable', true)
                .end()
                .find('.b-new-message-box__add-attach').attr('disabled', false)
                .end()
                .find('.b-new-message-box__send-button').attr('disabled', false)
            ;

            if (typeof callback != 'undefined') {
                callback();
            }
        });
    };

    var blockNewCommentEdit = function (callback) {

        $('.b-new-message-box').stop().animate({opacity: 0.5}, 'fast', function () {
            $(this)
                .find('.b-new-message-box__message').css('cursor', 'progress').attr('contenteditable', false)
                .end()
                .find('.b-new-message-box__add-attach').attr('disabled', true)
                .end()
                .find('.b-new-message-box__send-button').attr('disabled', true)
            ;

            if (typeof callback != 'undefined') {
                callback();
            }
        });
    };
    

    $('.b-comments .comment .images img[data-lazy=true]').lazyload();


    $('.b-comments').on('click', '.comment .comment-like .like', function () {
        alert('like');
    });

    $('.b-comments').on('click', '.comment .comment-like .dislike', function () {
        alert('dislike');
    });

    /**
     * Удаление коммента
     */
    $('.b-comments').on('click', '.comment .comment-delete a', function () {

        $this = $(this).closest('.b-comment');

        var params = {
            commentId: $this.attr('data-comment-id'),
            entity: commentSetup['entity'],
            entityId: commentSetup['entityId']
        };

        var handler = function (response) {

            $this.fadeOut('fast', function () {
                var nick = $this.find('a.nick-name').text();

                $this.addClass('b-comment-deleted').html('X&nbsp;<strong>' + nick + '</strong>&mdash;комментарий удален').show();
            });
        };

        $.post('/ajax/comment/delete', params, handler, 'json');

        return false;
    });


    /**
     * Удаление аттача
     */
    $('.b-new-message-box').on('click', '.b-new-message-box__attach-item', function () {
        $(this).fadeOut('fast', function () {
            $(this).remove();

            if ($('.b-new-message-box__attach div').length == 0) {
                $('.b-new-message-box__message-container').removeClass('b-new-message-box__message-container__attach-tab');
            }
        });
        return false;
    });

    /**
     * Написание коммента первого уровня
     */
    $('.b-comments__new-comment a').on('click', function () {
        var element = $('.b-new-message-box');

        element.attr({
            'data-reply-to': 0,
            'data-level-id': 0
        });

        var par = $(this).parent();

        par.hide();
        par.after(element);
        element.show();

        return false;
    });

    /**
     * Ответ на коммент
     */
    $('.b-comments').on('click', '.comment .comment-reply a', function () {
        $this = $(this);

        var commentId = $(this).closest('.b-comment').data('comment-id');
        var element = $('.b-new-message-box');

        element.fadeOut('fast', function () {
            element.attr('data-reply-to', commentId);
            element.fadeIn('fast');
            $this.closest('.b-comment').append(element);

            $('.b-comments__new-comment').show();
        });

        return false;
    });

    /**
     * Отправляем коммент
     */
    $('.b-new-message-box__send-button').on('click', function () {

        var $sendButton = $(this);
        var $commentsBox = $('.b-comments');

        var parent = $sendButton.closest('.b-new-message-box');
        var contentArea = parent.find('.b-new-message-box__message').clone();

        var images = [];

        parent.find('.b-new-message-box__attach-item').each(function () {
            images.push($(this).data('image-id'));
        });
        
        contentArea.find('img').each(function(){
			$(this).replaceWith($(this).attr('title'));
        });

        var params = {
            content: htmlToText(contentArea.html()),
            images: images,
            entity: commentSetup['entity'],
            replyTo: parent.attr('data-reply-to'),
            entityId: commentSetup['entityId']
        };

        $sendButton.addClass('b-new-message-box__send-button__loading').val('');

        blockNewCommentEdit();

        var handler = function (response) {
        	$('.b-smiles-bar').fadeOut('fast');
        	
            if (response.status == 'success') {

                var from = response.replyTo;
                var comments = $('.b-comments ul li');
                var it;
                var current = {};

                comments.each(function (index, element) {
                    if ($(element).data('comment-id') == from) {
                        it = index;

                        current['from'] = index;
                        current['level'] = $(element).data('level');

                    } else if ($(element).data('level') != current['level']) {
                        return false;
                    }
                });

                parent.fadeOut('fast', function () {

                    //Отвечаем на коммент
                    if (params.replyTo > 0) {

                        var $parentComment = $('.b-comment[data-comment-id=' + response.replyTo + ']', $commentsBox);
                        var $lastChild = $parentComment.nextAll('.b-comment[data-reply-to=' + response.replyTo + ']:last');

                        //Мы - первые отвечаем
                        if (!$lastChild.length) {
                            $parentComment.after(response.comment);
                            //Есть еще комменты..
                        } else {
                            var $lastChildChild = $('.b-comment[data-reply-to=' + $lastChild.attr('data-comment-id') + ']:last');

                            //Возможно у последнего найденного коммента есть вложенные
                            if ($lastChildChild.length) {
                                $lastChildChild.after(response.comment);
                                //Последний коммент одинок ;(
                            } else {
                                $lastChild.after(response.comment);
                            }
                        }
                    } else {
                    	//Коммент первого уровня (не отвечаем)
                        $('.b-comments ul').append(response.comment);
                    }
                    
                    var $newComment = $('.b-comment[data-comment-id='+ response.id +']');
                    
                    emojify.run($newComment.get(0));

                    //Коммент вставили, подсветим его (обратим внимание пользователя)
                    $('body,html')
                    .animate({scrollTop:$newComment.offset().top-30},'fast')
                    .promise()
                    .done(function(){
						$newComment.effect('highlight',{color:'#eed8e3'},3500);
                    });

                    
                    $(response.comment).remove();
                    parent.find('.b-new-message-box').html('');

                    if (params.replyTo > 0) {
                        $('.b-comments__new-comment').after(parent);
                    } else {
                        parent.show();
                    }

                    $sendButton.removeClass('b-new-message-box__send-button__loading').val('отправить');
                    resetNewCommentBox();
                    unBlockNewCommentEdit();

                    messageBox.css('color', '#999');
                });
            }
        };

        $.post('/ajax/comment/send', params, handler, 'json');

    });
    
    
    //emojify.run();

    emojify.run($('.b-comments').get(0));
    emojify.run($('.b-smiles-bar').get(0));

    var box=$('.b-new-message-box__message'),
          isAdd=false,
          eventName;

    $('.b-new-message-box__smile-toogle').on('click', function (e) {


        var $smileIcon = $(this);
        var $parentBox = $('.b-new-message-box');

        var $width = $parentBox.width();
        var $offset = $parentBox.offset();

        $('.b-smiles-bar')
              .css({
                  'width': $width,
                  'top': $offset.top - 156 + 'px',
                  'left': $offset.left + 'px'
              })
              .find('.b-smiles-bar__arrow')
              .css('margin-left', $width - 40)
              .end()
              .find('ul li').on('click', function () {
                  if (!$(this).hasClass('b-smiles-bar__close')) {
                      $(this).parent().find('li').removeClass('active');
                      $(this).addClass('active');
                  }
              })
              .end()
              .fadeIn('fast')
        ;

//				$('.b-new-message-box__message').focus();


    });


    $('.b-smiles-bar__close').on('click', function () {
        $('.b-smiles-bar').fadeOut('fast');
    });




    box.on('focus',function(){
        isAdd = true;
    }).on('blur',function(){
        isAdd = false;
    });

    var addSmile = function(el){
        if (window.navigator.pointerEnabled) eventName='onpointerdown';
        else if(window.navigator.msPointerEnabled) eventName='onMSPointerDown';
        else if('ontouchstart' in document.documentElement) eventName='ontouchstart';
        else eventName='onmousedown';


        var sel = rangy.getSelection();
        var range = sel.rangeCount ? sel.getRangeAt(0) : null;
        if (range) {
            range.deleteContents();

            var clone = $(el).clone();

            clone = clone.get(0);

            range.insertNode(clone);
            range.selectNode(clone);
            sel.collapseToEnd();
            range.detach();
            clone.onmousedown = function(event){
                event=event||window.event;
                event.preventDefault ? event.preventDefault() : event.returnValue=false;
            }
        }
    };


    $('.b-smiles-bar').on('mousedown', '.b-smiles-bar__smiles img', function (e) {

        e.preventDefault();

        if (isAdd){
            addSmile(this);
        }

    });

    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'new-message-add-photo',
        container: 'global-search-results',
        max_file_size: '10mb',
        unique_names: true,
        max_file_count: 2,

        url: "/ajax/user-upload",

        flash_swf_url: '/assets/res/plupload-2.1.1/js/Moxie.swf',
        silverlight_xap_url: '/assets/res/plupload-2.1.1/js/Moxie.xap',

        filters: [
            {title: "Image files", extensions: "jpg,jpeg,gif,png"},
        ],

        init: {
            PostInit: function () {

                (new Image()).src = '/assets/img/loaders/circle.gif';
                (new Image()).src = '/assets/img/loaders/input-loader.gif';

            },
            UploadComplete: function (up, files) {

                unBlockNewCommentEdit(function () {
                    $('.b-new-message-box__attach div[data-type=loader]').remove();
                });

            },
            BeforeUpload: function (up, file) {

                $('.b-new-message-box__message-container').addClass('b-new-message-box__message-container__attach-tab');

                blockNewCommentEdit(function () {
                    $('.b-new-message-box__attach').append('<div class="b-new-message-box__attach-item" data-type="loader"><img src="/assets/img/loaders/circle.gif" /></div>');
                });

            },

            FilesAdded: function (up, files) {
                up.start();
            },
            FileUploaded: function (upldir, file, object) {

                var data = $.parseJSON(object.response);

                $('.b-new-message-box__attach')
                    .append('<div class="b-new-message-box__attach-item" data-image-id="' + data.id + '"><img src="' + data.result.url + '" height="30" /></div>')
                    .end()
                    .find('div[data-type=loader]:last').remove()
                ;
            },

            UploadProgress: function (up, file) {

            },

            Error: function (up, err) {

            }
        }

    });

    uploader.bind('FilesAdded', function (up, files) {

        var maxfiles = 10;

        var filesAlready = $('.b-new-message-box__attach div').length;

        if (filesAlready >= maxfiles) {
            return false;
        }

        var est = (files.length + filesAlready) >= maxfiles ? (maxfiles - (files.length + filesAlready)) : maxfiles;

        files.splice(est);

//				if (up.files.length === maxfiles) {
//					$('#uploader_browse').hide("slow"); // provided there is only one #uploader_browse on page
//				}

        up.refresh(); // Reposition Flash/Silverlight
    });

    uploader.init();


});