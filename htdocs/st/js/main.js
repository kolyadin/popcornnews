$(document).ready(function() {
    /* ====== Title: Галерея на главной странице  ====== */
    /* $('.b-top-corn__short-news-container[data-large-photo]').each(function(){
        (new Image()).src = $(this).attr('data-large-photo');
    });

    $('.b-top-corn__short-news li > div')
        .on('mouseenter',function() {
            var lp = $('.b-top-corn__large-photo img'),
            cur = $(this);
            $('.b-top-corn__short-news-container').removeClass('b-top-corn__short-news-container_active');
            cur.addClass('b-top-corn__short-news-container_active');

            if (cur.attr('data-large-photo') !== undefined && cur.attr('data-large-photo') != lp.attr('src')) {
                lp.fadeOut('fast',function(){
                    lp.attr('src',cur.attr('data-large-photo'));
                    lp.fadeIn('fast');
                });
            }
        })
        .on('mouseleave',function(){
            var lp = $('.b-top-corn__large-photo img');
            $('.b-top-corn__short-news-container').removeClass('b-top-corn__short-news-container_active');

            lp.fadeOut('fast',function(){
                lp.attr('src',lp.attr('data-default-img'));
                lp.fadeIn('fast');
            });
        });     */
    function showHoverSlide(elemId) {
        $('.b-top-corn__large-news').stop(true, true);
        $('.b-top-corn__large-news_active').fadeOut(300, function () {
            $(this).removeClass('b-top-corn__large-news_active');
            $(elemId).fadeIn(300, function () {
                $(this).addClass('b-top-corn__large-news_active');
            });
        });
    }

    $('.b-top-corn__short-news > li')
        .on('mouseenter',function() {
            var elem = $(this),
                url = elem.attr('data-url'),//ToDo: вместо этих url вставить адрес нужного файла, атрибуты  data-url тогда не понадобятся, их удалить
                photoBlockId = 'b-top-corn__large-news_' + elem.index();

            if ($('#' + photoBlockId).length) {
                if (!$('#' + photoBlockId).hasClass('b-top-corn__large-news_active')) {
                    showHoverSlide('#' + photoBlockId);
                }
            } else {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (data) {
                        var newBlock = '<div class="b-top-corn__large-news clearfix" id="' + photoBlockId + '"></div>';
                        $('.b-top-corn__large').append(newBlock);
                        $('#' + photoBlockId).append(data);
                        showHoverSlide('#' + photoBlockId);
                    }
                });
            }
            $('.b-top-corn__short-news-container_active').removeClass('b-top-corn__short-news-container_active');
            elem.find('.b-top-corn__short-news-container').addClass('b-top-corn__short-news-container_active');
        })
        .on('mouseleave',function(){

        });

    $('.b-single-article__my-comment__smiles-open a').on('click', function () {
        var changeClassBlock = $(this).closest('.b-single-article__my-comment__smiles');
        changeClassBlock.toggleClass('b-single-article__my-comment__smiles_hidden');
        if (changeClassBlock.hasClass('b-single-article__my-comment__smiles_hidden')) {
            $(this).text('Показать смайлы');
        } else {
            $(this).text('Скрыть смайлы');
        }
        return false;
    });

    /* ====== Title: Social block  ====== */
    function socialsPosition() {
        var socials = $('.b-socials'),
            socialsHeight = socials.find('.b-socials__fixed').height(),
            orientalBlock = $('h1.b-single-article__title'),
            position = orientalBlock.offset().top + orientalBlock.outerHeight(true),
            windowHeight = $(window).height();

        if (windowHeight - socialsHeight < 0) {
            position = 0;
        } else if (position + socialsHeight - windowHeight > 0) {
            position = windowHeight - socialsHeight;
        }
        socials.offset({top: position});
    }

    if ($('.b-socials').length) {
        socialsPosition();
    }

    $(window).resize(function () {
        if ($('.b-socials').length) {
            socialsPosition();
        }
    });
});