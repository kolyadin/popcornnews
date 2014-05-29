<?php

namespace popcorn\app;

use popcorn\app\commands\SiteCommand;
use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\app\controllers\site\AjaxController;
use popcorn\app\controllers\site\community\CommunityController;
use popcorn\app\controllers\site\KidsController;
use popcorn\app\controllers\site\MainPageController;
use popcorn\app\controllers\site\MeetController;
use popcorn\app\controllers\site\PhotoArticleController;
use popcorn\app\controllers\site\PostController;
use popcorn\app\controllers\site\ProfileController;
use popcorn\app\controllers\site\ProfileManagerController;
use popcorn\app\controllers\site\SearchController;
use popcorn\app\controllers\site\SidebarController;
use popcorn\app\controllers\site\StaticController;
use popcorn\app\controllers\site\UsersController;
use popcorn\app\controllers\site\YourStyleController;
use popcorn\app\controllers\SiteMain;
use popcorn\app\controllers\site\person\PersonController;
use popcorn\lib\GenericHelper;
use popcorn\model\exceptions\BadAuthorizationException;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\exceptions\RemindWrongEmailException;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\dataMaps\UserDataMap;
use Twig_SimpleFilter;
use popcorn\lib\Config;

class Popcorn extends Application {

	public function __construct() {

		parent::__construct([
			'mode'           => Config::getMode(),
			'templates.path' => __DIR__ . '/../templates/site'
		]);


		//Покажем мобильную версию
		{
			$mobileVersionOff = $this->getSlim()->getCookie('popcorn-mobile-version');

			$detectDevice = new \Mobile_Detect();

			if ($detectDevice->isMobile() && !$mobileVersionOff) {
				header('Location:' . Config::getInfo()['mobileVersionUrl']);
				die;
			}
		}

		$currentUser = $this->getUser();

		if (!($currentUser instanceof GuestUser)) {
			$currentUser->setLastVisit(time());
			UserFactory::save($currentUser);
		}

		GenericHelper::setApp($this);
		GenericController::setApp($this);

		{
			$this->getTwig()->addGlobal('showSidebar', true);
			$sideBarController = new SidebarController();
			$sideBarController->build();
		}


		$this->getTwig()->addGlobal('currentUser', $currentUser);
		$this->getTwig()->addGlobal('backLink', base64_encode($_SERVER['REQUEST_URI']));

		{
			$userDataMap = new UserDataMap();
			$onlineUsersCount = $userDataMap->getOnlineUsersCount();

			$this->getTwig()->addGlobal('onlineUsersCount', $onlineUsersCount);
		}

		$this->getTwig()->addFilter(new Twig_SimpleFilter('dateRU', function () {
			$timestamp = func_get_args()[0];

			$month = explode(' ', 'января февраля марта апреля мая июня июля августа сентября октября ноября декабря');

			if ((time() - $timestamp) < 60 * 60 * 24) {
				$out = 'сегодня, ';
			} elseif ((time() - $timestamp) > 60 * 60 * 24 && (time() - $timestamp) < 60 * 60 * 24 * 2) {
				$out = 'вчера, ';
			} else {
				$out = sprintf('%u %s %04u, ',
					date('d', $timestamp),
					$month[date('m', $timestamp) - 1],
					date('Y', $timestamp)
				);
			}

			$out .= date('H:i', $timestamp);

			return $out;
		}));

		$this->getSlim()->error(function (\Exception $e) {
			if ($e instanceof NotAuthorizedException) {
				$this->getSlim()->response()->status(401);
				$this->getTwig()->display('/errors/NotAuthorized.twig');
			} elseif ($e instanceof BadAuthorizationException) {
				$this->getSlim()->response()->status(401);
				$this->getTwig()->display('/errors/BadAuthorization.twig');
			} elseif ($e instanceof RemindWrongEmailException) {
				$this->getTwig()->display('/errors/RemindWrongEmail.twig', $e->getTpl());
			}
		});

		$this->initControllers();
	}

	private function needAuthorization($role = USER::USER) {
		return function () use ($role) {
			$user = UserFactory::getCurrentUser();

			if ($user->getType() != $role) {
				$this->getSlim()->error(new NotAuthorizedException());
			}
		};
	}

	public function exitWithJsonSuccessMessage(array $messages = []) {
		$output = ['status' => 'success'];

		if (count($messages)) {
			foreach ($messages as $key => $message) {
				$output[$key] = $message;
			}
		}

		die(json_encode($output));
	}

	protected function initControllers() {

		$this->registerController(new AjaxController());
		$this->registerController(new SearchController());
		$this->registerController(new MainPageController());
		$this->registerController(new ProfileManagerController());
		$this->registerController(new KidsController());
		$this->registerController(new UsersController());
		$this->registerController(new ProfileController());
		$this->registerController(new PersonController());
		$this->registerController(new PostController());
		$this->registerController(new PhotoArticleController());
		$this->registerController(new StaticController());
		$this->registerController(new CommunityController());
		$this->registerController(new MeetController());
		$this->registerController(new YourStyleController());


		$this->getSlim()->get('/version/desktop', function () {

			$this->getSlim()->setCookie('popcorn-mobile-version', 'off', strtotime('+1 year'), '/');
			$this->getSlim()->redirect('/');

		});

		//Обработчик для 404 ошибки
		$this->getSlim()->notFound(function () {
			$this->getTwig()->display('/errors/Error404.twig');
		});
	}

	protected function getUser() {
		if ($this->getSlim()->getEncryptedCookie(User::COOKIE_USER_NAME)) {
			$cookie = $this->getSecurityCookie();

			UserFactory::loginByHash($cookie->userId, $cookie->securityHash);
		}

		return UserFactory::getCurrentUser();
	}

	protected function getSecurityCookie() {
		$str = str_rot13(base64_decode($this->getSlim()->getEncryptedCookie(User::COOKIE_USER_NAME)));
		$str = explode('~', $str);

		$class = new \stdClass();
		$class->userId = $str[0];
		$class->securityHash = $str[1];

		return $class;
	}

	private function registerController(ControllerInterface $ctr) {
		$ctr->getRoutes();
	}

}