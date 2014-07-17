<?php
namespace popcorn\app\controllers\office\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\persons\fanfics\FanFicFactory;
use popcorn\model\persons\PersonFactory;

class PersonFanficController extends GenericController implements ControllerInterface {

	const FANFICS_PER_PAGE = 13;

	public function getRoutes() {

		$this
			->getSlim()
			->get('/person:personId/fanfics(/page:pageId)', function ($personId, $page = null) {
				$this->getTwig()->addGlobal('tab4', true);
				$this->fanfics($personId, $page);
			})
			->conditions([
				':personId' => '[1-9][0-9]*',
				':pageId'   => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/person:personId/fanfic:fanficId/remove', function ($personId, $fanficId) {
				$this->getTwig()->addGlobal('tab4', true);

				switch ($this->getSlim()->request->getMethod()) {
					case 'POST':
						$this->fanficRemovePost();
						break;
					default:
						$this->fanficRemoveGet($personId, $fanficId);
						break;
				}
			})
			->conditions([
				':personId' => '[1-9][0-9]*',
				':pageId'   => '[1-9][0-9]*'
			])
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/person:personId/fanfic:fanficId', function ($personId, $fanficId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->fanficEditGet($personId, $fanficId);
						break;
					case 'POST':
						$this->fanficEditPost();
						break;
				}
			})
			->conditions([
				':meetId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');

	}

	public function fanfics($personId, $page = null) {

		if ($page === null) {
			$page = 1;
		}

		$person = PersonFactory::getPerson($personId);

		$fanfics = FanFicFactory::getFanFicsByPerson($person, [], ($page - 1) * self::FANFICS_PER_PAGE, self::FANFICS_PER_PAGE, $totalFound);

		$this
			->getTwig()
			->display('persons/fanfics/Fanfics.twig', [
				'person' => $person,
				'fanfics' => $fanfics,
				'paginator' => [
					'pages' => ceil($totalFound / self::FANFICS_PER_PAGE),
					'active' => $page
				]
			]
		);

	}

	public function fanficEditGet($personId, $fanficId) {

		$fanfic = FanFicFactory::getFanFic($fanficId);

		$this
			->getTwig()
			->display('persons/fanfics/FanficForm.twig', [
				'fanfic' => $fanfic,
				'personId' => $personId,
			]
		);

	}

	public function fanficEditPost() {

		$request = $this->getSlim()->request;

		$fanficId = $request->post('fanficId');
		$title = $request->post('title');
		$announce = $request->post('announce');
		$content = $request->post('content');
		$personId = $request->post('personId');

		$fanfic = FanFicFactory::getFanFic($fanficId);

		$fanfic->setTitle($title);
		$fanfic->setAnnounce($announce);
		$fanfic->setContent($content);

		FanFicFactory::saveFanFic($fanfic);

		$this->getSlim()->redirect(sprintf('/office/person%u/fanfic%u?status=updated', $personId, $fanficId));

	}

	public function fanficRemoveGet($personId, $fanficId) {

		$fanfic = FanFicFactory::getFanFic($fanficId);

		$this
			->getTwig()
			->display('persons/fanfics/FanficRemove.twig', [
				'personId' => $personId,
				'fanfic'   => $fanfic,
			]
		);

	}

	public function fanficRemovePost() {

		$fanficId = $this->getSlim()->request->post('fanficId');
		$personId = $this->getSlim()->request->post('personId');

		FanFicFactory::removeFanFic($fanficId);

		$this->getSlim()->flash('fanficRemoved', true);

		$this->getSlim()->redirect(sprintf('/office/person%u/fanfics', $personId));

	}

}