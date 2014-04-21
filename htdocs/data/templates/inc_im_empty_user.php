<?php

$message = $d['message'];

?>
<a rel="nofollow" class="ava">
    <img alt="" src="<?= $this->getStaticPath($this->getUserAvatar(null)); ?>"/></a>
<? if(!empty($d['cuser']) && !$message['deleted']) { ?>
    <div class="mark">
        <span class="up"><span><?=$message['rating'][1]?></span></span>
        <span class="down"><span>-<?=$message['rating'][0]?></span></span>
    </div>
<? } ?>
<div class="details">
    <span class="pc-user date"><strong>ѕользователь удален</strong></span>
    <noindex><span class="date"><?=$p['date']->unixtime($message['date'], '%d %F %Y, %H:%i')?></span></noindex>

        <span class="manage">
                    <?if(!$message['deleted'] && $this->isModer()) { ?>
                <span class="delete">удалить</span>
            <? }?>
            <span class="complain"><nobr>! пожаловатьс€</nobr></span>
                </span>
</div>