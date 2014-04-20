<?
$page = $d['page'];
$canonical_link = ($page > 1) ? 'http://www.popcornnews.ru/kid/'.$d['kid']['id'] : null;
$this->_render('inc_header',
               array(
                    'title' => 'Звездные дети '.$d['kid']['name'].' ',
                    'header' => 'Звездные дети',
                    'top_code' => '&#9829;',
                    'header_small' => 'Кто самый милый звездный ребенок?',
                    'js' => 'Comments_new.js?d=13.05.11',
                    'canonical_link' => $canonical_link,
               )
);
?>
<div id="contentWrapper" class="twoCols">
    <div id="content">
        <div class="pair kid">
            <?
            $kid = $d['kid'];
            if ($kid['person1'] != '') {
                $person1 = $p['query']->get('persons', array('id'=>$kid['person1']));
                $person_img1 = '<a href="/tag/' . $kid['person1'] . '"><img src="' . $this->getStaticPath('/upload/_393_243_90_' . $person1[0]['main_photo']) . '" /></a>';
                $person_bd1 = ($person1[0]['birthday'] != '') ? $person1[0]['birthday'] : $kid['person_bd1'];
                $person_bd1 = date('Y', time() - strtotime(str_pad($person_bd1, 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
                $person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');
            } else {
                $person_img1 = '<img src="' . $this->getStaticPath('/upload/' . $kid['person_img1']) . '" />';
                $person_bd1 = date('Y', time() - strtotime(str_pad($kid['person_bd1'], 8, '0', STR_PAD_RIGHT) . ' 000000')) - date('Y', strtotime(0));
                $person_bd1 .= $p['declension']->get($person_bd1, ' год', ' года', ' лет');
            }
            ?>
            <h3><span><?=$kid['name']?></span></h3>
            <div class="pics">
                <dl>
                    <dt><?=$person_img1?></dt>
                    <dd><?=$person_bd1?></dd>
                </dl>
            </div>
            <div class="stats">
                <ul class="dkvoter2" onclick="return <?=$kid['id']?>">
                    <li class="up">
                        <span><big><?=(int)$kid['rating_up']?></big><br/><?=$p['declension']->get($kid['rating_up'], ' голос', ' голоса', ' голосов')?></span>
                        <span class="button"></span>
                    </li>
                    <li class="down">
                        <span><big><?=(int)$kid['rating_down']?></big><br/><?=$p['declension']->get($kid['rating_down'], ' голос', ' голоса', ' голосов')?></span>
                        <span class="button"></span>
                    </li>
                </ul>
            </div>
            <div class="desc">
                <p><?=$kid['anounce']?></p>
            </div>
            <a class="all" href="/kids/">Все дети</a>
        </div>
        <?$this->_render('inc_im', array('roomId'        => 'kids-'.$kid['id'],
                                         'link'          => ('/kid/'.$kid['id']),
                                         'private'       => false,
                                   ));?>
    </div>
    <?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>