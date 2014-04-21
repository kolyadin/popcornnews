<?php
/**
 * User: anubis
 * Date: 09.04.13
 * Time: 20:02
 */

class Notify {

    /**
     * @param $uid кому сообщаем
     * @param $aid кто написал
     * @param $title название новости/топика/етц
     * @param $link линк на новость/топик/етц
     * @param $comment_link линк на коммент
     */
    public static function toNotify($uid, $aid, $title, $link, $comment_link) {
        $o_n = new VPA_table_notifications();
        $notify = array(
            'uid'        => $uid,
            'aid'        => $aid,
            'title'      => $title,
            'title_link' => $link,
            'link'       => $comment_link
        );
        $o_n->add($ret, $notify);
    }

    public static function toEmail($subscriber, $ui, $roomName, $title, $link) {
        $tpl = new VPA_template();
        $tpl->_init();
        $tpl->tpl('', '/mail/', 'message.php');
        $tpl->assign('title', 'Уведомление о новом комментарии');
        $tpl->assign(
            'message',
            sprintf(
                '%1$s<br>Пользователь %2$s оставил новый комментарий к новости "<a href="%3$s">%4$s</a>, за которой Вы следите (<a href="%3$s">%3$s</a>)<br><br>'.
                'Если Вы больше не хотите получать уведомления, пожалуйста, перейдите по ссылке: <a href="http://www.popcornnews.ru/unsubs/%5$s">http://www.popcornnews.ru/unsubs/%5$s</a>',
                date('d/m/Y H:i'), $ui->user['nick'], $link, $title, $roomName
            )
        );
        $letter = $tpl->make();

        html_mime_mail::getInstance()->quick_send(
            sprintf('"%s" <%s>', htmlspecialchars($subscriber['nick']), $subscriber['email']),
            'Уведомление о новом комментарии',
            $letter
        );
    }

}