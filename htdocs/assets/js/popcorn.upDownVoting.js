var upDownVoting = {
    config : {},

    doVote : function($vote){

        $target = $vote.closest(upDownVoting.config.element);

        if ($target.hasClass('animation-on')){
            return false;
        }

        $target.addClass('animation-on');

        var textDefault = $target.find('.text').attr('data-default-text');
        var action = $vote.attr('data-action');

        var params = {
            entityId : $target.attr(upDownVoting.config.id),
            vote     : action
        };

        var handler = function(response){

            if (response.status == 'success'){

                $target.find('.b-right-stats .col, .b-right-stats .text').animate({ opacity: 0 },'fast',function(){

                    $target.find('.col').text(response.points);

                    if (action == 'vote-up'){
                        $target.find('.btn_minus').addClass('btn_disable');
                    }else if (action == 'vote-down'){
                        $target.find('.btn_plus').addClass('btn_disable');
                    }

                    $target
                        .find('.text')
                        .text('Спасибо, ваш голос принят!')
                        .animate({ opacity: 1 },'fast',function(){
                            $this = $(this);
                            setTimeout(function(){
                                $this.text(response.pointsOverall);
                                $target.removeClass('animation-on');
                            },2000);
                        })
                    ;

                    $(this).animate({ opacity: 1 },'fast');
                });

            }else{

                $target.find('.b-right-stats .text').animate({ opacity: 0 },'fast',function(){

                    $target.find('.text').html(response.exception.message);

                    $(this).animate({ opacity: 1 },'fast',function(){

                        setTimeout(function(){
                            $target.find('.text').animate({ opacity: 0 },'fast',function(){
                                $(this).html(textDefault);

                                $(this).animate({ opacity: 1 },'fast',function(){
                                    $target.removeClass('animation-on');
                                });
                            });
                        },2000);

                    });
                });
            }

        };

        $.post(upDownVoting.config.url,params,handler,'json');
    },

    bind : function(){
        return {
            buttons : function(){
                $('a[data-action=vote-up],a[data-action=vote-down]',upDownVoting.config.element).on('click',function(){
                    upDownVoting.doVote($(this));
                    return false;
                });
            }
        };
    },
    init : function(config){
        upDownVoting.config = config;

        upDownVoting.bind().buttons();
    }
};