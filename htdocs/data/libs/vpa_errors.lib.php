<?php

class vpa_errors {
	public $errors;

	public function vpa_errors() {
		$this->errors = array(
			'no_email' => array('title' => 'Пустой E-mail', 'msg' => 'Вы не указали свой e-mail', 'link' => '', 'header' => HTTP_STATUS_200),
			'empty_pass' => array('title' => 'Пустой пароль', 'msg' => 'Вы не указали пароль', 'link' => '', 'header' => HTTP_STATUS_200),
			'no_login' => array('title' => 'Вы не зарегистрированы', 'msg' => 'Для доступа к данной странице, вы должны быть авторизованы. Если вы еще не зарегистрированы на нашем сайте, вы можете сделать это перейдя по ссылке "<a href="/register/">регистрация</a>".', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
			'auth_fail' => array('title' => 'Ошибка авторизации', 'msg' => 'Пароль неверен, проверьте раскладку клавиатуры и не нажата ли клавиша Caps Lock', 'link' => '', 'header' => HTTP_STATUS_200),
			'404' => array('title' => 'Страница не найдена', 'msg' => 'Вы запросили страницу, которой не существует. Убедитесь что вы правильно набрали адрес страницы, или попробуйте найти нужную вам страницу зайдя с главной.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_404),
			'401' => array('title' => 'Требуется авторизация', 'msg' => 'Ошибка при выполнение этого действия', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_404),
			'small_rating' => array('title' => 'Ваш рейтинг слишком мал', 'msg' => 'Для выполнения этого дейтствия вам необходимо иметь больший рейтинг. Вы можете поднять рейтинг с помощью добавлений комментариев к новостям или закачивая на сайт фотографии ваших любимых актеров или певцов.', 'link' => '', 'header' => HTTP_STATUS_403),
			'db_error' => array('title' => 'Ошибка', 'msg' => 'Во время выполнения операции произошла ошибка. Попробуйте повторить операцию попозже, и если ошибка повториться - обратитесь к администратору.', 'link' => '', 'header' => HTTP_STATUS_200),
			'short_query' => array('title' => 'Короткий запрос', 'msg' => 'Вы ввели слишком короткий запрос. Его длина не должна быть менее 3-х символов.', 'link' => '', 'header' => HTTP_STATUS_200),
			'unsubscribe' => array('title' => 'Вы успешно отписались', 'msg' => 'Вы отписались от получения писем о появлении новых сообщений.', 'link' => '', 'header' => HTTP_STATUS_200),
			'user_register' => array('title' => 'Вы успешно зарегистрировались.', 'msg' => 'Для окончания регистрации Вы должны подтвердить свой почтовый ящик, поэтому на указанный вами e-mail было выслано письмо с кодом подтверждения.', 'link' => '', 'header' => HTTP_STATUS_200),
			'user_commit' => array('title' => 'Вы успешно зарегистрировались.', 'msg' => 'Поздравляем! Ваш аккаунт активирован.', 'link' => '', 'header' => HTTP_STATUS_200),
			'user_reject' => array('title' => 'Указанный вами код неверен.', 'msg' => 'Указанный вами код неверен, проверьте правильность скопированной вами ссылки.', 'link' => '', 'header' => HTTP_STATUS_200),
			'limit_photos_exceed' => array('title' => 'Лимит закачки фотографий превышен', 'msg' => 'Вы исчерпали лимит аплоада фотографий (6 фотографий в день) на сегодня.', 'link' => '', 'header' => HTTP_STATUS_200),
			'email_failed' => array('title' => 'Email не корректен.', 'msg' => 'Email не может быть пустым.', 'link' => '', 'header' => HTTP_STATUS_200),
			'pass_sended' => array('title' => 'Пароль выслан.', 'msg' => 'Ваш пароль был выслан на почту, указанную вами при регистрации.', 'link' => '', 'header' => HTTP_STATUS_200),
			'user_not_found' => array('title' => 'Пользователь не найден', 'msg' => 'Зарегистрированного пользователя с таким e-mail-ом не существует.', 'link' => '', 'header' => HTTP_STATUS_200),
			'subscribe_successful' => array('title' => 'Вы фан !', 'msg' => 'Поздравляем вас ! Вы вступили в группу.', 'link' => '', 'header' => HTTP_STATUS_200),
			'unsubscribe_successful' => array('title' => 'Вы покинули группу', 'msg' => '', 'link' => '', 'header' => HTTP_STATUS_200),
			'empty_msg' => array('title' => 'Пустое сообщение', 'msg' => 'Пустые сообщения не добавляются.', 'link' => '', 'header' => HTTP_STATUS_200),
			'friend_sent' => array('title' => 'Вы уже добавляли его в друзья', 'msg' => 'Вы уже добавляли его в друзья, поэтому второй раз запрос отправлен не будет.', 'link' => '', 'header' => HTTP_STATUS_200),
			'not_confirmed' => array('title' => 'Вы не в ступили в группу', 'msg' => 'Вы не вступили в группу и не являтесь фаном.', 'link' => '', 'header' => HTTP_STATUS_200),
			'user_banned' => array('title' => 'Вы не можете писать сообщения', 'msg' => 'Вы были забанены и на данный момент не можете писать сообщения.', 'link' => '', 'header' => HTTP_STATUS_200),
			'user_spamer' => array('title' => 'Вы не можете писать спам сообщения', 'msg' => 'Вы не можете писать одинаковые сообщения к одной теме, это считается спамом.', 'link' => '', 'header' => HTTP_STATUS_200),
			'file_error' => array('title' => 'Файл должен быть изображением', 'msg' => 'Файл должен быть изображением', 'link' => '', 'header' => HTTP_STATUS_200),
			'too_many_files' => array('title' => 'Превышен лимит файлов', 'msg' => 'Превышен лимит файлов', 'link' => '', 'header' => HTTP_STATUS_200),

			'no_money' => array('title' => 'Этот подарок платный', 'msg' => 'У вас недостаточно средств. Пополните баланс', 'link' => '', 'header' => HTTP_STATUS_200),
			'free_gifts_limit' => array('title' => 'Лимит исчерпан', 'msg' => 'Уважаемый пользователь!<br />Вы можете отправить 1 бесплатный подарок в сутки.<br />Ваш лимит на сегодня исчерпан.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),

			'ask_already_exists' => array('title' => 'Такой вопрос уже задавали', 'msg' => 'Уважаемый пользователь!<br />Такой вопрос уже задавали, попробуйте поискать его на других страницах.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
			// community
			'community_delete_albums_have_photos' => array('title' => 'У этого альбома еще есть фотографии', 'msg' => 'У этого альбома еще есть фотографии.<br />Удалите сначала их.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
			'community_user_is_not_a_member' => array('title' => 'Вы не вступили в эту группу', 'msg' => 'Вы не вступили в эту группу.<br />Сначала нужно вступить.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
			'community_no_access_to_album' => array('title' => 'Вы не вступили в эту группу', 'msg' => 'Вы не можете просматривать альбомы закрытой группы.<br />Сначала нужно вступить.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
			// yourstyle_image_tranform
			'yourstyle_image_tranform' => array('title' => 'Не удалось преобразовать изображение', 'msg' => 'Не удалось преобразовать изображение<br />Попробуйте закачать изоюражение мешьшего размера.', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),

		    // fanfics errors
		    'fanfic_content_4000' => array('title' => 'Ошибка фанфика', 'msg' => 'Графа «текст» не может содержать менее 4000 знаков', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
		    'fanfic_announce' => array('title' => 'Ошибка фанфика', 'msg' => 'Графа «анонс» не может быть пустой', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),
		    'fanfic_name' => array('title' => 'Вы не указали тему для фанфика', 'link' => '<a href="javascript:window.history.back();">Назад</a>', 'header' => HTTP_STATUS_200),

            'private_settings' => array(
                'title' => 'Ошибка приватности',
                'msg' => 'Настройки приватности не позволяют вам писать сообщения',
                'link' => '<a href="javascript:window.history.back();">Назад</a>',
                'header' => HTTP_STATUS_200),
		);
	}

	public function get($error) {
		$data = $this->errors[$error];
		if (!empty($data)) {
			return $data;
		}
		return $this->errors['404'];
	}
}
