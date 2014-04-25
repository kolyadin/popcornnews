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

    /* --------- columnizer --------- */

    $('.celebrities-alphabet').columnize({
        width: 210,
        columns: 3
    });

});


var eventMas = [],
	monthNames = [
		['январь', 'января'],
		['февраль', 'февраля'],
		['март', 'марта'],
		['апрель', 'апреля'],
		['май', 'мая'],
		['июнь', 'июня'],
		['июль', 'июля'],
		['август', 'августа'],
		['сентябрь', 'сентября'],
		['октябрь', 'октября'],
		['ноябрь', 'ноября'],
		['декабрь', 'декабря']
	];

/**
 * Events Calendar API
 * @function
 * @global
 */
var eventCalendar = (function() {
	var currentYear = false, //тек. год
		currentMonth = false; //тек. месяц


	return {
		/**
		 * Инициализация календаря
		 */
		initCalendar: function() {
			currentYear = new Date().getFullYear();
			currentMonth = new Date().getMonth();

			eventCalendar.calendarConstructor();
			eventCalendar.changeMonth(currentMonth);

			/**
			 * кнопки туда-сюда
			 */
			$('.b-calendar__nav__btn:not(.b-calendar__nav__btn_disable)').on('click', function(e) {
				e.preventDefault();

				if ($(this).hasClass('b-calendar__nav__btn_prev')) {
					currentMonth --;
					if (currentMonth < 0) {
						currentMonth = 11;
						currentYear --;
					}
				} else {
					currentMonth ++;
					if (currentMonth > 11) {
						currentMonth = 0;
						currentYear ++;
					}
				}
				$('.b-calendar__day-events-wrap').removeClass('b-calendar__day-events-wrap_shown');
				eventCalendar.calendarConstructor();
				eventCalendar.changeMonth(currentMonth);

			});

		},

		/**
		 * Построение разметки календаря
		 */
		calendarConstructor: function() {
			var daysCount = eventCalendar.getDaysCount(currentYear, currentMonth), //дней в этом месяце
				daysPrevCount = eventCalendar.getDaysCountBefore(currentYear, currentMonth), //дней из предыдущего месяца
				daysNextCount = (7 - (daysCount + daysPrevCount) % 7) % 7, //дней из следующего месяца
				listTemplate = "", //разметка для списка всех дней
				listContainer = $('.b-calendar__list'); //контейнер для вывода списка дней

			for(var daysPrevCounter = 0; daysPrevCounter < daysPrevCount; daysPrevCounter++) {
				listTemplate += '<li class="b-calendar__item b-calendar__item_disabled"></li>';
			}

			for(var daysCounter = 0; daysCounter < daysCount; daysCounter++) {
				listTemplate += '<li class="b-calendar__item b-calendar__item_disabled">' +
					'<div class="b-calendar__item-day">' + (daysCounter + 1) + '</div>' +
				'</li>';

			}

			for(var dayNextCounter = 0; dayNextCounter < daysNextCount; dayNextCounter++) {
				listTemplate += '<li class="b-calendar__item b-calendar__item_disabled"></li>';
			}

			listContainer.html(listTemplate);

			eventCalendar.calendarAppendEvents();
		},

		/**
		 * Добавление событий в ячейки календаря
		 */
		calendarAppendEvents: function() {
			$('.b-calendar__item').each(function(indx) {
				var el = $(this),
					elDate = el.find('.b-calendar__item-day').text();

				//выходные
				if ((indx + 1) % 7 == 0 || (indx + 2) % 7 == 0) {
					el.addClass('b-calendar__item-end')
				}

				//события
				if (elDate.length && eventMas.length > 0) {

					for(var eventsCounter = 0; eventsCounter < eventMas.length; eventsCounter ++ ) {
						if (elDate == eventMas[eventsCounter].day) {
							var dateEventsList = "",
								dateEventsCount = eventMas[eventsCounter].list.length;

							el.removeClass('b-calendar__item_disabled');
							el.append('<ul class="b-calendar__item-events"></ul>');

							var dateEventsContainer = el.find('.b-calendar__item-events');

							//если событий до 4 (включ.) - выводим все
							if (dateEventsCount > 0 && dateEventsCount <= 4 ) {
								for (var dateEventsCounter = 0; dateEventsCounter < dateEventsCount; dateEventsCounter ++ ) {
									dateEventsList += '<li class="b-calendar__item-events__elem"><img src="'+ eventMas[eventsCounter].list[dateEventsCounter].img + '" alt=""/></li>';
								}
								dateEventsContainer.addClass('b-calendar__item-events_' + dateEventsCount);
							//если событий больше - выводим 3 + ссылку с количеством
							} else if (dateEventsCount > 0 && dateEventsCount > 4) {
								for (var dateEventsCounterElse = 0; dateEventsCounterElse < 3; dateEventsCounterElse ++ ) {
									dateEventsList += '<li class="b-calendar__item-events__elem"><img src="'+ eventMas[eventsCounter].list[dateEventsCounterElse].img + '" alt=""/></li>';
								}
								dateEventsList += '<li class="b-calendar__item-events__elem b-calendar__item-events_more-num">+' + (dateEventsCount - 3) + '</li>'
								dateEventsContainer.addClass('b-calendar__item-events_more');
							}
							dateEventsContainer.append(dateEventsList);
							el.attr('data-event-id', eventsCounter);
						}
					}
				}
			});

			/**
			 * клик по дню с событиями
			 */
			$('.b-calendar__item:not(.b-calendar__item_disabled)').on('click', function(e) {
				e.preventDefault();
				if (!$(this).hasClass('b-calendar__item_active')) {
					var elId = $(this).attr('data-event-id');
					eventCalendar.showEventsList($(this), elId);
				}
			});
		},

		/**
		 * Выводит разметку для выпадающего списка событий
		 * @param {object} dayCell объект ячейки с датой
		 * @param {number} elId индекс события в массиве событий на месяц
		 */
		showEventsList: function(dayCell, elId) {
			var eventsListHtml = "",
				eventPopup = $('.b-calendar__day-events-wrap');

			for (var dayEventsCounter = 0; dayEventsCounter < eventMas[elId].list.length; dayEventsCounter ++) {
				var dayEventImg = "",
					dayEventPlace = "",
					dayEventText = "";

				if (typeof eventMas[elId].list[dayEventsCounter].img !== 'undefined') {
					dayEventImg = '<div class="b-calendar__day-events__item-img"><img src="' + eventMas[elId].list[dayEventsCounter].img + '" alt=""/></div>'
				}

				if (typeof eventMas[elId].list[dayEventsCounter].place !== 'undefined') {
					dayEventPlace = '<div class="b-calendar__day-events__item-place">' + eventMas[elId].list[dayEventsCounter].place + '</div>'
				}

				if (typeof eventMas[elId].list[dayEventsCounter].name !== 'undefined') {
					dayEventText = '<div class="b-calendar__day-events__item-name">' + eventMas[elId].list[dayEventsCounter].name + '</div>'
				}

				eventsListHtml += '<li class="b-calendar__day-events__item">' +
					dayEventImg +
					'<div class="b-calendar__day-events__item-text">' + dayEventPlace + dayEventText + '</div>' +
				'</li>';
			}

			eventPopup.find('.b-calendar__day-events__list').html(eventsListHtml);
			$('.b-calendar__day-events__date-num').text(eventMas[elId].day);
			$('.b-calendar__day-events__date-month').text(monthNames[currentMonth][1]);
			var eventPopupHeight = eventPopup.outerHeight();

			eventPopup.removeClass('b-calendar__day-events-wrap_shown');

			if ($('.b-calendar__item_active').length) {
				$('.b-calendar__item_active').animate(
					{'marginBottom': 0}, 300, function() {
						var eventPopupTop = dayCell.offset().top + dayCell.height();
						$(this).removeClass('b-calendar__item_active');
						dayCell.addClass('b-calendar__item_active')
							.animate({'marginBottom': eventPopupHeight}, 300, function() {
								eventPopup.offset({'top': eventPopupTop})
									.addClass('b-calendar__day-events-wrap_shown');
							});
					}
				)

			} else {
				var eventPopupTop = dayCell.offset().top + dayCell.height();
				dayCell.addClass('b-calendar__item_active')
					.animate({'marginBottom': eventPopupHeight}, 300, function() {
						eventPopup.offset({'top': eventPopupTop})
							.addClass('b-calendar__day-events-wrap_shown');
					});
			}

		},

		/** Изменение названия месяца на кнопках
		 * @param {number} curMonth текущ. месяц
		 */
		changeMonth: function(curMonth) {
			var currentMonthName = monthNames[curMonth][0],
				nextMonth = curMonth + 1,
				prevMonth = curMonth - 1;

			nextMonth > 11 ? nextMonth = 0 : false;
			prevMonth < 0 ? prevMonth = 11 : false;

			var nextMonthName = monthNames[nextMonth][0],
				prevMonthName = monthNames[prevMonth][0];

			$('.b-calendar__nav__now').text(currentMonthName);
			$('.b-calendar__nav__btn_prev .b-calendar__nav__btn-name').text(prevMonthName);
			$('.b-calendar__nav__btn_next .b-calendar__nav__btn-name').text(nextMonthName);
		},

		/** Определяет кол-во дней в месяце
		 * @param {number} year искомый год
		 * @param {number} month искомый месяц
		 * @returns {number} кол-во дней в месяце
		 */
		getDaysCount: function(year, month) {
			return 33 - new Date(year, month, 33).getDate();
		},

		/** Возвращает кол-во дней из предыдущего месяца
		 * @param {number} year текущий год
		 * @param {number} month текущий месяц
		 * @returns {number}
		 */
		getDaysCountBefore: function(year, month) {
			var prevDays = new Date(year, month, 1).getDay() - 1;
			prevDays < 0 ? prevDays = 6 : false;
			return prevDays;
		}
	}
})();

$(function() {
	eventCalendar.initCalendar();



});
