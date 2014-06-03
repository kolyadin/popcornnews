<?php
namespace popcorn\app\controllers\office\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\persons\facts\FactFactory;
use popcorn\model\persons\PersonFactory;

class PersonFactController extends GenericController implements ControllerInterface {

	const FACTS_PER_PAGE = 13;

	public function getRoutes() {

		$this
			->getSlim()
			->get('/person:personId/facts(/page:pageId)', function ($personId, $page = null) {
				$this->getTwig()->addGlobal('tab3', true);
				$this->facts($personId, $page);
			})
			->conditions([
				':personId' => '[1-9][0-9]*',
				':pageId'   => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/person:personId/fact:factId/remove', function ($personId, $factId) {
				$this->getTwig()->addGlobal('tab3', true);

				switch ($this->getSlim()->request->getMethod()) {
					case 'POST':
						$this->factRemovePost();
						break;
					default:
						$this->factRemoveGet($personId, $factId);
						break;
				}
			})
			->conditions([
				':personId' => '[1-9][0-9]*',
				':pageId'   => '[1-9][0-9]*'
			])
			->via('GET', 'POST');

	}

	public function facts($personId, $page = null) {
		if ($page === null) {
			$page = 1;
		}

		$person = PersonFactory::getPerson($personId);

		$facts = FactFactory::getFacts($person, [], ($page - 1) * self::FACTS_PER_PAGE, self::FACTS_PER_PAGE, $totalFound);

		$this
			->getTwig()
			->display('persons/facts/Facts.twig', [
				'person'    => $person,
				'facts'     => $facts,
				'paginator' => [
					'pages'  => ceil($totalFound / self::FACTS_PER_PAGE),
					'active' => $page
				]
			]);
	}

	public function factRemoveGet($personId, $factId) {
		$person = PersonFactory::getPerson($personId);

		$fact = FactFactory::getFact($factId);

		$this
			->getTwig()
			->display('persons/facts/FactRemove.twig', [
				'person' => $person,
				'fact'   => $fact,
			]);
	}

	public function factRemovePost() {
		$factId = $this->getSlim()->request->post('factId');
		$personId = $this->getSlim()->request->post('personId');

		FactFactory::removeFact($factId);

		$this->getSlim()->flash('factRemoved', true);

		$this->getSlim()->redirect(sprintf('/office/person%u/facts', $personId));
	}
}