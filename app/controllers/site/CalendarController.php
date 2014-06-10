<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\calendar\EventFactory;

/**
 * Class CalendarController
 * @package popcorn\app\controllers
 */
class CalendarController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/calendar(/:month.:year)', function ($month = null, $year = null) {

				$this->calendar($month, $year);

			})
			->conditions([
				'month' => '[1-9][0-9]*',
				'year'  => '[0-9]{4}'
			]);
	}

	public function calendar($month, $year) {

		$events = EventFactory::getByMonth(new \DateTime('now'));

		$this
			->getTwig()
			->display('/Calendar.twig', [
				'month'  => $month,
				'year'   => $year,
				'events' => $events
			]);
	}
}