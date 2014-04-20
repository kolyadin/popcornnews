<?
$page = $d['page'];
$canonical_link = ($page > 1) ? 'http://www.popcornnews.ru/meet/'.$d['meet']['id'] : null;
$this->_render('inc_header',
               array(
                    'title' => 'Звездные пары '.$d['meet']['name'].' ',
                    'header' => 'Звездные пары',
                    'top_code' => '&#9829;',
                    'header_small' => 'Голосование за пары &ndash; какая пара лучше?',
                    'js' => 'Comments_new.js?d=22.04.13',
                    'canonical_link' => $canonical_link,
               )
);
?>
<div id="contentWrapper" class="twoCols">
    <div id="content">
        <div class="pair noKid">
            <?$meet = $d['meet'];
            if ($meet['person1'] != '') {
                $person1 = $p['query']->get('persons', array('id'=>$meet['person1']));
                $person_img1 = '<img src="' . $this->getStaticPath('/upload/_192_243_90_' . $person1[0]['main_photo']) . '" />';
                $person_bd1 = ($person1[0]['birthday'] != '')?$person1[0]['birthday']:$meet['person_bd1'];
                $person_bd1 = date('Y', time() - strtotime(str_pad($person_bd1, 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
                $person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');
            } else {
                $person_img1 = '<img src="' . $this->getStaticPath('/upload/' . $meet['person_img1']) . '" />';
                $person_bd1 = date('Y', time() - strtotime(str_pad($meet['person_bd1'], 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
                $person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');

            }
            if ($meet['person2'] != '') {
                $person2 = $p['query']->get('persons', array('id'=>$meet['person2']));
                $person_img2 = '<img src="' . $this->getStaticPath('/upload/_192_243_90_' . $person2[0]['main_photo']) . '" />';
                $person_bd2 = ($person2[0]['birthday'] != '')?$person2[0]['birthday']:$meet['person_bd2'];
                $person_bd2 = date('Y', time() - strtotime(str_pad($person_bd2, 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
                $person_bd2 .= $p['declension']->get($person_bd2, ' год', ' года', ' лет');
            } else {
                $person_img2 = '<img src="' . $this->getStaticPath('/upload/' . $meet['person_img2']) . '" />';
                $person_bd2 = date('Y', time() - strtotime(str_pad($meet['person_bd2'], 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
                $person_bd2 .= $p['declension']->get($person_bd2, ' год', ' года', ' лет');
            }
            ?>
            <h3><span><?=$meet['name']?></span></h3>
            <div class="pics">
                <dl>
                    <dt><?=$person_img1?></dt>
                    <dd><?=$person_bd1?></dd>
                </dl>
                <dl>
                    <dt><?=$person_img2?></dt>
                    <dd><?=$person_bd2?></dd>
                </dl>
            </div>
            <div class="stats">
                <ul class="dkvoter2" onclick="return <?=$meet['id']?>">
                    <li class="up">
                        <span><big><?=(int)$meet['rating_up']?></big><br/><?=$p['declension']->get($meet['rating_up'], ' голос', ' голоса', ' голосов')?></span>
                        <span class="button"></span>
                    </li>
                    <li class="down">
                        <span><big><?=(int)$meet['rating_down']?></big><br/><?=$p['declension']->get($meet['rating_down'], ' голос', ' голоса', ' голосов')?></span>
                        <span class="button"></span>
                    </li>
                </ul>
            </div>
            <div class="desc">
                <p><?=$meet['anounce']?></p>
            </div>
            <a class="all" href="/meet/">Все пары</a>
        </div>
        <?/*$this->_render('inc_comments_with_form', array('new'=>$meet, 'goto'=>'meet'));*/
        $this->_render('inc_im', array('roomId'        => 'meet-'.$meet['id'],
                                       'link'          => ('/meet/'.$meet['id']),
                                       'private'       => false,
                                 ));
        ?>
    </div>
    <?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>
