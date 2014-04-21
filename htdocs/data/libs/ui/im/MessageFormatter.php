<?php

class MessageFormatter {

    const MessageFormat = '<div class="trackItem%level%" id="%key%">
        <a name="cid_%key%"></a>
        <div class="post">
            <div class="entry">
                <p>%content%</p>
            </div>
            <a rel="nofollow" href="/profile/%userId%" class="ava">
                <img alt="" src="%userAvatara%" />
            </a>
            %messageRatingBlock%
            <div class="details">
                %userInfoBlock%
            </div>
        </div>
    </div>';
    
    const ratingBlock = '<div class="mark">
                <span class="up"><span>%rateUp%</span></span>
                <span class="down"><span>-%rateDown%</span></span>
            </div>';
    
    const registerUserBlock = '<a class="pc-user" rel="nofollow" href="/profile/%userId%">%userNick%</a>
                <noindex><span class="date">%messageDate%</span></noindex>
                <span class="manage">
                    %userActionsBlock%
                </span>
                <div class="userRating %userRatingClass%">
                    <div class="rating %userRatingStars%"></div>
                    <span>%userRating%</span>
                </div>';
    
    const unregisterUserBlock = '<span class="pc-user"></span>
                <noindex><span class="date">%messageDate%</span></noindex>

                <span class="manage">
                    %deleteActionBlock%
                    <span class="complain"><nobr>! пожаловаться</nobr></span>
                </span>';
    
    private $message = null;
    private $ui;
    private $roomId;
    
    public function __construct(IMessage $message, $ui, $roomId) {
        $this->message = $message->getData();
        $encoding = mb_detect_encoding($this->message['content'], array('utf-8', 'cp1251'));
        if($encoding == 'UTF-8') {
            $this->message['content'] = iconv('UTF-8', 'WINDOWS-1251', $this->message['content']);
        }
        $this->ui = $ui;
        $this->roomId = $roomId;
    } 
    
    public function format() {
        $tpl = $this->ui->tpl;
        $p = $tpl->plugins;
        $user = $this->getUser();
        
        $cuser = $this->ui->user;  
        $content = !$this->message['delete'] 
            ? $tpl->preg_repl($p['nc']->get($this->message['content'])) : COMMENTS_DELETE_PHRASE;
        $userId = $user['id'];
        $userAvatara = $tpl->getStaticPath($tpl->getUserAvatar($user['avatara']));
        $date = $p['date']->unixtime($this->message['date'], '%d %F %Y, %H:%i');
        
        $rateBlock = $this->formatMessageRatingBlock($cuser);       
        $userInfoBlock = $this->formatUserInfoBlock($user, $cuser);
        $level = RoomFactory::getMessageLevel($this->message['id'], $this->roomId);
        $level = ($level > 1) ? ' level-'.$level : '';
        
        $outFormat = self::MessageFormat;
        $outFormat = str_replace('%messageRatingBlock%', $rateBlock, $outFormat);
        $outFormat = str_replace('%userInfoBlock%', $userInfoBlock, $outFormat);
        $outFormat = str_replace('%userId%', $this->message['owner'], $outFormat);
        $outFormat = str_replace('%messageDate%', $date, $outFormat);
        $outFormat = str_replace('%userAvatara%', $userAvatara, $outFormat);
        $outFormat = str_replace('%level%', $level, $outFormat);
        $outFormat = str_replace('%key%', $this->message['id'], $outFormat);
        $outFormat = str_replace('%content%', $content, $outFormat);
        $outFormat = str_replace('%unformattedContent%', $p['nc']->replyText($this->message['content']), $outFormat);
                
        return $outFormat;
    }
    
	private function getUser() {
        $o_u = new VPA_table_users();
        $user = $o_u->get_first_fetch(array('id' => $this->message['owner']));
        unset($o_u);
        return $user;
    }
    
	private function formatUserInfoBlock($user, $cuser) {	    
        if(!empty($user)) {
            $userInfoBlock = $this->formatRegisteredUserBlock($user);           
        } else {
            $userInfoBlock = $this->formatUnregisteredUserBlock();
        }
        return $userInfoBlock;
	}

	private function formatUnregisteredUserBlock() {
        $userInfoBlock = self::unregisterUserBlock;
        $deleteActionBlock = '';
        if($this->ui->tpl->isModer() && !$this->message['delete']) {
            $deleteActionBlock .= '<span class="delete">удалить</span> ';
        }
        return str_replace('%deleteActionBlock%', $deleteActionBlock, $userInfoBlock);
    }

	private function formatRegisteredUserBlock($user) {
	    $p = $this->ui->tpl->plugins;
        $userInfoBlock = self::registerUserBlock;
        $userNick = htmlspecialchars($user['nick'], ENT_IGNORE, 'cp1251', false);            
        $rating = $p['rating']->_class($user['rating']);          
        $userActionsBlock = $this->formatUserActionsBlock();
        
        $userInfoBlock = str_replace('%userActionsBlock%', $userActionsBlock, $userInfoBlock);
        $userInfoBlock = str_replace('%userNick%', $userNick, $userInfoBlock);
        
        $userInfoBlock = str_replace('%userRatingClass%', $rating['class'], $userInfoBlock);
        $userInfoBlock = str_replace('%userRatingStars%', $rating['stars'], $userInfoBlock);
        $userInfoBlock = str_replace('%userRating%', $user['rating'], $userInfoBlock);
        return $userInfoBlock;
    }
	
	private function formatUserActionsBlock() {
	    $cuser = $this->ui->user;
	    $userActionsBlock = '';
        if(!$this->message['delete']) {
            if ($cuser['id'] == $this->message['owner']) {
                $userActionsBlock .= '<span class="edit" data-raw="%unformattedContent%">редактировать</span> ';
            }
            if ($cuser['id'] == $this->message['owner'] || $this->ui->tpl->isModer()) {
                $userActionsBlock .= '<span class="delete">удалить</span> ';
            }
            if (!empty($cuser)) {
                $userActionsBlock .= '<span class="reply">ответить</span> ';
                $userActionsBlock .= '<span class="complain">! пожаловаться</span> ';
            }
        }
        return $userActionsBlock;
	}
    
	private function formatMessageRatingBlock($user) {
	    $rateBlock = '';
        if(!empty($user) && !$this->message['delete']) {
            $rateBlock = self::ratingBlock;
            $rateUp = $this->message['rating'][1];
            $rateDown = $this->message['rating'][0];

            $rateBlock = str_replace('%rateUp%', $rateUp, $rateBlock);
            $rateBlock = str_replace('%rateDown%', $rateDown, $rateBlock);
        }
        return $rateBlock;
	}

    public static function clearCommentText($text) {
        if(preg_match_all('@\[b\]Ответ\s+на\s+сообщение\s+от(.+)\[\/b\](.+)\z@si', $text, $matches1)) {
            if(preg_match_all('@\[b\]Ответ\s+на\s+сообщение\s+от\s+(.+),(.+),(.+)\[\/b\]@', $matches1[0][0], $matches2)) {
                $commentText = $matches1[2][0];
                $ar = explode('[/quote]', $commentText);

                return trim(end($ar));
            }
        }

        return $text;
    }
}
