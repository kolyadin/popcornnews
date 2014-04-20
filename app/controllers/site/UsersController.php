<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\mmc\MMC;
use popcorn\lib\SphinxClient;
use popcorn\lib\SphinxHelper;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\system\users\UserFactory;

/**
 * Class UsersController
 * @package popcorn\app\controllers
 */
class UsersController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->map('/users', [$this, 'usersPage'])
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/users/city:cityId', [$this, 'usersByCity'])
			->conditions(['cityId' => '\d+']);

		$this
			->getSlim()
			->map('/users/search(/:searchString)', [$this, 'usersSearch'])
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/users/top(/:searchString)', [$this, 'usersTopPage'])
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/users/online', [$this, 'usersOnlinePage'])
			->via('GET', 'POST');

	}

	public function usersPage() {
		$dataMap = new UserDataMap();

		$top30Users = $dataMap->getTopUsers(0, 30);
		$newUsers = $dataMap->getNewUsers(0, 10);
		$countriesCities = $dataMap->getCountriesAndCities();
		$onlineUsersCount = $dataMap->getOnlineUsersCount();
		$onlineUsers = $dataMap->getOnlineUsers(0, 24, 'rand()');

		//@todo сделать активистов месяца


		$this
			->getTwig()
			->display('/users/UsersPage.twig', [
				'activist' => UserFactory::getUser(1),
				'top30Users' => $top30Users,
				'newUsers' => $newUsers,
				'onlineUsers' => $onlineUsers,

				'countriesCities' => $countriesCities,
				'onlineUsersCount' => $onlineUsersCount
			]);
	}

	public function usersByCity($cityId) {
		$dataMap = new UserDataMap();

		$countriesCities = $dataMap->getCountriesAndCities();

		$cityName = UserFactory::getCityNameById($cityId);

		$this
			->getTwig()
			->display('/users/UsersByCityPage.twig', [
				'cityId' => $cityId,
				'cityName' => $cityName,
				'countriesCities' => $countriesCities,

				'profiles' => $dataMap->getUsersByCity($cityId, 0, 50)
			]);
	}

	public function usersTopPage($searchString = '') {

		if ($this->getSlim()->request()->getMethod() == 'POST') {

			$string = $this->getSlim()->request()->post('searchString');

			$string = substr($string, 0, 50);
			$string = trim($string);

			if (!$string) {
				$this->getSlim()->redirect('/users/top');
			} else {
				$this->getSlim()->redirect(sprintf('/users/top/%s', $string));
			}
		}

		$dataMap = new UserDataMap();
		$tpl = [];

		if ($searchString) {
			$tpl['profiles'] = $dataMap->getTopUsersByNick($searchString);
			$tpl['customNum'] = 1;
			$tpl['safeSearchString'] = urldecode($searchString);
		} else {
			$tpl['profiles'] = $dataMap->getTopUsers(0, 60);
		}

		$this
			->getTwig()
			->display('/users/UsersTopPage.twig', $tpl);
	}

	public function usersSearch($searchString = '') {

		if ($this->getSlim()->request()->getMethod() == 'POST') {
			$string = $this->getSlim()->request()->post('nickName');

			$string = substr($string, 0, 50);
			$string = trim($string);

			if (!$string) {
				self::getSlim()->redirect('/users');
			} else {
				self::getSlim()->redirect(sprintf('/users/search/%s', urlencode($string)));
			}
		}

		$searchString = urldecode($searchString);

		$sphinx = SphinxHelper::getSphinx();

		$result = $sphinx
			->query('@nick %1$s', $searchString)
			->in('usersIndex')
			->sort(SPH_SORT_ATTR_ASC, 'nick_size')
			->fetch(['popcorn\model\system\users\UserFactory', 'getUser'])
			->run();

		$users = $result->matches;

		$tpl = [];

		$tpl['searchInput'] = $searchString;
		$tpl['profiles'] = $users;

		$this
			->getTwig()
			->display('/users/UsersSearch.twig', $tpl);
	}

	public function usersOnlinePage() {

		$dataMap = new UserDataMap();
		$onlineUsers = $dataMap->getOnlineUsers(0, 50);

		$this
			->getTwig()
			->display('/users/UsersOnlinePage.twig', [
				'onlineUsers' => $onlineUsers
			]);
	}
}