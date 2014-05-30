<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\lib\Middleware;
use popcorn\model\persons\PersonFactory;

/**
 * Class PersonFactsController
 * @package popcorn\app\controllers
 */
class PersonFactsController extends PersonController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->group('/persons/:name/facts', [$this, 'personExistsMiddleware'], function () {
				$this
					->getSlim()
					->get('(/page:page)', function ($urlName, $page = null) {
						if ($page == 1) {
							$this
								->getSlim()
								->redirect(sprintf('/persons/%s/facts', $urlName), 301);
						}

						$this->facts($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);


				$this
					->getSlim()
					->get('/post', 'popcorn\\lib\\Middleware::authorizationNeeded', function () {
						switch ($this->getSlim()->request->getMethod()) {
							case 'POST':
								$this->factPostPost();
								break;
							default:
								$this->factPostGet();
								break;

						}
					})
					->via('GET', 'POST');

			});

	}

	public function factPostGet() {
		$person = PersonFactory::getPerson(self::$personId);

		$this
			->getTwig()
			->display('/person/facts/PersonPostFact.twig', [
				'person' => $person
			]);
	}

	public function factPostPost() {

	}

	public function facts($page) {
		if ($page === null) {
			$page = 1;
		}

		$person = PersonFactory::getPerson(self::$personId);

		$this
			->getTwig()
			->display('/person/facts/PersonFacts.twig', [
				'person' => $person
			]);

	}

}