<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\lib\Middleware;
use popcorn\model\persons\facts\Fact;
use popcorn\model\persons\facts\FactFactory;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\UserFactory;

/**
 * Class PersonFactsController
 * @package popcorn\app\controllers
 */
class PersonFactsController extends PersonController implements ControllerInterface {

	const FACTS_PER_PAGE = 13;

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

						$this->getTwig()->addGlobal('tab1',true);
						$this->facts($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);

				//@todo Если о персоне нельзя добавлять факты выводить middleware exception
				$this
					->getSlim()
					->get('/post', 'popcorn\\lib\\Middleware::authorizationNeeded', function () {
						switch ($this->getSlim()->request->getMethod()) {
							case 'POST':
								$this->factPostPost();
								break;
							default:
								$this->getTwig()->addGlobal('tab2',true);
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
		$personId = (int)$this->getSlim()->request->post('personId');
		$factContent = $this->getSlim()->request->post('fact');

		$person = PersonFactory::getPerson($personId);

		if (!$person) {
			$this->getSlim()->notFound();
		}

		$fact = new Fact();
		$fact->setPerson($person);
		$fact->setUser(UserFactory::getCurrentUser());
		$fact->setCreatedAt(new \DateTime('now'));
		$fact->setFact($factContent);

		FactFactory::saveFact($fact);

		if ($fact->getId()) {
			$this->getSlim()->flash('factAdded', true);
			$this->getSlim()->redirect(sprintf('/persons/%s/facts', $person->getUrlName()));
		} else {
			$this->getSlim()->notFound();
		}

	}

	public function facts($page) {

		$person = PersonFactory::getPerson(self::$personId);

		if (is_null($page)) {
			$page = 1;
		}

		$facts = FactFactory::getFacts($person, [], ($page - 1) * self::FACTS_PER_PAGE, self::FACTS_PER_PAGE, $totalFound);

		if (!count($facts)) {
			$this->getSlim()->notFound();
		}

		$this
			->getTwig()
			->display('/person/facts/PersonFacts.twig', [
				'person'    => $person,
				'facts'     => $facts,
				'paginator' => [
					'pages'  => ceil($totalFound / self::FACTS_PER_PAGE),
					'active' => $page
				]
			]);
	}
}