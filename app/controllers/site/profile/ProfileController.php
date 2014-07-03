<?php

namespace popcorn\app\controllers\site\profile;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\MessageDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\dataMaps\UserDataMapPaginator;
use popcorn\model\dataMaps\YourStyleSetsDataMap;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\persons\PersonFactory;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserHash;
use popcorn\model\system\users\UserInfo;
use popcorn\model\system\users\UserSettings;
use popcorn\model\im\Message;
use popcorn\model\im\MessageFactory;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\UserInfoDataMap;
use popcorn\model\dataMaps\UserSettingsDataMap;
use Slim\Route;

/**
 * Class ProfileController
 * @package popcorn\app\controllers
 */
class ProfileController extends GenericController implements ControllerInterface {

	/**    @var User $profile */
	static protected $profile;

	static protected $profileId;

	public function registerIf() {
		$request = $this->getSlim()->request;

		if (
			preg_match('!^\/profile!', $request->getPath()) ||
			preg_match('!^\/im!', $request->getPath())
		) {
			return true;
		}

		return false;
	}

	final public function profileExistsMiddleware(Route $route) {

		$user = UserFactory::getUser($route->getParam('profileId'));

		if ($user instanceof User) {
			self::$profileId = $user->getId();
			$this->profileSetup(self::$profileId);
		} else {
			$this->getSlim()->notFound();
		}
	}

	final public function onlyForMeMiddleware(Route $route) {

		$user = UserFactory::getUser($route->getParam('profileId'));

		if (UserFactory::getCurrentUser()->getId() !== $user->getId()) {
			$this->getSlim()->notFound();
		}

	}

	public function getRoutes() {

		//@todo сделать дополнительную проверку
		$this
			->getSlim()
			->post('/profile/exit', function () {
				UserFactory::logout();

				$this->getSlim()->setEncryptedCookie(USER::COOKIE_USER_NAME, 0, time() - 3600, '/');
				$this->getSlim()->redirect(base64_decode($this->getSlim()->request->post('returnTo')));
			});


		//Личные сообщения
		{
			$this
				->getSlim()
				->get('/im(/companion:companionId)', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'imPage'])
				->conditions(['companionId' => '\d+']);

			$this
				->getSlim()
				->map('/im/create', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'imCreate'])
				->via('GET', 'POST');
		}


		$this
			->getSlim()
			->group('/profile/:profileId', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'profileExistsMiddleware'], function () {

				//@example /profile/1
				$this
					->getSlim()
					->get('', [$this, 'profileDispatcher']);

				{ #Только для профиля
					//@example /profile/1/form
					$this
						->getSlim()
						->get('/form', [$this, 'profileEditForm']);

					$this
						->getSlim()
						->post('/form', [$this, 'profileSaveForm']);

					//@example /profile/1/photos/del
					$this
						->getSlim()
						->get('/photos/del', [$this, 'profileEditForm']);

					//@example /profile/1/blacklist
					$this
						->getSlim()
						->get('/blacklist', [$this, 'profileBlackList']);

					$this
						->getSlim()
						->map('/messages/new', [$this, 'newMessageDispatcher'])
						->via('GET', 'POST');
				}

				//@example /profile/1/friends

				$this
					->getSlim()
					->get('/friends(/page:pageId)', [$this, 'friendsPage'])
					->conditions(['pageId' => '\d+']);

				$this
					->getSlim()
					->get('/sets', [$this, 'setsPage']);


			});


	}

	/**
	 * Все, что может понадобится для всех страниц пользователя
	 * @param $profileId
	 * @throws \popcorn\model\exceptions\NotAuthorizedException
	 */
	protected function profileSetup($profileId) {
		$currentUser = UserFactory::getCurrentUser();

		$profile = UserFactory::getUser($profileId);

		self::$profile = $profile;

		$dataMap = new UserDataMap();

		$data = [];

		$data['isMyProfile'] = false;
		$data['inBlackList'] = $dataMap->checkInBlackList($currentUser, self::$profile);
		$data['friendRequest'] = $dataMap->checkFriendRequest($currentUser, self::$profile);
		$data['isFriends'] = $dataMap->isFriends($currentUser, self::$profile);

		//Авторизованный пользователь смотрит свой профиль
		if ($currentUser->getId() == $profileId) {
			$data['isMyProfile'] = true;
			$data['notifyCounter'] = ['friends' => $dataMap->getNewFriendsCount($currentUser)];
		}

		$this
			->getTwig()
			->addGlobal('profileHelper', $data);

	}


	/**
	 * Диспетчер пользователей
	 * Если пользователь авторизован и мы находимся на своей странице, то покажем свой профиль
	 * В ином случае покажем страницу пользователя
	 */
	public function profileDispatcher() {

		$profile = UserFactory::getUser(self::$profileId, [
			'with' => UserDataMap::WITH_ALL
		]);

		$dataMap = new UserDataMap();

		$activeStatus = $dataMap->getActiveStatus($profile);
		$statuses = $dataMap->getStatuses($profile);

		$profile->setExtra('status', $activeStatus);

		self::getTwig()
			->display('/profile/ProfilePage.twig', array(
				'profile'    => $profile,
				'statusList' => $statuses
			));
	}

	public static function profileEditForm($profileId) {

		$profile = UserFactory::getUser($profileId);
		$personDataMap = new PersonDataMap();

		self::getTwig()
			->display('/profile/ProfileForm.twig', array(
					'profile'   => $profile,
					'countries' => UserFactory::getCountries(),
					'persons'   => $personDataMap->getPersonsLits(),
					'userError' => (isset($_SESSION['userError']) ? $_SESSION['userError'] : array()),
				)
			);

	}

	public static function profileSaveForm($profileId) {

		$request = self::getSlim()->request();

		$params = array(
			'name'                           => strip_tags(trim($request->post('name'))),
			'pass1'                          => trim($request->post('pass1')),
			'pass2'                          => trim($request->post('pass2')),
			'credo'                          => trim($request->post('credo')),
			'city'                           => trim($request->post('city')),
			'country'                        => trim($request->post('country')),
			'sex'                            => (int)$request->post('sex'),
			'show_bd'                        => (int)$request->post('show_bd'),
			'family'                         => (int)$request->post('family'),
			'meet_actor'                     => (int)$request->post('meet_actor'),
			'day'                            => (int)$request->post('day'),
			'month'                          => (int)$request->post('month'),
			'year'                           => (int)$request->post('year'),
			'daily_sub'                      => (int)$request->post('daily_sub'),
			'alert_on_new_mail'              => (int)$request->post('alert_on_new_mail'),
			'alert_on_new_guest_items'       => (int)$request->post('alert_on_new_guest_items'),
			'can_invite_to_community_groups' => (int)$request->post('can_invite_to_community_groups'),
		);

		$params['birthday'] = mktime(0, 0, 0, $params['month'], $params['day'], $params['year']);

		$profile = UserFactory::getUser($profileId);

		//Пароли отличаются
		if ($params['pass1'] != $params['pass2']) {
			$_SESSION['userError'] = 103;
			self::getSlim()->redirect('/profile/' . $profileId . '/form');
		} else {
			unset($_SESSION['userError']);
		}

		if (!empty($params['pass1'])) {
			$profile->setPassword($params['pass1']);
		}

		if (isset($_FILES) && isset($_FILES['avatara']) && !$_FILES['avatara']['error']) {
			$profile->setAvatar(
				ImageFactory::createFromUpload($_FILES['avatara']['tmp_name'])
			);
		}

		$userInfoDataMap = new UserInfoDataMap();
		$userInfo = $userInfoDataMap->findById($profile->getUserInfo()->getId());
		$userInfo->setName($params['name']);
		$userInfo->setBirthDate($params['birthday']);
		$userInfo->setCityId($params['city']);
		$userInfo->setCountryId($params['country']);
		$userInfo->setCredo($params['credo']);
		$userInfo->setSex($params['sex']);
		$userInfo->setMarried($params['family']);
		$userInfo->setMeetPerson($params['meet_actor']);
		$profile->setUserInfo($userInfo);

		$userSettingsDataMap = new UserSettingsDataMap();
		$userSettings = $userSettingsDataMap->findById($profile->getUserSettings()->getId());
		$userSettings->setDailySubscribe($params['daily_sub']);
		$userSettings->setShowBirthDate($params['show_bd']);
		$userSettings->setAlertMessage($params['alert_on_new_mail']);
		$userSettings->setAlertGuestBook($params['alert_on_new_guest_items']);
		$userSettings->setCanInvite($params['can_invite_to_community_groups']);
		$profile->setUserSettings($userSettings);

//		echo_arr($profile); die();
		UserFactory::save($profile);

		unset($_SESSION['userError']);

		self::getSlim()->redirect('/profile/' . $profileId . '/form');

	}


	public static function profileBlackList($profileId) {
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePage.twig', array(
				'profile' => $profile
			));
	}

	public static function friendsPage($profileId, $listPage = 1) {
		$profile = UserFactory::getUser($profileId, ['with' => UserDataMap::WITH_ALL]);

		$dataMap = new UserDataMap();

		$onPage = 20;
		$paginator = [];

		$friends = $dataMap->getFriends($profile,
			['myFriends' => (UserFactory::getCurrentUser()->getId() == $profileId) ? true : false],
			[($listPage - 1) * $onPage, $onPage],
			$paginator
		);

		$pages = ceil($paginator['overall'] / $onPage);

		self::getTwig()
			->display('/profile/ProfileFriends.twig', [
				'profile'   => $profile,
				'friends'   => $friends,
				'paginator' => [
					'overall' => $pages,
					'active'  => $listPage
				]
			]);
	}


	public static function imPage($companionId = null) {

		$profile = UserFactory::getCurrentUser();
		$dataMap = new MessageDataMap();

		/*$imgGen = self::getApp()->getImageGenerator();
		$image = $imgGen->convert('/data/sites/popcorn/htdocs/upload/2014/01/05/fb0bd391b7ee6df34361e57c039c2e09.jpg',
			['resize'=>'200x']
		);*/

		if ($companionId > 0) {

			$messages = $dataMap->getDialogMessages($profile, $companionId);

			self::getTwig()
				->display('/profile/ProfileMessages.twig', [
					'profile'  => $profile,
					'messages' => $messages
				]);

		} else {

			$dialogs = $dataMap->getDialogs($profile);

			self::getTwig()
				->display('/profile/ProfileMessages.twig', [
					'profile' => $profile,
					'dialogs' => $dialogs
				]);
		}


	}

	public static function imCreate() {

		$profile = UserFactory::getCurrentUser();
		$dataMap = new MessageDataMap();


	}

	public static function newMessageDispatcher($profileId) {
		switch (self::getSlim()->request()->getMethod()) {
			case 'GET':
				self::newMessageForm($profileId);
				break;
			case 'POST':
				self::newMessageSave($profileId);
				break;
		}
	}

	public static function newMessageSave() {

		$recipient = UserFactory::getUser(self::getSlim()->request()->post('recipient'));

		$message = new Message();
		$message->setSentTime(new \DateTime());
		$message->setAuthor(UserFactory::getCurrentUser());
		$message->setRecipient($recipient);
		$message->setContent(self::getSlim()->request()->post('message'));
		$message->setRead(0);
		$message->setRemovedAuthor(0);
		$message->setRemovedRecipient(0);

		MessageFactory::save($message);
	}

	public static function newMessageForm() {

		self::getTwig()
			->display('/profile/ProfileNewMessage.twig', [
				'profile' => self::$profile,
			]);

	}

	public static function siteUsers() {


	}

	public static function setsPage($profileId) {

		$profile = UserFactory::getUser($profileId, ['with' => UserDataMap::WITH_ALL]);

		$setsDataMap = new YourStyleSetsDataMap();
		$sets = $setsDataMap->getUserSets($profile);

		self::getTwig()
			->display('/profile/ProfileSets.twig', [
				'profile'   => $profile,
				'sets' => $sets,
			]);

	}


}