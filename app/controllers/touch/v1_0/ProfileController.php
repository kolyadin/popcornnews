<?php
namespace popcorn\app\controllers\touch\v1_0;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\MessageDataMap;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\dataMaps\MessageWallDataMap;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\im\MessageWall;
use popcorn\model\im\MessageWallFactory;
use Slim\Route;

/**
 * Class ProfileController
 * @package popcorn\app\controllers
 */
class ProfileController extends GenericController implements ControllerInterface {

	/**	@var User $profile */
	static private $profile;
	static private $twigData = [];

	public function getRoutes() {

		$profileMiddleware = function(Route $route) {
			$profileId = $route->getParam('profileId');

			//Существование пользователя в базе, если нет - 404
			if (!(UserFactory::getUser($profileId) instanceof User)){
				$this->getSlim()->notFound();
			}

			//Нужно быть авторизованным, чтобы смотреть чужой или свой профиль
			//Если нет - показываем ошибку авторизации
			if (!UserFactory::getCurrentUser()->getId()){
				$this->getSlim()->error(new NotAuthorizedException());
			}

			ProfileController::profileSetup($profileId);
		};

		$justForMeMiddleware = function(Route $route) {
			if (UserFactory::getCurrentUser()->getId() > 0){
				return true;
			}

			$this->getSlim()->error(new NotAuthorizedException());
//			$this->getSlim()->notFound();
		};

		$this
			->getSlim()
			->get('/profile/exit', function () {
				UserFactory::logout();

				$this->getSlim()->setEncryptedCookie(USER::COOKIE_USER_NAME, 0, time() - 3600, '/');
				$this->getSlim()->redirect('/');
			});

		$this
			->getSlim()
			->group('/profile/:profileId', $profileMiddleware, function () use($justForMeMiddleware) {

				$this
					->getSlim()
					->get('', [new ProfileController(), 'profileDispatcher']);

				//@example /profile/1/friends
				$this
					->getSlim()
					->get('/friends', [new ProfileController(), 'friendsPage']);

				//@example /profile/1/statuses
				$this
					->getSlim()
					->get('/statuses', [new ProfileController(), 'statusesPage']);

				$this
					->getSlim()
					->get('/newWallMessage', [new ProfileController(), 'newWallMessageForm']);

				$this
					->getSlim()
					->post('/addWallMessage', [new ProfileController(), 'newWallMessageSave']);

				$this
					->getSlim()
					->get('/newStatusMessage', [new ProfileController(), 'newStatusMessageForm']);

				$this
					->getSlim()
					->post('/addStatusMessage', [new ProfileController(), 'newStatusMessageSave']);


			});

		// Личные сообщения (Диалоги)
		$this
			->getSlim()
			->get('/im(/companion:companionId)', $justForMeMiddleware, array(new ProfileController(),'imPage'))
			->conditions(['companionId' => '\d+']);

		$this
			->getSlim()
			->map('/im/create', $justForMeMiddleware, array(new ProfileController(),'imCreate'))
			->via('GET','POST');

	}

	/**
	 * Все, что может понадобится для всех страниц пользователя
	 */
	public static function profileSetup($profileId){
		$currentUser = UserFactory::getCurrentUser();

		self::$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();
		self::$twigData['inBlackList'] = $dataMap->checkInBlackList(UserFactory::getCurrentUser(),self::$profile);
		self::$twigData['isMyProfile'] = false;


		//Авторизованный пользователь смотрит свой профиль
		if ($currentUser->getId() == $profileId){
			self::$twigData['isMyProfile'] = true;
			self::$twigData['notifyCounter'] = ['friends' => $dataMap->getNewFriendsCount($currentUser)];
		}

		self::getTwig()->addGlobal('profileHelper',self::$twigData);

	}


	/**
	 * Диспетчер пользователей
	 * Если пользователь авторизован и мы находимся на своей странице, то покажем свой профиль
	 * В ином случае покажем страницу пользователя
	 */
	public static function profileDispatcher(){

		$currentUser = UserFactory::getCurrentUser();

		$dataMap = new UserDataMap();

		$activeStatus	= $dataMap->getActiveStatus(self::$profile);
		$friends		= $dataMap->getFriends(self::$profile);

		$dataMapCurrent = new MessageWallDataMap();
		if (self::$profile->getId() == $currentUser->getId()) {
			$wallMessages = $dataMapCurrent->getMyWallMessages($currentUser, self::$profile);
		} else {
			$wallMessages = $dataMapCurrent->getWallMessages(self::$profile);
		}

		self::$profile->setExtra('status',$activeStatus);

		self::getTwig()
			->display('/profile/ProfilePage.twig',array(
				'profile'	 => self::$profile,
				'wallMessages'	 => $wallMessages,
				'friends'	 => $friends,
				'realFriendsCount' => count($dataMap->getFriends($currentUser, ['myFriends' => false])),
				'currentUserId' => $currentUser->getId()
			))
		;
	}

	public static function statusesPage(){

		$currentUser = UserFactory::getCurrentUser();

		$dataMap = new UserDataMap();
		$statuses = $dataMap->getStatuses(self::$profile);

		self::getTwig()
			->display('/profile/ProfileStatuses.twig',array(
				'profile'	 => self::$profile,
				'statusList' => $statuses,
				'realFriendsCount' => count($dataMap->getFriends($currentUser, ['myFriends' => false]))
			))
		;
	}

	public static function profileEditForm($profileId){
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePage.twig',array(
				'profile' => $profile
			))
		;
	}


	public static function profileBlackList($profileId){
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePage.twig',array(
				'profile' => $profile
			))
		;
	}

	public static function friendsPage($profileId, $listPage = 1) {

		$currentUser = UserFactory::getCurrentUser();

		$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();

		$onPage = 20;
		$paginator = [];

		$friends = $dataMap->getFriends($profile,
			['myFriends' => (UserFactory::getCurrentUser()->getId() == $profileId) ? true : false],
			[($listPage-1)*$onPage,$onPage],
			$paginator
		);

		#print '<pre>'.print_r($friends[0]->getAvatar()->getUrl(),true).'</pre>';

		$pages = ceil($paginator['overall']/$onPage);


		self::getTwig()
			->display('/profile/ProfileFriends.twig',[
				'currentUser' => $currentUser,
				'profile' => $profile,
				'friends' => $friends,
				'paginator' => [
					'overall' => $pages,
					'active'  => $listPage
				],
				'realFriendsCount' => count($dataMap->getFriends($currentUser, ['myFriends' => false]))
			])
		;
	}

	public static function fansPage($profileId){
		$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();
		$persons = $dataMap->getFans($profile);

		self::getTwig()
			->display('/profile/ProfilePersons.twig',[
				'profile' => $profile,
				'persons' => $persons
			])
		;

	}

	public static function fansForm($profileId){
		$profile = UserFactory::getUser($profileId);

		$dataMap = new UserDataMap();
		$fans = $dataMap->getFans($profile);

		$fansArray = [];

		foreach ($fans as $fan){
			$fansArray[] = $fan->getId();
		}

		$allPersons = PersonFactory::getPersons([],0,-1,['name'=>'asc']);

		self::getTwig()
			->display('/profile/ProfilePersonsAdd.twig',[
				'profile' => $profile,
				'persons' => $allPersons,
				'fans'    => $fansArray
			])
		;
	}

	public static function fansSave($profileId){

		if (UserFactory::getUser($profileId)->getId() != UserFactory::getCurrentUser()->getId()) return false;

		$dataMap = new UserDataMap();
		$dataMap->addToFans(UserFactory::getCurrentUser(),self::getSlim()->request()->post('persons'));

		self::getSlim()->redirect(sprintf('/profile/%u/persons',$profileId));
	}

	public static function fansAddDispatcher($profileId){

		switch(self::getSlim()->request()->getMethod()){
			case 'GET':
				self::fansForm($profileId);
				break;
			case 'POST':
				self::fansSave($profileId);
				break;
		}
	}


	public static function personsRemovePage($profileId){
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePersonsAdd.twig',[
				'profile' => $profile,
			])
		;

	}

	public static function personsNewsPage($profileId){
		$profile = UserFactory::getUser($profileId);

		self::getTwig()
			->display('/profile/ProfilePersonsAdd.twig',[
				'profile' => $profile,
			])
		;

	}

	public static function imPage($companionId = null) {

		$profile = UserFactory::getCurrentUser();
		$dataMap = new MessageDataMap();

		if ($companionId > 0) {
			$messages = $dataMap->getDialogMessages($profile, $companionId);
			$companionProfile = UserFactory::getUser($companionId);

			self::getTwig()
				->display('/profile/ProfileMessages.twig',[
					'profile' => $profile,
					'messages' => $messages,
					'companionProfile' => $companionProfile,
					'companionId' => $companionId,
				]);
		} else {
			$dialogs = $dataMap->getDialogs($profile);

			if (!count($dialogs)) {
				self::getSlim()->redirect('/im/create');
			}
			self::getTwig()
				->display('/profile/ProfileMessages.twig',[
					'profile' => $profile,
					'dialogs' => $dialogs
				]);
		}

	}

	public static function imCreate() {

		$profile = UserFactory::getCurrentUser();
		$dataMap = new UserDataMap();

		$onPage = 20;
		$paginator = [];

		$friends = $dataMap->getFriends($profile,
			['myFriends' => true],
			[0, $onPage],
			$paginator
		);

		self::getTwig()
			->display('/profile/ProfileMessageCreate.twig',[
				'friends' => $friends,
			])
		;

	}

	public static function newMessageDispatcher($profileId){
		switch(self::getSlim()->request()->getMethod()){
			case 'GET':
				self::newMessageForm($profileId);
				break;
			case 'POST':
				self::newMessageSave($profileId);
				break;
		}
	}

	public static function newMessageForm(){

		self::getTwig()
			->display('/profile/ProfileNewMessage.twig',[
				'profile' => self::$profile,
			])
		;

	}

	public static function siteUsers(){



	}

	public function newWallMessageForm() {

		self::getTwig()
			->display('/profile/ProfileNewMessageWall.twig',[
				'profile' => UserFactory::getCurrentUser(),
				'recepientProfile' => self::$profile
		]);

	}

	public function newWallMessageSave() {

		$newMessageText = trim(self::getSlim()->request()->post('wallMessage'));
		$authorId = (int)self::getSlim()->request()->post('authorId');
		$recepientId = (int)self::getSlim()->request()->post('recepientId');

		if ($newMessageText) {
			$message = new MessageWall();

			$message->setSentTime(new \DateTime());
			$message->setAuthor(UserFactory::getUser($authorId));
			$message->setRecipient(UserFactory::getUser($recepientId));
			$message->setContent($newMessageText);
			$message->setRead(0);
			$message->setRemovedAuthor(0);
			$message->setRemovedRecipient(0);

			MessageWallFactory::save($message);
		}

		self::getSlim()->redirect(sprintf('/profile/%u', self::$profile->getId()));

	}

	public function newStatusMessageForm() {

		self::getTwig()
			->display('/profile/ProfileNewStatus.twig',[
				'profile' => UserFactory::getCurrentUser()
		]);

	}

	public function newStatusMessageSave() {

		$profile = UserFactory::getCurrentUser();
		$statusMessage = trim(self::getSlim()->request()->post('statusMessage'));

		if ($statusMessage) {
			$dataMap = new UserDataMap();
			$statuses = $dataMap->statusUpdate($profile, $statusMessage);
		}

		self::getSlim()->redirect(sprintf('/profile/%u/statuses', self::$profile->getId()));

	}


}