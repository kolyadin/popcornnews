<?php
/**
 * User: anubis
 * Date: 2/5/13
 * Time: 4:07 PM
 */
class IMHandler {

    public static function showNoLoginError(user_base_api $ui) {
        $ui->tpl->assign('data', array('status' => 0, 'detail' => 'no_login'));
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $ui->handler_show_error('no_login');
        }

    }

    public static function showUserBanned(user_base_api $ui) {
        $ui->tpl->assign('data', array('status' => 0, 'detail' => 'banned'));
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $ui->handler_show_error('user_banned');
        }

    }

    private $ui = null;
    private $room;
    private $link;
    private $roomId;

    public function __construct(user_base_api $ui) {
        $this->ui = $ui;
        $this->roomId = $this->getRoomId();

        $private = is_null($this->ui->get_param('private')) ? false : $this->ui->get_param('private');
        $this->link = $this->ui->get_param('link');
        $this->room = $private ? RoomFactory::loadPrivate($this->roomId) : RoomFactory::load($this->roomId);
    }

    public function handle() {
        switch($this->getIMAction()) {
            case 'add':
                $this->addComment();
                break;
            case 'edit':
                $this->editMessage();
                break;
            case 'delete':
                $this->deleteMessage();
                break;
            case 'restore':
                $this->restoreMessage();
                break;
            case 'vote':
                $this->voteForMessage();
                break;
            case 'abuse':
                $this->abuseMessage();
                break;
            default:
                $this->link .= '#comments';
                break;
        }
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->ui->redirect($this->link);
        }

        return true;
    }

    private function abuseMessage() {
        $messageId = isset($this->ui->rewrite[4]) ? $this->ui->rewrite[4] : null;
        if(is_null($messageId)) throw new WrongMessageException();
        $message = $this->room->getMessage($messageId);
        $message->abuse($this->ui->user['id']);
        if($message->getAbuseCount() == 3) {
            $m = new VPA_table_user_msgs();
            $u = new VPA_table_users();
            $userInfo = $u->get_first_fetch(array('id' => $message->getOwner()));
            $newsLink = '/'.str_replace('-', '/', $this->roomId).'/#cid_'.$message->getID();
            $msg = 'Много жалоб на сообщение пользователя <a href="/profile/'.$message->getOwner().'">'.$userInfo['nick'].'</a>
            в новости <a href="'.$newsLink.'">'.$newsLink.'</a>';
            $params = array(
                'aid' => 57,
                'uid' => 83368,
                'content' => $msg,
                'cdate' => time(),
                'private' => 1
            );
            $m->add($ret, $params);
            $params['uid'] = 94637;
            $m->add($ret, $params);
            $params['uid'] = 107011;
            $m->add($ret, $params);
            unset($m);
        }

        $this->ui->tpl->assign('data', array('status' => 1));
    }

    private function voteForMessage() {
        $messageId = isset($this->ui->rewrite[4]) ? $this->ui->rewrite[4] : null;
        $direction = isset($this->ui->rewrite[5]) ? $this->ui->rewrite[5] : null;
        if(is_null($messageId)) throw new WrongMessageException();
        if(is_null($direction)) throw new WrongVoteException();
        $message = $this->room->getMessage($messageId);
        $rateInc = array(0, 0);
        if($direction == 'up') {
            $rateInc[1] = 1;
        }
        else {
            if($direction == 'down') {
                $rateInc[0] = 1;
            }
        }
        $message->rate($this->ui->user['id'], $rateInc);
        $messageInfo = $message->getData();
        $rating = $messageInfo['rating'];
        $this->ui->tpl->assign('data', array('status' => 1, 'rating' => $rating));
    }

    private function restoreMessage() {
        $messageId = isset($this->ui->rewrite[4]) ? $this->ui->rewrite[4] : null;
        if(is_null($messageId)) throw new WrongMessageException();
        $message = $this->room->getMessage($messageId);
        if($this->ui->user['id'] == $message->getOwner() || $this->ui->tpl->isModer()) {
            $message->restore();
        }
        $this->ui->tpl->assign('data', array('status' => 1, 'message' => $message->getData()));
    }

    private function deleteMessage() {
        $messageId = isset($this->ui->rewrite[4]) ? $this->ui->rewrite[4] : null;
        if(is_null($messageId)) throw new WrongMessageException();
        $message = $this->room->getMessage($messageId);

        if($this->ui->user['id'] == $message->getOwner() || $this->ui->tpl->isModer()) {
            $message->delete();
            $this->updateUserRating($message->getOwner(), -1);
        }

        $this->ui->tpl->assign('data', array('status' => 1, 'message' => $message->getData()));
    }

    private function editMessage() {
        $messageId = $this->ui->get_param('messageId');
        if(is_null($messageId)) throw new WrongMessageException();
        $message = $this->room->getMessage($messageId);
        $content = trim(htmlspecialchars($this->ui->get_param('content'), ENT_NOQUOTES));

        $content = $this->clearTrash($content);
        $this->checkContentForEmpty($content);
        $this->checkForSpam($content);
        $content = $this->reduceSmiles($content);
        $content = $this->convertEncoding($content);

        if($this->ui->user['id'] != $message->getOwner()) {
            throw new WrongMessageOwnerException();
        }

        $message->setContent($content);
        $this->link .= '#cid_'.$message->getID();

        //subscription
        $subscribe = (int)$this->ui->get_param('subscribe');
        if($subscribe == 1) {
            $this->room->subscribe($this->ui->user['id']);
        }
        else {
            $this->room->unSubscribe($this->ui->user['id']);
        }

        $formatter = new MessageFormatter($message, $this->ui, $this->room->getId());
        $this->ui->tpl->assign('data', array('status' => 1, 'message' => $formatter->format()));
    }

    private function addComment() {
        $msg = array();
        $msg['owner'] = $this->ui->user['id'];
        $msg['content'] = $this->ui->get_param('content');
        $msg['content'] = trim(htmlspecialchars($msg['content'], ENT_NOQUOTES));

        $msg['content'] = $this->clearTrash($msg['content']);
        $this->checkContentForEmpty($msg['content']);
        $this->checkForSpam($msg['content']);
        $msg['content'] = $this->reduceSmiles($msg['content']);
        $msg['content'] = $this->convertEncoding($msg['content']);

        $msg['parent'] = $this->ui->get_param('parentId');

        $message = new Message($msg);
        $this->room->addMessage($message);
        $this->link .= '#cid_'.$message->getID();

        //subscription
        $subscribe = (int)$this->ui->get_param('subscribe');
        if($subscribe == 1) {
            $this->room->subscribe($this->ui->user['id']);
        }
        else {
            $this->room->unSubscribe($this->ui->user['id']);
        }

        $this->updateUserRating($msg['owner'], 1);

        $formatter = new MessageFormatter($message, $this->ui, $this->room->getId());

        $this->notifyNewMessage($message);

        $this->ui->tpl->assign('data', array('status' => 1, 'message' => $formatter->format()));
    }

    private function convertEncoding($text) {

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if($this->room->getDataMap() instanceof RoomMysqlDataMap) {
                $text = iconv('UTF-8', 'WINDOWS-1251', $text);
            }
        } else {
            if($this->room->getDataMap() instanceof RoomMongoDataMap) {
                $text = iconv('WINDOWS-1251', 'UTF-8', $text);
            }
        }

        return $text;
    }

    private function notifyNewMessage(IMessage $message) {
        $this->notifyAnswer($message);
        $this->notifySubscribed($message);
    }

    private function notifySubscribed(IMessage $message) {
        $subscribers = $this->room->getSubscribed();
        if(count($subscribers) > 0) {
            foreach($subscribers as $subscriber) {
                if($subscriber['uid'] == $message->getOwner()) continue;
                $type = explode('-', $this->room->getId());
                $item = $this->getDataByType($type);
                if(!is_null($item)) {
                    $title = $item['name'];
                    $link = 'http://www.popcornnews.ru'.$this->ui->get_param('link');
                    Notify::toEmail($subscriber, $this->ui, $this->room->getId(), $title, $link);
                }
            }
        }
    }

    private function notifyAnswer(IMessage $message) {
        if(!is_null($message->getParent())) {
            $parent = $this->room->getMessage($message->getParent());
            if($parent->getOwner() != $message->getOwner()) {
                $type = explode('-', $this->room->getId());
                $item = $this->getDataByType($type);
                if(!is_null($item)) {
                    $title = $item['name'];
                    $link = $this->ui->get_param('link');
                    $comment_link = sprintf('%s/#cid_%s', $link, $message->getID());
                    Notify::toNotify($parent->getOwner(), $message->getOwner(), $title, $link, $comment_link);
                }
            }
        }
    }

    /**
     * режем смайлы до 5 штук подряд
     * @param $msg
     *
     * @return mixed
     */
    private function reduceSmiles($msg) {
        $pattern = '/(\[\s*[\w|-]*\s*\]\s*){6,}/is';
        $replacement = '\1\1\1\1\1';
        $msg = preg_replace($pattern, $replacement, $msg);

        return $msg;
    }

    private function checkForSpam($msg) {
        list($roomName, $roomNum) = explode('-', $this->room->getId());
        if($this->ui->check_for_spam($msg, $roomName, $roomNum)) {
            throw new SpamCommentException();
        }
        if(strlen($msg) <= 1 || preg_match('@^[\d\s]*$@Uis', $msg)) {
            throw new SpamCommentException();
        }
    }

    private function checkContentForEmpty($msg) {
        if(empty($msg)) {
            throw new EmptyContentException();
        }
    }

    private function getRoomId() {
        $roomId = $this->ui->get_param('roomId');

        if(is_null($roomId)) {
            $roomId = isset($this->ui->rewrite[3]) ? $this->ui->rewrite[3] : null;
            if(is_null($roomId)) {
                throw new WrongRoomException();
            }

            return $roomId;
        }

        return $roomId;
    }

    private function getIMAction() {
        return isset($this->ui->rewrite[2]) ? $this->ui->rewrite[2] : $this->ui->get_param('imAction');
    }

    private function updateUserRating($uid, $amount) {
        $o_user = new VPA_table_users;
        $o_user->set($r, array('rating' => 'rating+'.$amount), $uid);
    }

    private function getDataByType($type) {
        $item = null;
        switch($type[0]) {
            case 'news':
                $table = new VPA_table_news();
                break;
            default:
                $table = null;
        }
        if(!is_null($table)) {
            $table->get($ret, array('id' => $type[1]), null, 0, 1);
            $ret->get_first($item);
        }
        return $item;
    }

    private function clearTrash($content) {
        $content = preg_replace('/(jQuery[\d]+_[\d]+)/', '', $content);
        return $content;
    }

}
