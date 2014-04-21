/* ====== Title: Галерея стоп-кадров ====== */
$(function() {
    var gallery = $('.b-freeze-frame');
    if (gallery.length) {
        freezePhotoInList = function() {
            var elem = freezeList[currentFreezeInList],
                arrows = gallery.find('.b-freeze-frame__title-arrows-cont');

            window.location.hash = parseInt(currentFreezeInList) + 1;
            if (freezeList.length - 2 < 0) {
                arrows.hide();
            } else {
                arrows.show();
            }

            //работа над показом списка картинок
            function photoListShow(galleryPhotos) {
                var galLeft = gallery.find('.b-freeze-frame__gallery ul').offset().left,
                    listWidth = gallery.find('.b-freeze-frame__gallery ul li:last').offset().left + gallery.find('.b-freeze-frame__gallery ul li').outerWidth(true) - galLeft,
                    galWidth = gallery.find('.b-freeze-frame__gallery ul').width(),
                    pinkArrow = gallery.find('.b-freeze-frame__gallery-link');

                if (listWidth - galWidth > 0) {
                    pinkArrow.show();
                } else {
                    pinkArrow.hide();
                }

                function seeLiPosition() {
                    gallery.find('.b-freeze-frame__gallery ul li').each(function () {
                        var liPosition = $(this).offset().left;
                        if (galLeft + galWidth - liPosition > 0) {
                            $(this).fadeTo(300,1).addClass('shown');
                        } else {
                            $(this).fadeTo(0,0).addClass('hide');
                        }
                    });
                }

                seeLiPosition();

                $('.b-freeze-frame__gallery-link').on('click', function () {
                    gallery.find('.b-freeze-frame__gallery .shown').each(function () {
                        $(this).stop(true, true);
                        $(this).fadeOut(300, function () {
                            $(this).remove();
                        });
                    });

                    //задержка чтобы успели удалиться предыдущие картинки
                    setTimeout(function () {
                        gallery.find('.b-freeze-frame__gallery ul .hide').each(function () {
                            var liPosition = $(this).offset().left,
                                galLeft = gallery.find('.b-freeze-frame__gallery ul').offset().left,
                                galWidth = gallery.find('.b-freeze-frame__gallery ul').width();
                            if (galLeft + galWidth - liPosition > 0) {
                                $(this).removeClass('hide');
                                $(this).addClass('shown');

                            } else {
                                $(this).addClass('hide');
                            }
                        });
                        if (gallery.find('.b-freeze-frame__gallery .hide').length) {
                            gallery.find('.b-freeze-frame__gallery ul .shown').stop(true, true);
                            gallery.find('.b-freeze-frame__gallery ul .shown').fadeTo(300, 1);
                        }  else {
                            gallery.find('.b-freeze-frame__gallery ul').append(galleryPhotos);
                            seeLiPosition();
                        }
                    }, 310);
                    return false;
                });
            }

            function animateIt (galleryPhotos) {
                gallery.find('.b-freeze-frame__gallery ul').append(galleryPhotos);
                photoListShow(galleryPhotos);
                gallery.find('.b-freeze-frame__gallery').fadeTo(300, 1);
            }

            function doChange() {
                gallery.find('.b-freeze-frame__news-title').fadeOut(300, function () {
                    $(this).text(elem.header);
                    $(this).fadeIn(300);
                });
                var galleryPhotos = '';
                for (var i = 0; i < elem.photos.length; i++) {
                    galleryPhotos = galleryPhotos + '<li><a href="'+ elem.photos[i].link +'"><img src="'+ elem.photos[i].img.replace('\/', '/') +'"></a></li>';
                }

                if (gallery.find('.b-freeze-frame__gallery li:visible').length) {
                    gallery.find('.b-freeze-frame__gallery').fadeTo(300, 0, function (){
                        $(this).find('li').remove();
                        animateIt(galleryPhotos);
                    });
                } else {
                    animateIt(galleryPhotos);
                }

                if (gallery.find('.b-freeze-frame__gallery li').length) {
                    gallery.find('.b-freeze-frame__gallery p').remove();
                } else {
                    gallery.find('.b-freeze-frame__gallery').append('<p>Нет фото в галерее</p>');
                }
            }
            doChange();
        };

        gallery.find('.b-title-arrows__arrow_left').click(function() {
            currentFreezeInList--;
            if (currentFreezeInList < 0) {
                currentFreezeInList = freezeList.length - 1;
            }
            freezePhotoInList();
        });

        gallery.find('.b-title-arrows__arrow_right').click(function() {
            currentFreezeInList++;
            if (currentFreezeInList > freezeList.length-1) {
                currentFreezeInList = 0;
            }
            freezePhotoInList();
        });

        if (isNumber(parseInt(window.location.hash.split('#')[1]))) {
            var hashNumber = parseInt(window.location.hash.split('#')[1]);
            if ( hashNumber <= freezeList.length) {
                if (hashNumber < 1) {
                    hashNumber = 1;
                }
                currentFreezeInList = hashNumber - 1;
            }
        }
        freezePhotoInList();
    }
});
