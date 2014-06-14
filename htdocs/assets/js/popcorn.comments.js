var IM = {

    sel : {},

    prepare : function(){
        rangy.init();

        this.sel = {
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

        this.sel.editor.focus();


        clone.onmousedown = function (event) {
            event = event || window.event;
            event.preventDefault ? event.preventDefault() : event.returnValue = false;
        }
    },
    /**
     * Сброс формы отправки в изначальное состояние
     */
    resetForm : function(){
        this.sel.wrapper.attr({
            'data-reply': 0,
            'data-level-id': 0
        });

        this.sel.editor.text('');
        this.sel.attachBar.html('');
        this.sel.editorWrapper.removeClass('b-new-message-box__message-container__attach-tab');
    },
    blockForm : function(callback){

        $this = this;

        var handler = function(){
            $this.sel.editor.css('cursor', 'progress').attr('contenteditable', false);
            $this.sel.attachButton.attr('disabled', true);
            $this.sel.sendButton.attr('disabled', true);

            if (typeof callback != 'undefined') {
                callback();
            }
        };

        this.sel.wrapper.stop().animate({opacity: 0.5}, 'fast', handler);
    },
    unblockForm : function(callback){

        $this = this;

        var handler = function(){
            $this.sel.editor.css('cursor', 'default').attr('contenteditable', true);
            $this.sel.attachButton.attr('disabled', false);
            $this.sel.sendButton.attr('disabled', false);

            if (typeof callback != 'undefined') {
                callback();
            }
        };

        this.sel.wrapper.stop().animate({opacity: 1}, 'fast', handler);
    },



    interceptPaste : function(){
        this.sel.editor.on('paste',function(e){

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

    /**
     * Удаление коммента
     */
    removeComment : function(){
        this.sel.comments.on('click', '.comment .comment-delete a', function () {

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
    },
    /**
     * Ответ на коммент
     */
    replyComment : function(){
        this.sel.comments.on('click', '.comment .comment-reply a', function () {
            $this = $(this);

            var commentId = $(this).closest('.b-comment').data('comment-id');

            this.sel.wrapper.fadeOut('fast', function () {
                element.attr('data-reply-to', commentId);
                element.fadeIn('fast');
                $this.closest('.b-comment').append(this.sel.wrapper);

                $('.b-comments__new-comment').show(function(){
                    this.sel.editor.focus();
                });
            });

            return false;
        });
    },
    /**
     * Написание коммента первого уровня
     */
    addComment : function(){
        this.sel.addComment.on('click', function () {
            this.sel.wrapper.attr({
                'data-reply-to': 0,
                'data-level-id': 0
            });

            var par = $(this).parent();

            par.hide();
            par.after(this.sel.wrapper);
            this.sel.wrapper.show(function(){
                messageBox.focus();
            });

            return false;
        });
    },
    addSmile : function(smile){
        var range = getSelectedRangeWithin(this.sel.editor.get(0));

        if (range) {
            pasteSmile(range, smile);
        } else {
            var endRange = placeCaretAtEnd(this.sel.editor.get(0));
            pasteSmile(endRange, smile);
        }
    },
    /**
     * Отправляем коммент
     */
    sendComment : function(){
        this.sel.sendButton.on('click', function () {

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

            this.blockForm();

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

                        this.resetForm();
                        this.unblockForm();
                    });
                }
            };

            $.post('/ajax/comment/send', params, handler, 'json');

        });
    },
    /**
     * Удаление аттача
     */
    removeAttach : function(){
        this.sel.wrapper.on('click', '.b-new-message-box__attach-item', function () {
            $(this).fadeOut('fast', function () {
                $(this).remove();

                if (this.sel.attachBar.find('div').length == 0) {
                    this.sel.editorWrapper.removeClass('b-new-message-box__message-container__attach-tab');
                }
            });
            return false;
        });
    },
    commentLike : function(){
        this.sel.comments.on('click', '.comment .comment-like .like', function () {
            alert('like');
        });
    },
    commentDislike : function(){
        this.sel.comments.on('click', '.comment .comment-like .dislike', function () {
            alert('dislike');
        });
    },
    showSmileBar : function(){

        var $width = this.sel.wrapper.width();
        var $offset = this.sel.wrapper.offset();

        this.sel.smilesBar
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
    closeSmileBar : function(){
        $('.b-smiles-bar__close').on('click', function () {
            $('.b-smiles-bar').fadeOut('fast');
        });
    },
    bind : function(){
        $this = this;

        return {
            uploader  : function(){
                var $this = this;

                var uploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: $this.sel.attachButton.attr('id'),
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

                            $this.unblockForm(function () {
                                $this.sel.attachBar.find('div[data-type=loader]').remove();
                            });

                        },
                        BeforeUpload: function (up, file) {

                            $this.sel.editorWrapper.addClass('b-new-message-box__message-container__attach-tab');

                            $this.blockForm(function () {
                                $this.sel.attachBar.append('<div class="b-new-message-box__attach-item" data-type="loader"><img src="/assets/img/loaders/circle.gif" /></div>');
                            });

                        },

                        FilesAdded: function (up, files) {
                            up.start();
                        },
                        FileUploaded: function (upldir, file, object) {

                            var data = $.parseJSON(object.response);

                            $this.sel.attachBar
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

                    var filesAlready = $this.sel.attachBar.find('div').length;

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
                $this.sel.smileToggle.on('click', function (e) {
                    e.preventDefault();
                    $this.showSmileBar();
                });
            },
            smiles    : function(){
                $this.sel.smilesBar.on('click', '.b-smiles-bar__smiles img', function (e) {
                    $this.addSmile($(this));
                });
            }

        };
    },
    init : function(){

        this.prepare();

        $this = this;

        emojify.run($('.b-comments').get(0));
        emojify.run($('.b-smiles-bar').get(0));

        if (navigator.userAgent.match(/opera/i)) {
            var st;

            $this.sel.editor.on('focus',function () {
                st = setTimeout(function () {
                    $this.sel.editor.blur().focus();
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




        this.bind.uploader();
        this.bind.smilesBar();
        this.bind.smiles();

    }
};

$(function () {
    IM.init();
});