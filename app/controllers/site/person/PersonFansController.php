<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\model\dataMaps\PersonFanDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\UserFactory;

/**
 * Class PersonFansController
 * @package popcorn\app\controllers
 */
class PersonFansController extends PersonController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->group('/persons/:name/fans', [$this, 'personExistsMiddleware'], function () {

				$this
					->getSlim()
					->get('(/page:page)', function ($urlName, $page = null) {
						if ($page == 1) {
							$this->getSlim()->redirect(sprintf('/persons/%s/fans', $urlName));
						}

						$this->fansPage($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);

				$this
					->getSlim()
					->get('/new', [$this, 'fansNewPage']);

				$this
					->getSlim()
					->get('/local(/page:page)', 'popcorn\\lib\\Middleware::authorizationNeeded', function ($personUrl, $page = null) {
						if ($page == 1) {
							$this->getSlim()->redirect(sprintf('/persons/%s/fans/local', $personUrl));
						}

						$this->fansLocalPage($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);

				$this
					->getSlim()
					->get('/subscribe', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'fanSubscribePage']);
				$this
					->getSlim()
					->get('/unsubscribe', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'fanUnSubscribePage']);
			});
	}

	/**
	 * Все фаны персоны
	 */
	public function fansPage($page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$person = PersonFactory::getPerson(self::$personId);

		{
			$onPage = 50;
			$paginator = [($page - 1) * $onPage, $onPage];
		}

		$dataMap = new PersonFanDataMap();
		$users = $dataMap->findById(self::$personId, ['id' => 'asc'], $paginator);

		if ($page > $paginator['pages']) {
			$this->getSlim()->notFound();
		}

		{
			$i = 1;
			foreach ($users as &$user) {
				$user->setExtra('row', $i++ + $paginator[0]);
			}
			unset($i);
		}

		$this->getTwig()->display('/person/fans/PersonFans.twig', [
			'person'    => $person,
			'fans'      => $users,
			'customNum' => 1,
			'paginator' => [
				'pages'  => $paginator['pages'],
				'active' => $page
			]
		]);

	}

	/**
	 * Новыне фаны персоны
	 */
	public function fansNewPage() {

		$person = PersonFactory::getPerson(self::$personId);

		$dataMap = new PersonFanDataMap();

		$paginator = [0, 50];

		$users = $dataMap->findById(self::$personId, ['id' => 'desc'], $paginator);

		{

			$i = 1;
			foreach ($users as &$user) {
				$user->setExtra('row', $paginator['overall']--);
			}
			unset($i);
		}

		$this->getTwig()->display('/person/fans/PersonNewFans.twig', [
			'person'    => $person,
			'fans'      => $users,
			'customNum' => 1,
		]);

	}

	/**
	 * Фаны "в твоем городе"
	 */
	public function fansLocalPage($page = null) {

		if (is_null($page)) {
			$page = 1;
		}

		$person = PersonFactory::getPerson(self::$personId);

		{
			$onPage = 50;
			$paginator = [($page - 1) * $onPage, $onPage];
		}

		$cityId = UserFactory::getCurrentUser()->getUserInfo()->getCityId();

		$dataMap = new PersonFanDataMap();
		$users = $dataMap->find(self::$personId, ['info.cityId' => $cityId], ['id' => 'asc'], $paginator);

		if ($page > $paginator['pages']) {
			$this->getSlim()->notFound();
		}

		{
			$i = 1;
			foreach ($users as &$user) {
				$user->setExtra('row', $i++ + $paginator[0]);
			}
			unset($i);
		}

		$this->getTwig()->display('/person/fans/PersonLocalFans.twig', [
			'person'    => $person,
			'fans'      => $users,
			'customNum' => 1,
			'paginator' => [
				'pages'  => $paginator['pages'],
				'active' => $page
			]
		]);

	}

	/**
	 * Предлагаем стать поклонником
	 */
	public function fanSubscribePage() {

		$person = PersonFactory::getPerson(self::$personId);

		$dataMap = new PersonFanDataMap();

		if ($dataMap->isFan(UserFactory::getCurrentUser(), $person)) {
			$this->getSlim()->redirect(sprintf('/persons/%s/fans/unsubscribe', $person->getUrlName()));
		}

		$this->getTwig()->display('/person/fans/PersonFansSubscribe.twig', [
			'person' => $person
		]);

	}

	/**
	 * Предлагаем выйти из группы
	 */
	public function fanUnSubscribePage() {

		$person = PersonFactory::getPerson(self::$personId);

		$dataMap = new PersonFanDataMap();

		if (!$dataMap->isFan(UserFactory::getCurrentUser(), $person)) {
			$this->getSlim()->redirect(sprintf('/persons/%s/fans/subscribe', $person->getUrlName()));
		}

		$this->getTwig()->display('/person/fans/PersonFansUnSubscribe.twig', [
			'person' => $person
		]);

	}

}