<?
$this->_render('inc_header', array('title'        => 'Звездные дети', 'header' => 'Звездные дети', 'top_code' => '&#9829;',
                                   'header_small' => 'Кто самый милый звездный ребенок?'));?>
<div id="contentWrapper" class="twoCols">
    <div id="content">
        <div class="pairsTrack">
            <?
            $limit = 12;
            $offset = ($d['page'] - 1) * $limit;
            $num_kids = $p['query']->get_num('kids', array('no_show' => 1));

            foreach($p['query']->get('kids', array('no_show' => 1), array('rating_up desc', 'id desc'), $offset,
                                     $limit) as $i => $kid) {
                if($kid['person1'] != '') {
                    $person1 = $p['query']->get('persons', array('id' => $kid['person1']));
                    $person_img1 = '<img src="'.$this->getStaticPath('/upload/_393_243_90_'.$person1[0]['main_photo']).'" />';
                    $person_bd1 = ($person1[0]['birthday'] != '') ? $person1[0]['birthday'] : $kid['person_bd1'];
                    $person_bd1 = date('Y', time() - strtotime(str_pad($person_bd1, 8, '0', STR_PAD_RIGHT).' 000000')) - date('Y',
                                                                                                                              strtotime(0));
                    $person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');
                }
                else {
                    $person_img1 = '<img src="'.$this->getStaticPath('/upload/'.$kid['person_img1']).'" />';
                    $person_bd1 =
                        date('Y', time() - strtotime(str_pad($kid['person_bd1'], 8, '0', STR_PAD_RIGHT).' 000000')) - date('Y',
                                                                                                                           strtotime(0));
                    $person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');

                }
                ?>
                <div class="pair kid">
                    <h3><span><a href="/kid/<?= $kid['id'] ?>"><?= $kid['name'] ?></a></span></h3>

                    <div class="pics">
                        <dl>
                            <dt><a href="/kid/<?= $kid['id'] ?>"><?= $person_img1 ?></a></dt>
                            <dd><?= $person_bd1 ?></dd>
                        </dl>
                    </div>
                    <div class="stats">
                        <ul class="dkvoter2" onclick="return <?= $kid['id'] ?>">
                            <li class="up">
                                <span><big><?= (int)$kid['rating_up'] ?></big><br/><?= $p['declension']->get($kid['rating_up'],
                                                                                                             ' голос', ' голоса',
                                                                                                             ' голосов') ?></span>
                                <span class="button"></span>
                            </li>
                            <li class="down">
                                <span><big><?= (int)$kid['rating_down'] ?></big><br/><?= $p['declension']->get($kid['rating_down'],
                                                                                                               ' голос',
                                                                                                               ' голоса',
                                                                                                               ' голосов') ?></span>
                                <span class="button"></span>
                            </li>
                        </ul>
                        <?php $commentCount = RoomFactory::load('kids-'.$kid['id'])->getCount(); ?>
                        <a href="/kid/<?= $kid['id'] ?>#comments" class="cCounter"><?= ($commentCount > 0) ?
                                $commentCount.$p['declension']->get($commentCount, ' комментарий', ' комментария',
                                                                      ' комментариев') : 'нет комментариев' ?> </a><br/>
                        <a href="/kid/<?= $kid['id'] ?>#write" class="comment" rel="nofollow">Написать комментарий</a>
                    </div>
                    <div class="desc">
                        <p><?= $kid['anounce'] ?></p>
                    </div>
                </div>
            <? } ?>
        </div>
        <?
        $pages = ceil($num_kids / $limit);
        if($pages > 1) {
            ?>
            <div class="paginator smaller">
                <p class="pages">Страницы:</p>
                <ul>
                    <? foreach($p['pager']->make($d['page'], $pages, 50) as $i => $pi) { ?>
                        <li>
                            <? if(!isset($pi['current'])) { ?>
                                <a href="/kids/page/<?= $pi['link'] ?>"><?= $pi['text'] ?></a>
                            <? }
                            else { ?>
                                <?= $pi['text'] ?>
                            <? } ?>
                        </li>
                    <? } ?>
                </ul>
            </div>
        <? } ?>
    </div>
    <? $this->_render('inc_right_column'); ?>
</div>
<? $this->_render('inc_footer'); ?>
