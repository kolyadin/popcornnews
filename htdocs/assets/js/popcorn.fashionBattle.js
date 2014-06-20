var PostFB = {

    sel: {},
    config: {},

    prepare: function () {
        PostFB.sel = {
            wrapper: $('.b-single-article__battle-persons')
        };
    },
    doVote: function(option){

        var params = {
            fbId: PostFB.config.fbId,
            option: option
        };

        var handler = function(){

        };

        $.post('/ajax/post/fb/send',params,handler,'json');
    },
    bind: function () {
        return {
            vote: function () {
                PostFB.sel.wrapper.find('a[data-action=vote-first]').on('click', function () {
                    PostFB.doVote(1);
                    return false;
                });

                PostFB.sel.wrapper.find('a[data-action=vote-second]').on('click', function () {
                    PostFB.doVote(2);
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