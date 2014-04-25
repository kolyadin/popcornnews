<?php

$user = $d['user'];
$message = $d['message'];

?>
<a rel="nofollow" href="/profile/<?= $user['id']; ?>" class="ava">
    <img alt="" src="<?= $this->getStaticPath($this->getUserAvatar($user['avatara'])); ?>"/></a>
<? if(!empty($d['cuser']) && !$message['deleted']) { ?>
    <div class="mark">
        <span class="up"><span><?=$message['rating'][1]?></span></span>
        <span class="down"><span>-<?=$message['rating'][0]?></span></span>
    </div>
<? } ?>
<div class="details">
    <a class="pc-user" rel="nofollow" href="/profile/<?= $user['id'] ?>">
        <?=htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);?></a>
    <noindex><span class="date"><?=$p['date']->unixtime($message['date'], '%d %F %Y, %H:%i')?></span></noindex>
        <span class="manage">
                    <?if(!$message['deleted']) { ?>
                <? if($d['cuser']['id'] == $message['owner']) { ?>
                    <span class="edit" data-raw="<?= $p['nc']->replyText($message['content']) ?>">редактировать</span>
                <? }
                if($d['cuser']['id'] == $message['owner'] || $this->isModer()) { ?>
                    <span class="delete">удалить</span>
                <? }
                if(!empty($d['cuser'])) { ?>
                    <span class="reply">ответить</span>
                    <span class="complain">! пожаловаться</span>
                <?
                }
            }
            $rating = $p['rating']->_class($user['rating']);
            ?>
                </span>

    <div class="userRating <?= $rating['class'] ?>">
        <div class="rating <?= $rating['stars'] ?>"></div>
        <span><?=$user['rating']?></span>
    </div>
</div>