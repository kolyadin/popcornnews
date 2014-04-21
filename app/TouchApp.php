<?php

namespace popcorn\app;

use popcorn\app\controllers\GenericController;
use popcorn\app\controllers\touch\v1_0\AjaxController;
use popcorn\app\controllers\touch\v1_0\NewsController;
use popcorn\app\controllers\touch\v1_0\ProfileController;
use popcorn\app\controllers\touch\v1_0\ProfileManagerController;
use popcorn\model\exceptions\BadAuthorizationException;
use popcorn\model\exceptions\NotAuthorizedException;
use popcorn\model\exceptions\RemindWrongEmailException;
use popcorn\model\system\users\GuestUser;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use Slim\Route;
use popcorn\lib\Config;

class TouchApp extends Application {

	/**
	 * @var string
	 */
	private $touchVersion;

	private static $versions = [
		'v1.0' => 'popcorn\\app\\controllers\\touch\\v1_0\\TouchController',
		'v1.1' => 'popcorn\\app\\controllers\\touch\\v1_1\\TouchController'
	];

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {

		$this->touchVersion = $params['touch_version'];

		parent::__construct([
			'mode' => Config::getMode(),
			'touch_version' => $params['touch_version'],
			'templates.path' => '../templates/touch/' . $this->touchVersion
		]);

		$currentUser = $this->getUser();

		GenericController::setApp($this);

		$this->getSlim()->error(function(\Exception $e){
			if ($e instanceof NotAuthorizedException){
				$this->getSlim()->response()->status(401);
				$this->getTwig()->display('/errors/NotAuthorized.twig');
			}elseif ($e instanceof BadAuthorizationException){
				$this->getSlim()->response()->status(401);
				$this->getTwig()->display('/errors/BadAuthorization.twig');
			}elseif ($e instanceof RemindWrongEmailException){
				$this->getTwig()->display('/errors/RemindWrongEmail.twig', $e->getTpl());
			}
		});

		$this->initRoutes();
	}


	protected function initRoutes() {
		//run_time_logger('a_routes', print_r($_SERVER, 1), print_r($_POST, 1));

		$this->setProfileRoutes();
		$this->setProfileManagerRoutes();
		$this->setAjaxRoutes();
		$this->setNewsRoutes();

		$this
			->getSlim()
			->get('/version/desktop',function(){

				$this->getSlim()->redirect('http://popcornnews.loc/version/desktop');

			});

		//Обработчик для 404 ошибки
		$this->getSlim()->notFound(function () {
			$this->getTwig()->display('/errors/Error404.twig');
		});
	}

	private function setProfileManagerRoutes() {

		$controller = new ProfileManagerController();
		$controller->getRoutes();

	}

	private function setProfileRoutes() {

		$controller = new ProfileController();
		$controller->getRoutes();

	}

	private function setAjaxRoutes() {

		$controller = new AjaxController();
		$controller->getRoutes();

	}

	private function setNewsRoutes() {

		$controller = new NewsController();
		$controller->getRoutes();

	}

	/**
	 * @param string $touchVersion
	 */
	public function setVersion($touchVersion) {
		$this->touchVersion = $touchVersion;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->touchVersion;
	}

	public function getUser(){
		if ($this->getSlim()->getEncryptedCookie(User::COOKIE_USER_NAME)){
			$cookie = $this->getSecurityCookie();

			UserFactory::loginByHash($cookie->userId, $cookie->securityHash);
		}

		return UserFactory::getCurrentUser();
	}

	protected function getSecurityCookie(){
		$str = str_rot13(base64_decode($this->getSlim()->getEncryptedCookie(User::COOKIE_USER_NAME)));
		$str = explode('~',$str);

		$class = new \stdClass();
		$class->userId = $str[0];
		$class->securityHash = $str[1];

		return $class;
	}


}