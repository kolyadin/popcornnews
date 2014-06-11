<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\MailHelper;
use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\exceptions\BadAuthorizationException;
use popcorn\model\exceptions\RemindWrongEmailException;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserHash;
use popcorn\model\system\users\UserInfo;
use popcorn\model\system\users\UserSettings;
use popcorn\model\content\NullImage;
use Stringy\Stringy as S;

/**
 * Контроллер отвечает за регистрация, авторизацию, подтверждение профилей и напоминание паролей
 * В общем, эдакий контроллер-менеджер учетных записей на сайте
 *
 * Class ProfileManagerController
 * @package popcorn\app\controllers
 */
class ProfileManagerController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->map('/register(/:status)', [$this, 'register'])
			->via('GET', 'POST')
			->conditions(['status' => '(errors|success)']);

		$this
			->getSlim()
			->get('/register/:userId/:hash', [$this, 'profileConfirmation'])
			->conditions([
				'userId' => '\d+',
				'hash'   => '[a-z0-9]{40}'
			]);

		$this
			->getSlim()
			->map('/remind(/:status)', [$this, 'remind'])
			->via('GET', 'POST')
			->conditions(['status' => '(success|success2|error)']);

		$this
			->getSlim()
			->get('/remind/:userId/:hash', [$this, 'remindUserPassword'])
			->conditions([
				'userId' => '\d+',
				'hash'   => '[a-z0-9]{40}'
			]);

		$this
			->getSlim()
			->post('/performLogin', [$this, 'performLogin']);

	}

	/**
	 * Обработчик авторизации
	 *
	 * Непосредственно принимает с формы данные, проверяет и авторизует на сайте, если данные верны
	 */
	public function performLogin() {

		$request = $this->getSlim()->request();

		if (UserFactory::login($request->post('email'), $request->post('pass'))) {

			$user = UserFactory::getCurrentUser();

			$this->getSlim()->setEncryptedCookie(
				User::COOKIE_USER_NAME,
				base64_encode(str_rot13($user->getId() . '~' . $user->getUserHash()->getSecurityHash())),
				'1 day'
			);

			if ($this->getSlim()->request()->get('back')) {
				$this->getSlim()->redirect(
					base64_decode($this->getSlim()->request()->get('back'))
				);
			} else {
				$this->getSlim()->redirect(
					sprintf('/profile/%u', $user->getId())
				);
			}


		} else {
			$this->getSlim()->error(new BadAuthorizationException());
		}

	}

	/**
	 * Подтверждение кода активации (в электронной почте)
	 *
	 * @param int $userId
	 * @param string $hash
	 */
	public function profileConfirmation($userId, $hash) {

		$user = UserFactory::getUser($userId);

		if ($user->getEnabled()) {
			$this->getSlim()->redirect(sprintf('/profile/%u', $user->getId()));
		}

		if ($user->getRegCode() == $hash) {

			$user->setEnabled(true);
			UserFactory::save($user);

			$this->getSlim()->setEncryptedCookie(
				User::COOKIE_USER_NAME,
				base64_encode(str_rot13($user->getId() . '~' . $user->getUserHash()->getSecurityHash())),
				'1 day', '/'
			);

			$this->getSlim()->redirect(
				sprintf('/profile/%u', $user->getId())
			);
		}

	}

	public function register($status = null) {

		switch ($this->getSlim()->request()->getMethod()) {
			case 'POST':
				$this->createProfile();
				break;
			case 'GET':
				$this->registerPage($status);
				break;
		}
	}

	private function createProfile() {
		$request = $this->getSlim()->request();

		$params = array(
			'name'       => strip_tags(trim($request->post('name'))),
			'nick'       => strip_tags(trim($request->post('nick'))),
			'email'      => trim($request->post('email')),
			'pass1'      => trim($request->post('pass1')),
			'pass2'      => trim($request->post('pass2')),
			'credo'      => trim($request->post('credo')),
			'city'       => trim($request->post('city')),
			'country'    => trim($request->post('country')),
			'sex'        => (int)$request->post('sex'),
			'show_bd'    => (int)$request->post('show_bd'),
			'family'     => (int)$request->post('family'),
			'meetPerson' => (int)$request->post('meetPerson'),
			'day'        => (int)$request->post('day'),
			'month'      => (int)$request->post('month'),
			'year'       => (int)$request->post('year'),
			'daily_sub'  => (int)$request->post('daily_sub'),
			'rules'      => (int)$request->post('rules'),
		);

		$params['birthday'] = mktime(0, 0, 0, $params['month'], $params['day'], $params['year']);

		$_SESSION['userData'] = $params;

		if (!$params['nick'] || !$params['email'] || !$params['pass1'] || !$params['pass2']) {
			$_SESSION['userError'] = 104;
			$this->getSlim()->redirect('/register/errors');
		} else {
			unset($_SESSION['userError']);
		}

		$userDataMap = new UserDataMap();

		//Ник уже используется
		if ($userDataMap->findBy(array('nick' => $params['nick'])) instanceof User) {
			$_SESSION['userError'] = 110;
			$this->getSlim()->redirect('/register/errors');
		} else {
			unset($_SESSION['userError']);
		}

		//Мыло уже используется
		if ($userDataMap->findBy(array('email' => $params['email'])) instanceof User) {
			$_SESSION['userError'] = 111;
			$this->getSlim()->redirect('/register/errors');
		} else {
			unset($_SESSION['userError']);
		}


		//Не согласился с правилами - показываем эти самые правила
		if ($params['rules'] != 1) {
			$_SESSION['userError'] = 102;
			$this->getSlim()->redirect('/rules');
		} else {
			unset($_SESSION['userError']);
		}

		//Пароли отличаются
		if ($params['pass1'] != $params['pass2']) {
			$_SESSION['userError'] = 103;
			$this->getSlim()->redirect('/register/errors');
		} else {
			unset($_SESSION['userError']);
		}

		$user = new User();

		$user->setNick($params['nick']);
		$user->setEmail($params['email']);
		$user->setPassword($params['pass1']);
		$user->setCreateTime(time());
		$user->setLastVisit(time());
		$user->setEnabled(0);
		$user->setRating(0);
		$user->setType(User::USER);

		if (isset($_FILES) && isset($_FILES['avatara']) && !$_FILES['avatara']['error']) {
			$user->setAvatar(ImageFactory::createFromUpload($_FILES['avatara']['tmp_name']));
		} else {
			//Аватарки нет
			$user->setAvatar(new NullImage());
		}

		$user->setUserInfo(new UserInfo());
		$user->getUserInfo()->setName($params['name']);
		$user->getUserInfo()->setBirthDate($params['birthday']);
		$user->getUserInfo()->setCityId($params['city']);
		$user->getUserInfo()->setCountryId($params['country']);
		$user->getUserInfo()->setCredo($params['credo']);
		$user->getUserInfo()->setMeetPerson($params['meetPerson']);

		$user->setUserSettings(new UserSettings());
		$user->getUserSettings()->setDailySubscribe($params['daily_sub'] == 1 ? true : false);
		$user->getUserSettings()->setShowBirthDate($params['show_bd'] == 1 ? true : false);

		$user->setUserHash(new UserHash());
		$user->getUserHash()->setSecurityHash();


		/*
		$user->getUserSettings()->setAlertGuestBook(1);
		$user->getUserSettings()->setAlertMessage(1);
		$user->getUserSettings()->setCanInvite(1);
		$user->getUserSettings()->setDailySubscribe(1);
		*/

		UserFactory::save($user);

		$mail = MailHelper::getInstance();
		$mail->setFrom('robot@popcornnews.ru');
		$mail->addAddress($user->getEmail());
		$mail->Subject = sprintf('Регистрация на сайте %s', $_SERVER['HTTP_HOST']);
		$mail->msgHTML(
			$this->getTwig()->render('/mail/Register.twig', array(
				'user' => array(
					'id'   => $user->getId(),
					'hash' => $user->getRegCode()
				)
			))
		);
		$mail->send();

		unset($_SESSION['userData']);
		unset($_SESSION['userError']);

		$this->getSlim()->redirect('/register/success');
	}

	private function registerPage($status) {

		$stmt = PDOHelper::getPDO()->prepare('SELECT id,name FROM pn_countries ORDER BY rating');
		$stmt->execute();
		$countries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$tpl = [
			'errors'    => ($status == 'errors') ? true : false,
			'success'   => ($status == 'success') ? true : false,
			'persons'   => PersonFactory::getPersons([], 0, -1),
			'countries' => $countries,

			'userData'  => isset($_SESSION['userData']) ? $_SESSION['userData'] : ['daily_sub' => 1],
			'userError' => isset($_SESSION['userError']) ? $_SESSION['userError'] : []
		];

		$this->getTwig()->display('RegisterPage.twig', $tpl);
	}

	public function remind($status = null) {
		switch ($this->getSlim()->request()->getMethod()) {
			case 'POST':
				$this->performRemind();
				break;
			case 'GET':
				$this->remindPage($status);
				break;
		}
	}

	public function remindPage($status) {
		$this
			->getTwig()
			->display('/RemindPasswordPage.twig', [
				'status' => $status
			]);
	}

	public function remindUserPassword($userId, $securityHash) {

		$dataMap = new UserDataMap();

		$user = $dataMap->findBy(array('id' => $userId, 'sha1(concat(id,password))' => $securityHash));

		if (!($user instanceof User)) {
			$this->getSlim()->redirect('/remind');
		}

		$randomPass = S::create(filter_var(microtime(1), FILTER_SANITIZE_NUMBER_INT))
			->replace(0, '')
			->reverse()
			->substr(0, 8)
			->shuffle();

		$user->setPassword($randomPass);
		UserFactory::save($user);

		$mail = MailHelper::getInstance();
		$mail->setFrom('robot@popcornnews.ru');
		$mail->addAddress($user->getEmail());
		$mail->Subject = sprintf('Вам создан новый пароль на сайте %s', $_SERVER['HTTP_HOST']);
		$mail->msgHTML(
			$this->getTwig()->render('mail/RemindFinish.twig', array(
				'user' => array(
					'id'          => $user->getId(),
					'newPassword' => $randomPass
				)
			))
		);

		if ($mail->send()) {
			$this->getSlim()->redirect('/remind/success2');
		}

	}

	public function performRemind() {

		$email = $this->getSlim()->request()->post('email');
		$email = substr($email, 0, 150);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->getSlim()->error(new RemindWrongEmailException(['email' => $email]));
		}

		$dataMap = new UserDataMap();

		$user = $dataMap->findBy(array('email' => $email));

		if (!($user instanceof User)) {
			$this->getSlim()->error(new RemindWrongEmailException(['email' => $email]));
		}

		$changePasswordHash = sha1($user->getId() . $user->getPassword());

		$mail = MailHelper::getInstance();
		$mail->setFrom('robot@popcornnews.ru');
		$mail->addAddress($user->getEmail());
		$mail->Subject = sprintf('Восстановление пароля на сайте %s', $_SERVER['HTTP_HOST']);
		$mail->msgHTML(
			$this->getTwig()->render('mail/RemindStart.twig', array(
				'user' => array(
					'id'                 => $user->getId(),
					'changePasswordHash' => $changePasswordHash
				)
			))
		);

		if ($mail->send()) {
			$this->getSlim()->redirect('/remind/success');
		}
	}
}