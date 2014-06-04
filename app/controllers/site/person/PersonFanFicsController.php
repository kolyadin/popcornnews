<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\model\persons\fanfics\FanFicFactory;
use popcorn\model\persons\PersonFactory;

/**
 * Class PersonFanFicsController
 * @package popcorn\app\controllers
 */
class PersonFanFicsController extends PersonController implements ControllerInterface {

	const FANFICS_PER_PAGE = 13;

	public function getRoutes() {

		$this
			->getSlim()
			->group('/persons/:name/fanfics', [$this, 'personExistsMiddleware'], function () {

				$this
					->getSlim()
					->get('(/page:page)', function ($urlName, $page = null) {

						if ($page == 1) {
							$this
								->getSlim()
								->redirect(sprintf('/persons/%s/facts', $urlName), 301);
						}


						$this->fanfics($page);
					})
					->conditions([
						'page' => '[1-9][0-9]*'
					]);

				$this
					->getSlim()
					->get('/post', 'popcorn\\lib\\Middleware::authorizationNeeded', function () {
						switch ($this->getSlim()->request->getMethod()) {
							case 'POST':
								$this->fanficPostPost();
								break;
							default:
								$this->fanficPostGet();
								break;
						}
					})
					->via('GET', 'POST');
			});
	}

	public function fanfics($page) {

		$person = PersonFactory::getPerson(self::$personId);

		if (is_null($page)) {
			$page = 1;
		}

		$fanfics = FanFicFactory::getFanFicsByPerson($person, [], ($page - 1) * self::FANFICS_PER_PAGE, self::FANFICS_PER_PAGE, $totalFound);

		if (!count($fanfics)) {
			$this->getSlim()->notFound();
		}

		$this
			->getTwig()
			->display('/person/fanfics/PersonFanFics.twig', [
				'person'    => $person,
				'fanfics'   => $fanfics,
				'paginator' => [
					'pages'  => ceil($totalFound / self::FANFICS_PER_PAGE),
					'active' => $page
				]
			]);
	}

	public function fanficPostGet() {

		$person = PersonFactory::getPerson(self::$personId);

		$this
			->getTwig()
			->display('/person/fanfics/PersonPostFanFic.twig', [
				'person' => $person
			]);
	}


}