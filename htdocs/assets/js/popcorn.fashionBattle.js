var PostFB = {

    sel: {},
    config: {},
    eventActive: false,

    prepare: function () {
        PostFB.sel = {
            wrapper: $('.b-single-article__battle-persons')
        };
    },
    doVote: function (option, obj) {

        if (PostFB.eventActive) {
            return false;
        }

        PostFB.eventActive = true;

        var $obj = $(obj);

        var params = {
            fbId: PostFB.config.fbId,
            option: option
        };

        var handler = function (response) {

            if (response.status == 'success') {

                $('.b-single-article__battle-persons__vote-count').html(response['pointsOverall']);

                var $rating = $('.b-single-article__battle-rating');
                var $ratingFirst = $rating.find('.b-single-article__battle-person_left');
                var $ratingSecond = $rating.find('.b-single-article__battle-person_right');

                $ratingFirst.find('span').text(response.firstVotes).end().animate({width: response.firstPercent + '%'}, 'fast');
                $ratingSecond.find('span').text(response.secondVotes).end().animate({width: response.secondPercent + '%'}, 'fast');

                if (response.firstVotes > response.secondVotes) {
                    $ratingSecond.removeClass('b-single-article__battle-person_best');
                    $ratingFirst.addClass('b-single-article__battle-person_best');
                } else if (response.secondVotes > response.firstVotes) {
                    $ratingFirst.removeClass('b-single-article__battle-person_best');
                    $ratingSecond.addClass('b-single-article__battle-person_best');
                } else {
                    $ratingFirst.removeClass('b-single-article__battle-person_best');
                    $ratingSecond.removeClass('b-single-article__battle-person_best');
                }

                $obj.tooltipster({
                    theme: '.tooltipster-pink',
                    animation: 'swing',
                    interactive: false,
                    content: 'Ваш голос принят',
                    trigger: 'custom'
                }).tooltipster('show', function () {
                    setTimeout(function () {
                        $obj.tooltipster('hide', function () {
                            PostFB.eventActive = false;
                        });
                    }, 2000);
                });
            } else if (response.status == 'error') {
                $obj.tooltipster({
                    theme: '.tooltipster-pink',
                    animation: 'swing',
                    interactive: false,
                    content: response.exception.message,
                    trigger: 'custom'
                }).tooltipster('show', function () {
                    setTimeout(function () {
                        $obj.tooltipster('hide', function () {
                            PostFB.eventActive = false;
                        });
                    }, 2000);
                });
            }


        };

        $.post('/ajax/post/fb/send', params, handler, 'json');
    },
    bind: function () {
        return {
            vote: function () {
                PostFB.sel.wrapper.find('a[data-action=vote-first]').on('click', function () {
                    PostFB.doVote(1, this);
                    return false;
                });

                PostFB.sel.wrapper.find('a[data-action=vote-second]').on('click', function () {
                    PostFB.doVote(2, this);
                    return false;
                });
            }

        };
    },
    init: function (config) {

        PostFB.config = config;
        PostFB.prepare();

        PostFB.bind().vote();
    }
};