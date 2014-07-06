/**
 * FashionBattle 1.0
 *
 * @type {{sel: {}, config: {}, eventActive: boolean, prepare: Function, showAuthForm: Function, doVote: Function, bind: Function, init: Function}}
 */
var PostFB = {

    sel: {},
    config: {},
    eventActive: false,

    prepare: function () {
        PostFB.sel = {
            wrapper: $('.b-single-article__battle-persons'),
            voteFirst: $('a[data-action=vote-first]', PostFB.sel.wrapper),
            voteSecond: $('a[data-action=vote-second]', PostFB.sel.wrapper)
        };

        $([PostFB.sel.voteFirst, PostFB.sel.voteSecond]).each(function () {
            $(this).tooltipster({
                theme: '.tooltipster-silver',
                animation: 'swing',
                interactive: false,
                content: '',
                trigger: 'custom'
            });
        });

    },
    showAuthForm: function () {
        var html = '<a href="#" data-action="go-auth" style="font-weight:bold;">Авторизуйтесь</a> или <a href="/register" style="font-weight:bold;">зарегистрируйтесь</a> для того, чтобы голосовать';

        var $vote = $('.b-single-article__battle-persons__vote-count');

        $vote.tooltipster({
            theme: '.tooltipster-silver',
            animation: 'swing',
            interactive: true,
            content: html,
            contentAsHTML: true,
            trigger: 'custom'
        }).tooltipster('show');

        setTimeout(function () {
            $vote.tooltipster('hide');
        }, 5000);
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

            var handlerSuccess = function () {
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

                $obj.tooltipster('content', 'Ваш голос принят').tooltipster('show', function () {
                    setTimeout(function () {
                        $obj.tooltipster('hide', function () {
                            PostFB.eventActive = false;
                        });
                    }, 2000);
                });
            };

            var handlerAlreadyVoted = function () {
                $obj.tooltipster('content', 'Вы уже голосовали').tooltipster('show', function () {
                    setTimeout(function () {
                        $obj.tooltipster('hide', function () {
                            PostFB.eventActive = false;
                        });
                    }, 2000);
                });
            };

            var handlerNeedAuthorization = function () {
                PostFB.showAuthForm();
                PostFB.eventActive = false;
            };

            if (response.status == 'success') {
                handlerSuccess();
            } else if (response.status == 'already_voted') {
                handlerAlreadyVoted();
            } else if (response.status == 'auth_error') {
                handlerNeedAuthorization();
            }

        };

        $.post('/ajax/post/fb/send', params, handler, 'json');
    },
    bind: function () {
        return {
            vote: function () {
                PostFB.sel.voteFirst.on('click', function () {
                    PostFB.doVote(1, this);
                    return false;
                });

                PostFB.sel.voteSecond.on('click', function () {
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