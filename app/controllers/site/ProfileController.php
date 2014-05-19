<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\MessageDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\dataMaps\UserDataMapPaginator;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\persons\PersonFactory;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\im\Message;
use popcorn\model\im\MessageFactory;
use Slim\Route;

/**
 * Class ProfileController
 * @package popcorn\app\controllers
 */
class ProfileController extends GenericController implements ControllerInterface {

	/**    @var User $profile */
	static private $profile;
	static private $twigData = [];

	public function getRoutes() {

		//callback для страницы пользователей и профиля
		//проверяет существование пользователя и права доступа
		$profileMiddleware = function (Route $route) {
			$profileId = $route->getParam('profileId');

			//Существование пользователя в базе, если нет - 404
			if (!(UserFactory::getUser($profileId) instanceof User)) {
				$this->getSlim()->notFound();
			}

			//Нужно быть авторизованным, чтобы смотреть чужой или свой профиль
			//Если нет - показываем ошибку авторизации
			if (UserFactory::getCurrentUser() instanceof GuestUser) {
				$this->getSlim()->error(new NotAuthorizedException());
			}

			self::profileSetup($profileId);
		};

		$justForMeMiddleware = function (Route $route) {
			if (UserFactory::getCurrentUser()->getId() > 0) {
				return true;
			}

			$this->getSlim()->notFound();
		};

		$this
			->getSlim()
			->post('/profile/exit', function () {
				UserFactory::logout();

				$this->getSlim()->setEncryptedCookie(USER::COOKIE_USER_NAME, 0, time() - 3600, '/');
				$this->getSlim()->redirect(base64_decode($this->getSlim()->request()->post('returnTo')));
			});


		//Личные сообщения
		{
			$this
				->getSlim()
				->get('/im(/companion:companionId)', $justForMeMiddleware, [$this, 'imPage'])
				->conditions(['companionId' => '\d+']);

			$this
				->getSlim()
				->map('/im/create', $justForMeMiddleware, [$this, 'imCreate'])
				->via('GET', 'POST');
		}


		$this
			->getSlim()
			->group('/profile/:profileId', $profileMiddleware, function () use ($justForMeMiddleware) {

				//@example /profile/1
				$this
					->getSlim()
					->get('', [new ProfileController(), 'profileDispatcher']);

				{ #Только для профиля
					//@example /profile/1/form
					$this
						->getSlim()
						->get('/form', $justForMeMiddleware, [$this, 'profileEditForm']);

					//@example /profile/1/photos/del
					$this
						->getSlim()
						->get('/photos/del', $justForMeMiddleware, [$this, 'profileEditForm']);

					//@example /profile/1/blacklist
					$this
						->getSlim()
						->get('/blacklist', $justForMeMiddleware, [$this, 'profileBlackList']);

					$this
						->getSlim()
						->get('/persons/news', $justForMeMiddleware, [$this, 'personsNewsPage']);

					$this
						->getSlim()
						->map('/persons/manage', $justForMeMiddleware, [$this, 'fansAddDispatcher'])
						->via('GET', 'POST');

					$this
						->getSlim()
						->map('/messages/new', $justForMeMiddleware, [$this, 'newMessageDispatcher'])
						->via('GET', 'POST');
				}

				//@example /profile/1/friends

				$this
					->getSlim()
					->get('/friends(/page:pageId)', [$this, 'friendsPage'])
					->conditions(['pageId' => '\d+']);

				$this
					->getSlim()
					->get('/persons', [$this, 'fansPage']);


			});


	}

	/**
	 * Все, что может понадобится для всех страниц пользователя
	 */
	public static function profileSetup($profileId) {
		$currentUser = UserFactory::getCurrentUser();

		self::$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();
		self::$twigData['inBlackList'] = $dataMap->checkInBlackList(UserFactory::getCurrentUser(), self::$profile);
		self::$twigData['isMyProfile'] = false;


		//Авторизованный пользователь смотрит свой профиль
		if ($currentUser->getId() == $profileId) {
			self::$twigData['isMyProfile'] = true;
			self::$twigData['notifyCounter'] = ['friends' => $dataMap->getNewFriendsCount($currentUser)];
		}

		self::getTwig()->addGlobal('profileHelper', self::$twigData);

	}


	/**
	 * Диспетчер пользователей
	 * Если пользователь авторизован и мы находимся на своей странице, то покажем свой профиль
	 * В ином случае покажем страницу пользователя
	 */
	public static function profileDispatcher() {

		$dataMap = new UserDataMap();

		$activeStatus = $dataMap->getActiveStatus(self::$profile);
		$statuses = $dataMap->getStatuses(self::$profile);

		self::$profile->setExtra('status', $activeStatus);

		self::getTwig()
			->display('/profile/ProfilePage.twig', array(
				'profile' => self::$profile,
				'statusList' => $statuses
			));
	}

	public static function profileEditForm($profileId) {

		$profile = UserFactory::getUser($profileId);
		$personDataMap = new PersonDataMap();

		self::getTwig()
			->display('/profile/ProfileForm.twig', array(
				'profile' => $profile,
				'countries' => UserFactory::getCountries(),
				'persons' =>  $personDataMap->getPersonsLits(),
			)
		);

	}


	public static function profileBlackList($profileId) {
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePage.twig', array(
				'profile' => $profile
			));
	}

	public static function friendsPage($profileId, $listPage = 1) {
		$profile = UserFactory::getUser($profileId);

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
				'profile' => $profile,
				'friends' => $friends,
				'paginator' => [
					'overall' => $pages,
					'active' => $listPage
				]
			]);
	}

	public static function fansPage($profileId) {
		$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();
		$persons = $dataMap->getFans($profile);

		self::getTwig()
			->display('/profile/ProfilePersons.twig', [
				'profile' => $profile,
				'persons' => $persons
			]);

	}

	public static function fansForm($profileId) {
		$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();
		$fans = $dataMap->getFans($profile);

		$fansArray = [];

		foreach ($fans as $fan) {
			$fansArray[] = $fan->getId();
		}

		$allPersons = PersonFactory::getPersons([], 0, -1, ['name' => 'asc']);

		self::getTwig()
			->display('/profile/ProfilePersonsAdd.twig', [
				'profile' => $profile,
				'persons' => $allPersons,
				'fans' => $fansArray
			]);
	}

	public static function fansSave($profileId) {

		if (UserFactory::getUser($profileId)->getId() != UserFactory::getCurrentUser()->getId()) return false;

		$dataMap = new UserDataMap();
		$dataMap->addToFans(UserFactory::getCurrentUser(), self::getSlim()->request()->post('persons'));

		self::getSlim()->redirect(sprintf('/profile/%u/persons', $profileId));
	}

	public static function fansAddDispatcher($profileId) {

		switch (self::getSlim()->request()->getMethod()) {
			case 'GET':
				self::fansForm($profileId);
				break;
			case 'POST':
				self::fansSave($profileId);
				break;
		}
	}


	public static function personsRemovePage($profileId) {
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePersonsAdd.twig', [
				'profile' => $profile,
			]);

	}

	public static function personsNewsPage($profileId) {
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePersonsAdd.twig', [
				'profile' => $profile,
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
					'profile' => $profile,
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


}