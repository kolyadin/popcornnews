<?php

$article = $d['article'];

$photos = $article->getPhotos();

$photosData = array();

foreach($photos as $photo) {
    $path =  ($photo->isZoomable()) ?
        $this->getStaticPath($photo->getOriginalPath())
        : $this->getStaticPath($photo->getPhotoPathBySize('500x700'));
    $date = $p['date']->unixtime($photo->getDate(), '%d %F');

    $photoPersons = $photo->getPersons();
    $ids = array();
    foreach($photoPersons as $person) {
        $ids[] = $person['personId'];
    }
    $photoPersonsInfo = $p['query']->get('persons', array('ids' => implode(',', $ids)));

    $photoPersons = array();

    foreach($photoPersonsInfo as $item) {
        $link = $item['eng_name'];
        $link = str_replace('-', '_', $link);
        $link = str_replace('&dash;', '_', $link);
        $link = '/persons/'.str_replace(' ', '-', $link);
        $photoPersons[] = array(
            'name' => $item['name'],
            'link' => $link
        );
    }

    $photosData[] = array(
        'img'         => $path,
        'description' => $photo->getDescription(),
        'date'        => $date,
        'zoomable'    => $photo->isZoomable(),
        'title'       => $photo->getTitle(),
        'source'      => $photo->getSource(),
        'persons'     => $photoPersons
    );
}

$photoList = _json_encode($photosData);

$page = $d['page'];

$canonical_link = ($page > 1) ? 'http://www.popcornnews.ru/photo-article/'.$article->getId() : null;

$title = $article->getTitle().(($page > 1) ? ' - комментарии страница '.$page : '');

$js = array('jquery/jquery.js', 'Comments_new.js?d=13.02.13', 'lstgal.js?v=6.1', 'cloudzoom.js?v=2');
$css = array('style_share.css', 'lstgal.css?v=6');

$this->_render('inc_header',
               array(
                    'title'          => $title,
                    'header'         => $article->getTitle(),
                    'top_code'       => '*',
                    'header_small'   => '',
                    'js'             => $js,
                    'canonical_link' => $canonical_link,
                    'css'            => $css,
               )
);

$currentUrl = 'http://www.popcornnews.ru/photo-article/'.$article->getId();

$room = RoomFactory::load('photoarticle-'.$article->getId());

$commentsCount = $room->getCount();

?>

<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?52"></script>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(document).ready(function () {

        $('.block-social-dockable').css('opacity', 0.8).mouseover(function () {
            $(this).css('opacity', 1);
        }).mouseout(function () {
                    $(this).css('opacity', 0.8)
                });

        var showSharing = {'vk':0, /*'twitter':0,*/'fb':0};
        var showSharingCheck = function () {
            if (showSharing['vk'] == 1 /*&& showSharing['twitter'] == 1*/ && showSharing['fb'] == 1) {
                $('.block-social-dockable').delay(400).css('left', 'auto');
            }
        }

        // fb
        $.getJSON(
                "http://graph.facebook.com/<?=$currentUrl;?>&callback=?",
                function (data) {
                    if (data.shares == undefined) {
                        fb_shares = 0
                    } else {
                        fb_shares = data.shares
                    }
                    $("#fb_soc_counter").html(fb_shares + '<div></div>');
                    $("#fb_small_soc_counter").text(fb_shares);

                    showSharing['fb'] = 1;
                    showSharingCheck();


                }
        );

        // vk
        VK.Share = {};
        // объявляем callback метод
        VK.Share.count = function (index, count) {
            $("#vk_soc_counter").html(count + '<div></div>');
            $("#vk_small_soc_counter").text(count);
            showSharing['vk'] = 1;
            showSharingCheck();
        };

        $.getJSON("http://vk.com/share.php?act=count&index=1&url=<?=$currentUrl;?>&format=json&callback=?");

    });


    //--><!]]>
</script>

<div id="contentWrapper" class="twoCols">
    <div id="content">


        <!--Социальный блок-->
        <noindex>
            <div class="block-social-dockable" style="margin-left:-109px;left:-9999px;">


                <div class="item">
                    <div class="counter" id="fb_soc_counter">0
                        <div></div>
                    </div>
                    <div class="button">
                        <a rel="nofollow" href="http://www.facebook.com/sharer.php?u=<?=urlencode($currentUrl);?>" class="fb"
                           target="_blank" title="Опубликовать в Фейсбуке"></a>
                    </div>
                </div>

                <div class="item">
                    <div class="counter" id="vk_soc_counter">0
                        <div></div>
                    </div>
                    <div class="button">
                        <a rel="nofollow" href="http://vkontakte.ru/share.php?url=<?=$currentUrl;?>" class="b" target="_blank"
                           title="Опубликовать в ВКонтакте"></a>
                    </div>
                </div>


                <div class="item">
                    <div class="button" style="text-align:center;">
                        <a rel="nofollow" href="https://twitter.com/share" data-text="<?=$article->getTitle().' '.$currentUrl;?>"
                           class="twitter-share-button" data-url="<?=urlencode($currentUrl);?>" data-via="popcornnews_ru"
                           data-lang="en" data-related="anywhereTheJavascriptAPI" data-count="vertical">Tweet</a>
                        <script>!function (d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0];
                            if (!d.getElementById(id)) {
                                js = d.createElement(s);
                                js.id = id;
                                js.src = "//platform.twitter.com/widgets.js";
                                fjs.parentNode.insertBefore(js, fjs);
                            }
                        }(document, "script", "twitter-wjs");</script>
                    </div>
                </div>

                <div class="item">
                    <script type="text/javascript">
                        (function () {
                            var po = document.createElement('script');
                            po.type = 'text/javascript';
                            po.async = true;
                            po.src = 'https://apis.google.com/js/plusone.js';
                            var s = document.getElementsByTagName('script')[0];
                            s.parentNode.insertBefore(po, s);
                        })();
                    </script>
                    <div style="margin-left: 13px;">
                        <g:plusone size="tall"></g:plusone>
                    </div>
                </div>


                <div class="item">
                    <div class="button" style="text-align:center;margin-left:17px;">
                        <div style="width:82px;overflow:hidden;">
                            <a rel="nofollow" target="_blank" class="mrc__plugin_uber_like_button"
                               href="http://connect.mail.ru/share"
                               data-mrc-config="{'st' : '1', 'vt':'1', 'sz':'20', 'ck':'1','cm':'1','type':'micro','nt':'1'}">Нравится</a>
                            <script src="http://cdn.connect.mail.ru/js/loader.js" type="text/javascript" charset="UTF-8"></script>
                        </div>
                    </div>
                </div>


            </div>
        </noindex>

        <noscript>
            <link rel="stylesheet" type="text/css" href="lstgal_nojs.css" />

        </noscript>

        <script type="text/javascript">
            var photoList = <?=$photoList;?>;
            var currentPhotoInList = 0;
        </script>

        <div class="newsTrack">
            <div class="trackItem">
                <div class="entryCont">

                    <div class="entryImg">

                        <div class="entryGal cloud-zoom" id='zoom1'>

                            <img class ="theresNoJS" src="<?=$this->getStaticPath($article->getItem(0)->getOriginalPath());?>" alt="">
                        </div>

                    </div>


                    <span class="entryArrowsLeft"></span><span class="entryArrowsRight"></span>

                </div>

                <div class="newsMeta">
                    <span class="comments"><a
                            href="<?=$currentUrl;?>#comments">Комментариев: <?=$commentsCount;?></a></span>
                    <span class="views">Просмотров: <?=$article->getViews();?></span>
                    <span class="tags"><span class="tagsTitle">Тэги: </span><?php
                        $persons = $p['query']->get('persons', array('ids' => implode(',', $article->getPersons())));
                        $events = $p['query']->get('events', array('ids' => implode(',', $article->getTags())));
                        $tagsList = array();
                        foreach($persons as $i => $person) {
                            $link = $person['eng_name'];
                            $link = str_replace('-', '_', $link);
                            $link = str_replace('&dash;', '_', $link);
                            $link = '/persons/'.str_replace(' ', '-', $link);
                            $tagsList[] = '<a href="'.$link.'">'.$person['name'].'</a>';
                        }
                            ?>
                        <?foreach($events as $i => $event) {
                            $tagsList[] = '<a href="/event/'.$event['id'].'">'.$event['name'].'</a>';
                        }
                        ?>
                        <noindex>
                            <?=implode(', ', $tagsList);?>
                        </noindex>
                    </span>

                </div>
            </div>
        </div>
    <? $this->_render('inc_im',
                      array('roomId' => 'photoarticle-'.$article->getId(),
                            'link' => ('/photo-article/'.$article->getId()),
                            'private' => false,
                      )); ?>

    </div>

    <?
    $this->_render('inc_right_column', array('article' => $article));
    ?>
</div>
<? $this->_render('inc_footer'); ?>