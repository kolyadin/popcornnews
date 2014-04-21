<div class="irh irhGB">
    <div class="irhContainer">
        <h3>гостевая<a rel="nofollow" href="/profile/<?=$d['user']['id']?>/guestbook" class="replacer"></a></h3>
        <span class="counter"><a rel="nofollow" href="/profile/<?=$d['user']['id']?>/guestbook"><?=$p['query']->get_num('user_msgs', array('private' => 0, 'uid' => $d['user']['id'], 'pid' => 0, 'del_uid' => 0))?></a></span>
    </div>
    <div class="trackContainer pMessagesTrack">
        <?foreach ($p['query']->get('public_msgs', array('uid'=>$d['user']['id'], 'pid'=>0, 'del_uid' => 0), array('id desc'), 0, 5) as $i => $msg) {?>
        <div class="trackItem" id="<?=$msg['id']?>">
            <style>
                div.entry img {max-width: 374px;}
            </style>
            <div class="entry">
                <p><?=$this->preg_repl($p['nc']->get($msg['content']))?></p>
            </div>
            <a rel="nofollow" href="/profile/<?=$msg['aid'];?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($msg['avatara']))?>" /></a>
            <div class="details">
                <a class="pc-user" rel="nofollow" href="/profile/<?=$msg['aid']?>"><?=htmlspecialchars($msg['nick'], ENT_IGNORE, 'cp1251', false);?></a>
                <span class="date"><?=$p['date']->unixtime($msg['cdate'], "%d %F %Y, %H:%i")?></span>
                <a class="reply" href="#" onclick="delete_msg(<?=$msg['id'];?>, 'wall'); return false;">удалить</a>
                <a class="reply" href="#" onclick="var a=document.getElementById('m_<?=$msg['id']?>'); a.style.display=a.style.display=='block' ? 'none':'block'; return false;">ответить</a>
                <?$rating = $p['rating']->_class($msg['user_rating']);?>
                <div class="userRating <?=$rating['class']?>"  title="<?=$msg['user_rating']?>">
                    <div class="rating <?=$rating['stars']?>"></div>
                    <span><?=$msg['user_rating']?></span>
                </div>
            </div>
        </div>
        <div class="trackContainer mailTrack">
            <div class="trackItem answering" id="m_<?=$msg['id']?>" style="display:none">
                <?php
                $blackList = BlackListFactory::getBlackListForUser($msg['aid']);
                if(!$blackList->isUserExists($d['cuser']['id'])) { ?>
                <form class="answer newMessage smallAnswer" action="/" method="POST" name="fmr">
                    <input type="hidden" name="type" value="guestbook">
                    <input type="hidden" name="action" value="add_comment">
                    <input type="hidden" name="uid" value="<?=$msg['aid']?>">
                    <?$this->_render('inc_bbcode');?>
                    <?$this->_render('inc_smiles');?>
                    <textarea name="content"></textarea>
                    <div class="meta">
                        <input type="submit" value="отправить" />
                    </div>
                </form>
                <?php } else { ?>
                    <h4>настройки приватности не позволяют вам писать сообщения</h4>
                <?php } ?>
            </div>
        </div>
        <?}?>
        <a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook" class="new">Написать у себя в гостевой</a>
    </div>
</div>