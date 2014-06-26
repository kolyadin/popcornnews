<?php

namespace popcorn\app\controllers\site\profile;

use popcorn\app\controllers\ControllerInterface;
use popcorn\model\dataMaps\UserDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\PostFactory;
use popcorn\model\system\users\UserFactory;


/**
 * Class ProfilePersonsController
 * @package popcorn\app\controllers
 */
class ProfilePersonsController extends ProfileController implements ControllerInterface {

	private $subscribedPersons = [];

	public function manageMiddleware() {

		if (!$this->subscribedPersons) {

			$this
				->getSlim()
				->redirect(sprintf('/profile/%u/persons/manage', self::$profileId));
		}
	}

	public function checkPersonsMiddleware() {
		$persons = UserFactory::getSubscribedPersons(self::$profile);

		$this->subscribedPersons = $persons;

		if ($persons) {
			$this
				->getTwig()
				->addGlobal('tab3ShowLinks', true);
		}
	}

	public function getRoutes() {

		$this
			->getSlim()
			->group('/profile/:profileId/persons', 'popcorn\\lib\\Middleware::authorizationNeeded', [$this, 'profileExistsMiddleware'], [$this, 'checkPersonsMiddleware'], function () {

				$this
					->getSlim()
					->get('', [$this, 'manageMiddleware'], [$this, 'persons']);

				$this
					->getSlim()
					->get('/news', [$this, 'manageMiddleware'], [$this, 'personsNews']);

				$this
					->getSlim()
					->map('/manage', [$this, 'onlyForMeMiddleware'], function () {

						if ($this->getSlim()->request->getMethod() == 'POST') {
							$this->managePost();
						} else {
							$this->manageGet();
						}

					})
					->via('GET', 'POST');

			});
	}

	public function persons() {

		$profile = UserFactory::getUser(self::$profileId, [
			'with' => UserDataMap::WITH_ALL
		]);

		$persons = UserFactory::getSubscribedPersons($profile);

		$this
			->getTwig()
			->display('/profile/persons/List.twig', [
				'profile' => $profile,
				'persons' => $persons
			]);

	}

	public function manageGet() {
		$profile = UserFactory::getUser(self::$profileId, [
			'with' => UserDataMap::WITH_ALL
		]);

		$persons = UserFactory::getSubscribedPersons($profile);

		$fans = [];

		foreach ($persons as $person) {
			$fans[] = $person->getId();
		}

		$allPersons = PersonFactory::getPersons(['orderBy' => ['name' => 'asc']], 0, -1);

		$this
			->getTwig()
			->display('/profile/persons/Manage.twig', [
				'profile' => $profile,
				'persons' => $allPersons,
				'fans'    => $fans
			]);
	}

	public function managePost() {

		$request = $this->getSlim()->request;

		$profile = UserFactory::getUser(self::$profileId);

		if (UserFactory::unsubscribeAllPersons($profile)) {

			if (count($request->post('persons'))) {
				foreach ($request->post('persons') as $personId) {
					PersonFactory::subscribeFan(PersonFactory::getPerson($personId), $profile);
				}
			}
		}

		$this
			->getSlim()
			->redirect(sprintf('/profile/%u/persons', self::$profileId));
	}

	public function personsNews() {
		$profile = UserFactory::getUser(self::$profileId);

		$posts = PostFactory::findByPersons($this->subscribedPersons, [], 0, 10);

		$this
			->getTwig()
			->display('/profile/persons/News.twig', [
				'profile' => $profile,
				'posts'   => $posts
			]);

	}

}