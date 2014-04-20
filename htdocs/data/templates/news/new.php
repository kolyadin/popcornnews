<?
$new = $d['new_data'];
$zoom_in = ($new['zoom_in'] == "Yes");
$date_tmp = $new['cdate'];
$page = $d['page'];
if(strpos($_SERVER['REQUEST_URI'], "/news/{$new['id']}/page/1") !== false && $page == 1) {
    $location = "/news/{$new['id']}";
    header('HTTP/1.1 301 Moved Permanently');
    header("Location: {$location}");
}
/*$noindex = ($page > 1) ? '<noindex>' : '';
$noindexEnd = ($page > 1) ? '</noindex>' : '';*/
$noindex = '';
$noindexEnd = '';

$canonical_link = ($page > 1) ? 'http://www.popcornnews.ru/news/'.$new['id'] : null;

$title = $new['name'].(($page > 1) ? ' - комментарии страница '.$page : '');

$isBlog = (strpos($new['ids_events'], $_SERVER['blogID']) !== false);

$js = array('jquery/jquery.js', 'Comments_new.js?d=15.04.13');
$css = array('style_share.css');

if($zoom_in) {
    $js[] = 'cloud-zoom.1.0.2.min.js?v=1';
    $css[] = 'cloud-zoom.css?v=1';
}

$this->_render('inc_header',
               array(
                    'title'          => $title,
                    'header'         => $new['name'],
                    'top_code'       => ($isBlog) ? '' : '*',
                    'header_small'   => $p['date']->parse($new['cdate'], '%d %F %Y'),
                    'js'             => $js,
                    'isBlog'         => $isBlog,
                    'canonical_link' => $canonical_link,
                    'css'            => $css,
               )
);
preg_match_all('/\<iframe.*\/iframe\>/is', $new['content'], $m, PREG_SET_ORDER);
if(isset($m[0])) {
    if(isset($m[0][0])) {
        $t = $m[0][0];
        if(strpos($t, 'api.kinoafisha.info') !== false) {
            $out = str_replace('width="645"', 'width="580"', $t);
            $out = str_replace('height="345"', 'height="310"', $out);
            $new['content'] = str_replace($t, $out, $new['content']);
        }
    }
}
$new['content'] = str_replace('upload/image', 'upload/image/fc', $new['content']);
$new_url = 'http://www.popcornnews.ru/news/'.$new['id'];

?>
    <script type="text/javascript" src="http://userapi.com/js/api/openapi.js?52"></script>
    <script type="text/javascript">
        $(document).ready(function () {

            $('.block-social-dockable').css('opacity', 0.8).mouseover(function () {
                $(this).css('opacity', 1);
            }).mouseout(function () {
                    $(this).css('opacity', 0.8)
                });

            var showSharing = {'vk': 0, /*'twitter':0,*/'fb': 0};
            var showSharingCheck = function () {
                //if (showSharing['vk'] == 1 /*&& showSharing['twitter'] == 1* && showSharing['fb'] == 1*/) {
                $('.block-social-dockable').delay(400).css('left', 'auto');
                //}
            }

            // fb
            $.getJSON(
                "http://graph.facebook.com/<?=$new_url;?>&callback=?",
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

            $.getJSON("http://vkontakte.ru/share.php?act=count&index=1&url=<?=$new_url;?>&format=json&callback=?");

        });

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
                    <a rel="nofollow" href="http://www.facebook.com/sharer.php?u=<?= urlencode($new_url); ?>" class="fb"
                       target="_blank" title="Опубликовать в Фейсбуке"></a>
                </div>
            </div>

            <div class="item">
                <div class="counter" id="vk_soc_counter">0
                    <div></div>
                </div>
                <div class="button">
                    <a rel="nofollow" href="http://vkontakte.ru/share.php?url=<?= $new_url; ?>" class="b" target="_blank"
                       title="Опубликовать в ВКонтакте"></a>
                </div>
            </div>


            <div class="item">
                <div class="button" style="text-align:center;">
                    <a rel="nofollow" href="https://twitter.com/share" data-text="<?= $new['name'].' '.$new_url; ?>"
                       class="twitter-share-button" data-url="<?= urlencode($new_url); ?>" data-via="popcornnews_ru"
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
                        <a rel="nofollow" target="_blank" class="mrc__plugin_uber_like_button" href="http://connect.mail.ru/share"
                           data-mrc-config="{'st' : '1', 'vt':'1', 'sz':'20', 'ck':'1','cm':'1','type':'micro','nt':'1'}">Нравится</a>
                        <script src="http://cdn.connect.mail.ru/js/loader.js" type="text/javascript" charset="UTF-8"></script>
                    </div>
                </div>
            </div>
            
            
            <div class="item"><div style="text-align:center;">
            	<a rel="nofollow" href="//pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" ><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>
            	<script async="true" type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
            </div></div>
            


        </div>
    </noindex>
    <!---->


    <div class="newsTrack">
        <div class="trackItem">
            <?= $noindex; ?>
            <div class="content-lock">
                <div class="entry-anounce">
                    <?= $new['anounce'] ?>
                </div>
                <div class="imagesContainer-details">
                    <?if(!empty($new['main_photo'])) {
                        if($zoom_in) {

                            $small_img = $this->getStaticPath('/upload/_500_600_80_'.$new['main_photo']);
                            $big_img = $this->getStaticPath('/upload/'.$new['main_photo']);

                            ?>
                            <script type="text/javascript">$(document).ready(function () {
                                    $width = $('.cloud-zoom').find('img').width();
                                    $('.imagesContainer-details').css('width', $width);
                                    $('.img_zoom').css('display', 'block');
                                });
                            </script>
                            <div class="img_zoom"><img src="/i/zooom.png"/></div>
                            <div href='<?= $big_img ?>' class='cloud-zoom' id='zoom1'
                                 rel="position: 'inside', showTitle: false">
                                <img src="<?= $small_img ?>" alt="<?= $new['name'] ?>" title="<?= $new['name'] ?>"/>
                            </div>
                        <?
                        }
                        else {
                            ?>
                            <img src="<?= $this->getStaticPath('/upload/_500_600_80_'.$new['main_photo']) ?>"
                                 alt="<?= $new['name'] ?>" title="<?= $new['name'] ?>"/>
                        <?
                        }
                    } ?>
                </div>
                <div class="entry-details">
                    <?= $new['content'] ?>
                    <? $ids_persons = $new['ids_persons'] ?>
                    <? $ids_events = $new['ids_events'] ?>
                    <? foreach($p['query']->get('news_images', array('news_id' => $new['id']), array('seq')) as $i => $pix) { ?>
                    	
                    	<?
                    	if (!empty($pix['caption']))
                    	{
                    		print '
                    		<style type="text/css">
                    		.news_images_wrapper {
                    			display: inline-block;
								*display: inline;
								zoom: 1;
                    		}
                    		</style>
                    		';
                    		
                    		printf('<br/><br/><div class="news_images_wrapper"><img src="%s" alt="%s" title="%s"><div style="width:auto;color:#666;text-align:center;">%s</div></div>'
                    			,$this->getStaticPath('/k/news/500x700'.$pix['filepath'])
                    			,$pix['name']
                    			,$pix['name']
                    			,$pix['caption']
                    		);
                    	}
                    	else
                    	{
                    		printf('<br/><br/><img src="%s" alt="%s" title="%s">'
                    			,$this->getStaticPath('/k/news/500x700'.$pix['filepath'])
                    			,$pix['name']
                    			,$pix['name']
                    		);
                    	}
                    	?>
                    <? } ?>
                    <? if($new["link"]) { ?>
                        <div class="source">Источник: <?= $new["link"] ?></div><? } ?>
                    <? if($new["photo_link"]) { ?>
                        <br /><br />
                        <div class="source-image">Фото: <?= $new["photo_link"] ?></div><? } ?>
                    <? if(!empty($d['new_battle_rating'])) { ?>
                        <div class="battle" onclick="return {id:<?= $new['id'] ?>}">
                            <div class="wrs">
                                <a href="/new_vote/<?= $new['id'] ?>/1" class="lwr"><?= $new['name1'] ?></a>
                                <a href="/new_vote/<?= $new['id'] ?>/2" class="rwr"><?= $new['name2'] ?></a>
                            </div>

                            <div class="battleRate<?=
                            ($d['new_battle_rating']['v1'] == $d['new_battle_rating']['v2'] ?
                                ' battleEqual' : null) ?>">
                                <div class="lwr<?=
                                ($d['new_battle_rating']['v1'] > $d['new_battle_rating']['v2'] ? ' leading' :
                                    null) ?>" style="width: <?= $d['new_battle_rating']['p1_print'] ?>%">
                                    <span><?= $d['new_battle_rating']['v1'] ?></span>
                                    <small class="t"></small>
                                    <small class="b"></small>
                                </div>
                                <div class="rwr<?=
                                ($d['new_battle_rating']['v2'] > $d['new_battle_rating']['v1'] ? ' leading' :
                                    null) ?>" style="width: <?= $d['new_battle_rating']['p2_print'] ?>%">
                                    <span><?= $d['new_battle_rating']['v2'] ?></span>
                                    <small class="t"></small>
                                    <small class="b"></small>
                                </div>
                            </div>
                        </div>
                    <? } ?>

                    <? if($d['new_data']['poll']) { ?>
                        <div class="poll">
                            <h4><?= $d['new_data']['poll'] ?></h4>

                            <div id="options">
                                <? if($d['cuser'] && !$d['user_vote']) { ?>
                                    <form class="poll" name="poll" onsubmit="return news_poll_submit(this);">
                                        <ul class="poll">
                                            <? foreach($d['poll_options'] as $option) { ?>
                                                <li><label><input type="radio" name="option"
                                                                  value="<?= $option['id'] ?>"/><?= $option['title'] ?></label>
                                                </li>
                                            <? } ?>
                                        </ul>
                                        <input type="submit" class="submit"/>
                                    </form>
                                <?
                                }
                                else {
                                    ?>
                                    <ul class="poll">
                                        <? foreach($d['poll_options'] as $option) { ?>
                                            <li>
                                                <span class="name"><?= $option['title'] ?></span><span
                                                    class="count"><?= $option['rating'] ?></span>
                                                <span class="percent"><span
                                                        style="width: <?= $option['percent'] ?>%"></span></span>
                                            </li>
                                        <? } ?>
                                    </ul>
                                <? } ?>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>
            <?= $noindexEnd; ?>

            <div class="newsMeta">
                <span class="comments"><a
                        href="/news/<?= $new['id'] ?>#comments">Комментариев: <?=
                        RoomFactory::load('news-'.$new['id'])
                            ->getCount();?></a></span>
                <span class="views">Просмотров: <?= $d['views'] ?></span>
					<span class="tags">Тэги:<?
                        $persons = $p['query']->get('persons', array('ids' => $new['ids_persons']));
                        $events = $p['query']->get('events', array('ids' => $new['ids_events']));
                        foreach($persons as $i => $person) {
                            $link = $person['eng_name'];
                            $link = str_replace('-', '_', $link);
                            $link = str_replace('&dash;', '_', $link);
                            $link = '/persons/'.str_replace(' ', '-', $link);
                            ?>
                            <noindex><a href="<?= $link; ?>"><?= $person['name'] ?></a>
                            </noindex><? if($i < count($persons) - 1) { ?>,<? } ?>
                        <? } ?>
                        <? if(!empty($persons) && !empty($events)) { ?>,<? }; ?>
                        <? foreach($events as $i => $event) { ?>
                            <noindex><a href="/event/<?= $event['id'] ?>"><?= $event['name'] ?></a>
                            </noindex><? if($i < count($events) - 1) { ?>,<? } ?>
                        <? } ?>
					</span>

                <noindex>
                    <div id="form_for_lj">
                        <form id="form_lj_post">
                            <input type="hidden" name="subject" value="<?= $p['iconv']->iconv($new['name']) ?>"/>
                            <textarea
                                name="event"><?=
                                $p['iconv']->iconv(sprintf('<p>Новость с popcornnews</p><p><a href="/news/%u">%s</a></p>',
                                                           $new['id'], $new['name']))?></textarea>
                        </form>
                    </div>
                </noindex>
            </div>
        </div>
    </div>
    <?
    $date_tmp = strtotime(substr($date_tmp, 0, 4).'-'.substr($date_tmp, 4, 2).'-'.substr($date_tmp, 6, 2));
    $date_diff = time() - $date_tmp;

    // Больше года или нельзя комментировать
    if($new['forbid_comments'] || ($date_diff > 3600 * 24 * 365)) {
        $this->_render('inc_im', array('roomId'        => 'news-'.$new['id'],
                                       'link'          => ('/news/'.$new['id']),
                                       'private'       => false,
                                 ));
    }
    else {
        $this->_render('inc_im', array('roomId'        => 'news-'.$new['id'],
                                       'link'          => ('/news/'.$new['id']),
                                       'private'       => false,
                                 ));
    }
    ?>
    <div class="pastHeadline">
        <h2>ранее</h2>
    </div>
    <div class="trackContainer datesTrack">
        <?
        for($date = time(), $i = 0; $i < 3; $i++, $date = strtotime("-1 month", $date)) {
            $news = $p['query']->get('news', array('date_ym_like' => date('Y-m', $date), 'id_no' => $d['new_id']),
                                     array('newsIntDate DESC', 'id DESC'), 0, 5, null, true);
            if(!empty($news)) {
                ?>
                <div class="trackItem">
                    <h4><?= $p['date']->unixtime($date, '%N %Y') ?></h4>
                    <ul>
                        <? foreach($news as $j => $new) { ?>
                            <li><a href="/news/<?= $new['id'] ?>"><?= $new['name'] ?></a>
                                (<?= RoomFactory::load('news-'.$new['id'])->getCount(); ?>)
                            </li>
                        <? } ?>
                    </ul>
                </div>
            <?
            }
        }
        ?>
    </div>
    </div>
    <? $this->_render('inc_right_column'); ?>
    </div>
<? $this->_render('inc_footer'); ?>