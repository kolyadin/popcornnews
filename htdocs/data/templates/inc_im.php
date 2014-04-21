<?

$private = isset($d['private']) ? $d['private'] : false;
$link = $d['link'];
$roomId = $d['roomId'];

$roomType = explode('-', $roomId);
$roomType = $roomType[0];

$configs = RoomConfig::getConfigs($roomType);
$canComment = isset($d['canComment']) ? $d['canComment'] : $configs['canComment'];

$room = RoomFactory::load($roomId);

$messages = $room->getMessages();
$users = array();
$o_u = new VPA_table_users();
$commentCount = $room->getCount();
$rootCommentCount = $room->getRootCount();

if(get_class($room) != "NullRoom") {
    $options = isset($d['editorOptions']) ? $d['editorOptions'] : array(
        'images' => $configs['editorImages']
    );
    $canSubscribe = isset($d['canSubscribe']) ? $d['canSubscribe'] : $configs['canSubscribe'];

    ?>
    <style>
        a.show-image-link {
            text-decoration:none;
            border-bottom:1px dashed #f70080;
            display: inline-block;
        }

        a.show-image-link img {
            display: none;
        }

        a.toggle {
            border: none;
        }

        a.toggle img {
            display: block;
            margin-top: 6px;
        }

        a.toggle span {
            display: none;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {

            function createImage(item) {
                var img = $(new Image());
                img.attr('src', item.attr('href'));
                item.append(img);
            }

            function toggleImage(item) {
                if($('img', item).length == 0) {
                    createImage(item);
                }
                item.toggleClass('toggle');
            }

            $('a.show-image-link').click(function(){
                toggleImage($(this));
                return false;
            });
        });
    </script>
    <div class="trackContainer commentsTrack"><a name="comments"></a>

        <div class="irh irhComments">
            <div class="irhContainer">
                <h3>комментарии<span class="replacer"></span></h3>
                <span class="counter"><?=$commentCount;?></span>
                <a id="write_new_comment" class="_1" href="#write_comment">Написать новый</a>
            </div>
        </div>
        <?php
        if(!empty($messages)) {
            foreach($messages as $key => $message) {
                $user = $room->getUser($message['owner']);
                $level = ($message['level'] > 1) ? ' level-'.$message['level'] : '';
                preg_match_all('/\[img\](.+)\[\/img\]/Uis', $message['content'], $matches, PREG_SET_ORDER);
                if(count($matches) > 0) {
                    foreach($matches as $m) {
                        $message['content'] = str_replace($m[0], '<a href="'.$m[1].'" target="_blank" class="show-image-link"><span>показать изображение</span></a><br />', $message['content']);
                    }
                }
                ?>
                <div class="trackItem<?= $level; ?>" id="<?= $key; ?>">
                    <a name="cid_<?= $key; ?>"></a>

                    <div class="post">
                        <div class="entry">
                            <p>
                                <?php
                                $text = (!$message['deleted'] ? $this->preg_repl($p['nc']->get($message['content'])) :
                                    COMMENTS_DELETE_PHRASE);
                                print $text;
                                ?>
                            </p>
                        </div>
                        <?php
                        if(empty($user)) {
                            $this->_render('inc_im_empty_user', array('message' => $message));
                        }
                        else {
                            $this->_render('inc_im_user', array('user' => $user, 'message' => $message));
                        }
                        ?>
                    </div>
                </div>
            <?php
            }
        } ?>
    </div>
    <?php if(!empty($d['cuser'])) {
        if($canComment) {
            ?>
            <div class="trackContainer">
                <a id="write_comment" name="write_comment"></a><br/>
                <img src="/i/f6.gif" alt="написать комментарий"/><br/><br/>
                <img class="emulate_comments_form" src="/i/f5.jpg" alt="Написать комментарий"/>

                <form id="comments_form" class="newComment" name="fmr" action="/ajax/im" method="post">
                    <input type="hidden" name="type" value="ajax"/>
                    <input type="hidden" name="action" value="im"/>
                    <input type="hidden" name="imAction" value="add"/>
                    <input type="hidden" name="parentId" value=""/>
                    <input type="hidden" name="messageId" value=""/>
                    <input type="hidden" name="private" value="<?= $private; ?>"/>
                    <input type="hidden" name="link" value="<?= $link; ?>"/>
                    <input type="hidden" name="roomId" value="<?= $roomId; ?>"/>
                    <input type="hidden" name="owner" value="<?= $d['cuser']['id'] ?>"/>
                    <a name="write"></a>

                    <div class="trackItem">
                        <div class="entry">
                            <?$this->_render('inc_bbcode', $options);?>
                            <?$this->_render('inc_smiles');?>
                            <textarea name="content"></textarea>
                        </div>
                        <fieldset class="loggedOut twoCols">
                            <div class="aboutMe">
                                <a rel="nofollow" href="/profile/<?= $d['cuser']['id'] ?>" class="ava"><img alt=""
                                                                                                            src="<?= $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'])) ?>"/></a>
                                <span>Вы пишете как</span><br/>
                                <a rel="nofollow"
                                   href="/profile/<?= $d['cuser']['id'] ?>"><?=htmlspecialchars($d['cuser']['nick'], ENT_IGNORE,
                                                                                                'cp1251', false);?></a>
                            </div>
                        </fieldset>
                    </div>
                    <div class="formActions">
                        <input type="submit" name="submit"
                               value="отправить"<?php /*?> onclick="this.enabled = false;"<?php */?> />
                        <?php if($canSubscribe) { ?>
                            <label>
                                <input type="checkbox" name="subscribe"<?=($room->isSubscribed($d['cuser']['id']) ?
                                    ' value="1" checked="checked"' : 'value="0"')?> onclick="this.value = this.checked ? 1 : 0;console.log(this.value);" />
                                Присылать мне уведомления о новых комментариях
                            </label>
                        <?php } ?>
                    </div>
                </form>
            </div>
        <?
        }
    }
    else {
        ?>
        <a id="write_comment" name="write_comment"></a><br/><br/>
        <?php if(isset($d['customUnregisterNotify'])) {
            $notify = $d['customUnregisterNotify'];
            echo sprintf($notify['text'], $notify['link']);
        }
        else {
            ?>Если Вы хотите оставить комментарий - <a href="/register">зарегистрируйтесь.</a>
        <?php } ?>
    <?
    }
} else {
    echo "<h2>Комментарии временно недоступны</h2>";
} ?>