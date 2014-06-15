var IM = {

    sel : {},
    config : {},

    prepare : function(){
        rangy.init();

        IM.sel = {
            comments      : $('.b-comments'),
            addComment    : $('.b-comments__new-comment').find('a'),
            wrapper       : $('.b-new-message-box'),
            editor        : $('.b-new-message-box__message',this.sel.wrapper),
            attachBar     : $('.b-new-message-box__attach',this.sel.wrapper),
            attachButton  : $('.b-new-message-box__add-attach',this.sel.wrapper),
            editorWrapper : $('.b-new-message-box__message-container',this.sel.wrapper),
            sendButton    : $('.b-new-message-box__send-button',this.sel.wrapper),
            smileToggle   : $('.b-new-message-box__smile-toogle',this.sel.wrapper),
            smilesBar     : $('.b-smiles-bar')
        };
    },

    generateEmoji : function(){
        emojify.run(this.sel.comments.get(0));
        emojify.run(this.sel.smilesBar.get(0));
    },

    placeCaretAtEnd : function(el){
        el.focus();

        var range = rangy.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = rangy.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);

        return range;
    },
    getSelectedRangeWithin : function(el){
        var selectedRange = null;
        var sel = rangy.getSelection();
        var elRange = rangy.createRange();
        elRange.selectNodeContents(el);
        if (sel.rangeCount) {
            selectedRange = sel.getRangeAt(0).intersection(elRange);
            return selectedRange;
        }
        elRange.detach();
    },
    pasteSmile : function(range, smile){
        range.deleteContents();

        var clone = smile.clone().get(0);
        var sel = rangy.getSelection();

        range.collapse(false);
        range.insertNode(clone);
        range.collapseAfter(clone);
        sel.setSingleRange(range);

        IM.sel.editor.focus();

        clone.onmousedown = function (event) {
            event = event || window.event;
            event.preventDefault ? event.preventDefault() : event.returnValue = false;
        }
    },
    /**
     * Сброс формы отправки в изначальное состояние
     */
    resetForm : function(){
        IM.sel.wrapper.attr({
            'data-reply': 0,
            'data-level-id': 0
        });

        IM.sel.editor.text('');
        IM.sel.attachBar.html('');
        IM.sel.editorWrapper.removeClass('b-new-message-box__message-container__attach-tab');
    },
    blockForm : function(callback){

        var handler = function(){
            IM.sel.editor.css('cursor', 'progress').attr('contenteditable', false);
            IM.sel.attachButton.attr('disabled', true);
            IM.sel.sendButton.attr('disabled', true);

            if (typeof callback != 'undefined') {
                callback();
            }
        };

        IM.sel.wrapper.stop().animate({opacity: 0.5}, 'fast', handler);
    },
    unblockForm : function(callback){

        var handler = function(){
            IM.sel.editor.css('cursor', 'default').attr('contenteditable', true);
            IM.sel.attachButton.attr('disabled', false);
            IM.sel.sendButton.attr('disabled', false);

            if (typeof callback != 'undefined') {
                callback();
            }
        };

        IM.sel.wrapper.stop().animate({opacity: 1}, 'fast', handler);
    },

    removeComment : function(comment){

        var params = {
            commentId : comment.attr('data-comment-id'),
            entity    : IM.config['entity'],
            entityId  : IM.config['entityId']
        };

        var handler = function (response) {

            comment.fadeOut('fast', function () {
                var nick = comment.find('a.nick-name').text();

                comment.addClass('b-comment-deleted').html('X&nbsp;<strong>' + nick + '</strong>&mdash;комментарий удален').show();
            });
        };

        $.post('/ajax/comment/remove', params, handler, 'json');
    },

    /**
     * Ответ на коммент
     */
    replyComment : function($comment){

        var commentId = $comment.closest('.b-comment').data('comment-id');

        IM.sel.wrapper.fadeOut(30,function () {
            IM.sel.wrapper.attr('data-reply-to', commentId);
            IM.sel.wrapper.fadeIn('fast');
            $comment.closest('.b-comment').append(IM.sel.wrapper);

            setTimeout(function(){
                IM.sel.editor.focus();
            },1);

            $('.b-comments__new-comment').show(function(){

            });
        });


    },
    /**
     * Написание коммента первого уровня
     */
    addComment : function(){

    },
    addSmile : function(smile){
        var range = this.getSelectedRangeWithin(IM.sel.editor.get(0));

        if (range) {
            IM.pasteSmile(range, smile);
        } else {
            var endRange = IM.placeCaretAtEnd(IM.sel.editor.get(0));
            IM.pasteSmile(endRange, smile);
        }
    },
    commentLike : function(){
        IM.sel.comments.on('click', '.comment .comment-like .like', function () {
            alert('like');
        });
    },
    commentDislike : function(){
        IM.sel.comments.on('click', '.comment .comment-like .dislike', function () {
            alert('dislike');
        });
    },
    showSmilesBar : function(){

        var $width = IM.sel.wrapper.width();
        var $offset = IM.sel.wrapper.offset();

        IM.sel.smilesBar
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
            .fadeIn('fast');
    },
    closeSmilesBar : function(){
        IM.sel.smilesBar.fadeOut('fast');
    },
    bind : function(){

        return {
            uploader  : function(){
                var uploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: IM.sel.attachButton.attr('id'),
                    container: 'global-search-results',
                    max_file_size: '10mb',
                    unique_names: true,
                    max_file_count: 2,

                    url: "/ajax/user-upload",

                    flash_swf_url: '/assets/res/plupload-2.1.1/js/Moxie.swf',
                    silverlight_xap_url: '/assets/res/plupload-2.1.1/js/Moxie.xap',

                    filters: [
                        {title: "Image files", extensions: "jpg,jpeg,gif,png"}
                    ],

                    init: {
                        PostInit: function () {

                            (new Image()).src = '/assets/img/loaders/circle.gif';
                            (new Image()).src = '/assets/img/loaders/input-loader.gif';

                        },
                        UploadComplete: function (up, files) {

                            IM.unblockForm(function () {
                                IM.sel.attachBar.find('div[data-type=loader]').remove();
                            });

                        },
                        BeforeUpload: function (up, file) {

                            IM.sel.editorWrapper.addClass('b-new-message-box__message-container__attach-tab');

                            IM.blockForm(function () {
                                IM.sel.attachBar.append('<div class="b-new-message-box__attach-item" data-type="loader"><img src="/assets/img/loaders/circle.gif" /></div>');
                            });

                        },

                        FilesAdded: function (up, files) {
                            up.start();
                        },
                        FileUploaded: function (upldir, file, object) {

                            var data = $.parseJSON(object.response);

                            IM.sel.attachBar
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

                    var filesAlready = IM.sel.attachBar.find('div').length;

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
            },
            smilesBar : function(){
                IM.sel.smileToggle.on('click', function () {
                    IM.showSmilesBar();
                    return false;
                });

                $('.b-smiles-bar__close').on('click', function () {
                    IM.closeSmilesBar();
                    return false;
                });
            },
            smiles    : function(){
                IM.sel.smilesBar.on('click', '.b-smiles-bar__smiles img', function (e) {
                    IM.addSmile($(this));
                });
            },
            interceptPaste : function(){
                IM.sel.editor.on('paste',function(e){

                    e.preventDefault();

                    var text;
                    var clp = (e.originalEvent || e).clipboardData;
                    if (clp === undefined || clp === null) {
                        text = window.clipboardData.getData("text") || "";
                        if (text !== "") {
                            if (window.getSelection) {
                                var newNode = document.createElement("span");
                                newNode.innerHTML = text;
                                window.getSelection().getRangeAt(0).insertNode(newNode);
                            } else {
                                document.selection.createRange().pasteHTML(text);
                            }
                        }
                    } else {
                        text = clp.getData('text/plain') || "";
                        if (text !== "") {
                            document.execCommand('insertText', false, text);
                        }
                    }
                });
            },
            addComment : function(){
                IM.sel.addComment.on('click', function () {
                    IM.sel.wrapper.attr({
                        'data-reply-to': 0,
                        'data-level-id': 0
                    });

                    var par = $(this).parent();

                    par.hide();
                    par.after(IM.sel.wrapper);
                    IM.sel.wrapper.show(function(){
                        IM.sel.editor.focus();
                    });

                    return false;
                });
            },
            removeComment : function(){

                IM.sel.comments.on('click', '.comment .comment-delete a', function () {

                    $comment = $(this).closest('.b-comment');

                    IM.removeComment($comment);

                    return false;
                });
            },
            replyComment : function(){
                IM.sel.comments.on('click', '.comment .comment-reply a', function () {
                    IM.replyComment($(this));

                    return false;
                });
            },
            sendComment : function(){
                IM.sel.sendButton.on('click', function () {

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
                        entity: IM.config['entity'],
                        replyTo: parent.attr('data-reply-to'),
                        entityId: IM.config['entityId']
                    };

                    $sendButton.addClass('b-new-message-box__send-button__loading').val('');

                    IM.blockForm();

                    var handler = function (response) {
                        IM.sel.smilesBar.fadeOut('fast');

                        if (response.status == 'success') {

                            var from = response.replyTo;
                            var comments = IM.sel.comments.find('ul li');
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
                                    IM.sel.comments.find('ul').append(response.comment);
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

                                IM.resetForm();
                                IM.unblockForm();
                            });
                        }
                    };

                    $.post('/ajax/comment/send', params, handler, 'json');

                });
            },



            removeAttach : function(){
                IM.sel.wrapper.on('click', '.b-new-message-box__attach-item', function () {
                    $(this).fadeOut('fast', function () {
                        $(this).remove();

                        if (IM.sel.attachBar.find('div').length == 0) {
                            IM.sel.editorWrapper.removeClass('b-new-message-box__message-container__attach-tab');
                        }
                    });
                    return false;
                });
            }

        };
    },
    init : function(config){

        IM.config = config;
        IM.prepare();

        if (navigator.userAgent.match(/opera/i)) {
            var st;

            IM.sel.editor.on('focus',function () {
                st = setTimeout(function () {
                    IM.sel.editor.blur().focus();
                    clearInterval(st);
                }, 10);
            }).focusout(function () {
                clearInterval(st);
            });
        }

        try {
            document.execCommand("MultipleSelection", null, true);
        } catch (ex) {
        }

        IM.bind().uploader();
        IM.bind().smilesBar();
        IM.bind().smiles();
        IM.bind().interceptPaste();

        IM.bind().addComment();
        IM.bind().removeComment();
        IM.bind().replyComment();
        IM.bind().sendComment();
        IM.bind().removeAttach();

    }
};