<?
$this->_render('inc_header', array('title'=>$d['cuser']['nick'], 'header'=>'Друзья', 'top_code'=>'<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small'=>'Твой профиль'));

$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));

$blackList = BlackListFactory::getBlackListForUser($d['cuser']['id']);
$list = $blackList->getUserList();
$ou = new VPA_table_users();

?>
<div id="contentWrapper" class="twoCols">
    <div id="content">
        <ul class="menu">
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
            <li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">персоны</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">гостевая</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
            <li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>
        </ul>
        <ul class="menu bLevel">
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">главная</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/form">редактировать</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos">фотографии</a></li>
            <li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/photos/del">удаление фото</a></li>
            <li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/blacklist">черный список</a></li>
        </ul>
        <h2>Черный список</h2>
        <?php if(count($list) > 0) { ?>
        <table class="contentUsersTable">
            <tr>
                <th class="user">Пользователь</th>
                <th class="starRating">&nbsp;</th>
                <th class="actions">&nbsp;</th>
            </tr>
            <?php
            foreach($list as $uid) {
                $user = $ou->get_first_fetch(array('id' => $uid));
                ?>
                <tr>
                    <td class="user">
                        <a rel="nofollow" href="/profile/<?=$user['id']?>" class="ava"><img src="<?=$this->getStaticPath($this->getUserAvatar($user['avatara']))?>" alt="" /></a>
                        <a rel="nofollow" href="/profile/<?=$user['id']?>">
                            <span><?=(strlen($user['nick']) < 20 ?
                                htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false)
                                : substr(htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false), 0, 20) . ' ...')?></span>
                        </a>
                    </td>
                    <td class="starRating">
                        <?$rating = $p['rating']->_class($user['rating']);?>
                        <div class="userRating <?=$rating['class']?>">
                            <div class="rating <?=$rating['stars']?>"></div>
                            <span><?=$rating['name']?></span>
                        </div>
                    </td>
                    <td class="actions">
                        <a href="/profile/<?=$d['cuser']['id'];?>/unblock/<?=$uid;?>?back=true">убрать из списка</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
        <h4>Нет заблокированных пользователей</h4>
        <?php } ?>
    </div>
    <?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>