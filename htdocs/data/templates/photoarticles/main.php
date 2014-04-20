<?php
if (!is_numeric($d['page'])) $d['page'] = 1;
$per_page = $d['per_page'];

$num = $d['total_articles'];
$articles = $d['articles'];

$templateParams = array(
    'header' => 'Фотогалереи',
    'top_code' => '*',
    'title' => 'Фотогалереи '
);
$pages = ceil($num / $per_page);

if($d['page'] + 1 <= $pages) {
    $templateParams['chrome_next'] = '/photo-articles/page/'.($d['page'] + 1);
}

$this->_render(
    'inc_header',
    $templateParams
);

?>
<style type="text/css">
    .b-grid__g4 {
        width: 180px;
        margin-left: 10px;
        display: inline-block;
        vertical-align: top;
        overflow: hidden;
        *display: inline;
        *zoom: 1;
    }

    .b-grid__g4 img {
        max-width: 180px;
    }

    .b-photo-articles {
        margin-left: -10px;

        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #E5E5E5;
    }

        /**/

    .b-photo-articles {
        margin-bottom: 29px;
    }

    .b-photo-articles__title-cont {
        margin-bottom: 18px;
        padding-left: 10px;
    }

    .b-photo-articles__title {
        display: inline-block;
        *display: inline;
        *zoom: 1;
    }

    .b-photo-articles__counter {
        padding-left: 3px;
        font-weight: bold;
        color: #e72292;
        font-size: 16px;
    }

    .b-photo-articles__photo-cont {
        display: block;
        width: 180px;
        height: 180px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .b-photo-articles__photo-count {
        font-size: 12px;
        color: #ee2c90;
        margin-bottom: 4px;
    }

    .b-photo-articles__news-title {
        font-size: 20px;
        line-height: 1.16;
    }

    .b-photo-articles__news-title a {
        text-decoration: none;
        color: black;
    }
</style>

<div id="contentWrapper" class="twoCols">
    <div id="content">
        <div class="newsTrack">
            <div class="trackItem ">
                <div class="b-photo-articles">
                    <?php
                    foreach($articles as $article) {
                        $photo = $article->getItem(0);
                        $mainImagePath = $this->getStaticPath($photo->getPhotoPathBySize('180x180'));
                        $articlePath = '/photo-article/'.$article->getId();
                        $photosCountText = $article->getPhotosCount().' '
                                           .$p['declension']->get($article->getPhotosCount(),
                                                                  'фотография', 'фотографии', 'фотографий');
                        ?>
                        <div class="b-grid__g4">
                            <a href="<?=$articlePath;?>" class="b-photo-articles__photo-cont">
                                <img src="<?=$mainImagePath;?>" alt="">
                            </a>
                            <div class="b-photo-articles__photo-count"><?=$photosCountText;?></div>
                            <h2 class="b-photo-articles__news-title">
                                <a href="<?=$articlePath;?>"><?=$article->getTitle();?></a>
                            </h2>
                        </div>
                        <?
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if ($num > $per_page) {?>
            <div>
                <div class="paginator smaller">
                    <p class="pages">Страницы:</p>
                    <ul style="display: block; float: none;">
                        <?if ($d['page'] > 1) {?>
                        <li><a href="/photo-articles/page/<?=($d['page'] <= $num ? $d['page'] - 1 : $num);?>">Предыдущая</a></li>
                        <?}?>
                        <?if($d['page'] < $num) {?>
                        <li><a href="/photo-articles/page/<?=($d['page'] != 1 ? $d['page'] + 1 : 2);?>">Следующая</a></li>
                        <? } ?>
                    </ul>
                    <ul style="display: block; clear: both;">
                        <?=($p['pager']->cat_pages($d['page'], $num, 1, '/photo-articles/page/', $per_page));?>
                    </ul>
                </div>
            </div>
            <?
        }
        ?>
    </div>
    <?$this->_render('inc_right_column');?>
</div>

<?$this->_render('inc_footer');?>