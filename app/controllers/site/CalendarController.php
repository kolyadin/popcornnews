<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\RuHelper;
use popcorn\model\calendar\Event;
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

				$this->calendar($month ?: date('m'), $year ?: date('Y'));

			})
			->conditions([
				'month' => '[0-9]{2}',
				'year'  => '[0-9]{4}'
			]);

		$this
			->getSlim()
			->get('/calendar/:eventId', [$this, 'event'])
			->conditions([
				'eventId' => '[1-9][0-9]*'
			]);
	}

	public function event($eventId) {
		$event = EventFactory::getEvent($eventId);

		if (!($event instanceof Event)) {
			$this
				->getSlim()
				->notFound();
		}

		$this
			->getTwig()
			->display('/CalendarEvent.twig', [
				'event' => $event
			]);
	}

	public function calendar($month, $year) {

		$currentDatetime = strtotime(sprintf('%04u-%02u-01 03:00:00', $year, $month));

		$prevMonth = strtotime('-1 month', $currentDatetime);
		$nextMonth = strtotime('+1 month', $currentDatetime);

		$ruMonth = RuHelper::$ruMonth[$month - 1][0];

		$events = EventFactory::getByMonth(new \DateTime(sprintf('%04u-%02u-05 03:00:00', $year, $month)));

		$this
			->getTwig()
			->display('/Calendar.twig', [
				'month'     => $month,
				'ruMonth'   => $ruMonth,
				'year'      => $year,
				'events'    => $events,
				'prevMonth' => $prevMonth,
				'nextMonth' => $nextMonth
			]);
	}
}