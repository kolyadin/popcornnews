/* ====== Title: Галерея на странице новости ====== */
$(function() {
    isNumber =  function(v){
        return typeof v === 'number' && isFinite(v);
    };
    if ($('.b-single-article__slider-photo-cont').length) {
        slidePhotoInList = function() {
            var currentElem = photoList[currentPhotoInList];
            var newImage = new Image();
            var leftArrow = $('.b-single-article__slider-photo-arrows_left');

            $('.b-single-article__slider-photo-counter-total').text(photoList.length);
            currentPhotoInList == 0 ? leftArrow.hide() : leftArrow.show();
            window.location.hash = parseInt(currentPhotoInList) + 1;

            $('.b-single-article__slider-photo-cont').removeClass('loaded');
            newImage.src = currentElem.img.replace('\/', '/');
            newImage.onLoad = function() {
                var oldImg = $('.b-single-article__slider-photo').find('img:visible');
                var maxContHeight = 0;

                function animateIt () {
                    var tmpImg = $(newImage);
                    tmpImg
                        .css('display', 'none')
                        .appendTo('.b-single-article__slider-photo')
                        .fadeIn(function() {
                            if (currentElem.zoomable) {
                                $('.b-single-article__slider-photo')
                                    .attr('href', newImage.src)
                                    .CloudZoom();

                                $zoomblock = $('<div class="img_zoom show"><img src="/i/zooom.png"></div>');
                                $('.entryImg #wrap').append($zoomblock);
                            } else {
                                $('.b-single-article__slider-photo').removeAttr('href');
                                $('.mousetrap').remove();
                                $('.img_zoom').remove();
                            }

                            $('.b-single-article__slider-photo-cont').addClass('loaded');
                        });

                    $('.b-single-article__slider-photo-counter-current').text(currentPhotoInList+1);
                }

                function doResize() {
                    $('.b-single-article__slider-photo-date').text(currentElem.date);
                    $('.b-single-article__slider-photo-lead').html(currentElem.description);
                    $('.b-single-article__title').text(currentElem.title);
                    $('.b-single-article__slider-photo-source').text(currentElem.source);

                    $('li[class^="b-single-article__tags-item"]').remove();

                    var tags = '<li class="b-single-article__tags-item_'+ currentPhotoInList + '">';
                    for (var i=0; i<currentElem.persons.length; i++) {
                        if ((i==0) && (!$('.newsTrack .newsMeta .tags a').length)) {
                            tags = tags + '<a href="'+ currentElem.persons[i].link +'">'+ currentElem.persons[i].name +'</a>';
                        } else {
                            tags = tags + ', <a href="'+ currentElem.persons[i].link +'">'+ currentElem.persons[i].name +'</a>';
                        }
                    }
                    tags = tags + "</li>";

                    if ($('.b-single-article__tags-list a').length) {
                        $('.b-single-article__tags-list a:last').after( $(tags) );
                    } else {
                        $('.b-single-article__tags-list').append( $(tags) );
                    }
                    if ($('.b-single-article__tags-list a').length) {
                        $('.b-single-article__tags-title').show();
                    } else {
                        $('.b-single-article__tags-title').hide();
                    }

                    if ($('.b-single-article__slider-photo').find('img:visible').length) {
                        $('.b-single-article__slider-photo')
                            .find('img:visible')
                            .fadeOut()
                            .remove();
                        animateIt();
                    } else {
                        animateIt();
                    }
                }
                doResize();
            }(currentElem)
        };

        $('.b-single-article__slider-photo-arrows_left').click(function() {
            currentPhotoInList--;
            slidePhotoInList();
        });

        $('.b-single-article__slider-photo-arrows_right').click(function() {
            currentPhotoInList++;
            if (currentPhotoInList > photoList.length-1) {
                window.location.href="/";
            }
            slidePhotoInList();
        });

        if (isNumber(parseInt(window.location.hash.split('#')[1]))) {
            var hashNumber = parseInt(window.location.hash.split('#')[1]);
            if ( hashNumber <= photoList.length) {
                if (hashNumber < 1) {
                    hashNumber = 1;
                }
                currentPhotoInList = hashNumber - 1;
            }
        }

        slidePhotoInList();
        $('.theresNoJS').remove();
    }
});
