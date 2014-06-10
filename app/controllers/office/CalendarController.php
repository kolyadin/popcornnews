<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\calendar\Event;
use popcorn\model\calendar\EventFactory;
use popcorn\model\content\ImageFactory;
use popcorn\model\posts\photoArticle\PhotoArticleFactory;
use popcorn\model\posts\photoArticle\PhotoArticlePost;
use popcorn\model\posts\photoArticle\PhotoArticleTagDataMap;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\Movie;
use popcorn\model\posts\MovieFactory;
use popcorn\model\posts\photoArticle\PhotoArticleDataMap;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;

class CalendarController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/calendar/events(/page:pageId)', function ($page = null) {
				$this->events($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/calendar/event_add', function () {
				switch ($this->getSlim()->request->getMethod()) {
					default:
						$this->eventEditGet();
						break;
					case 'POST':
						$this->eventEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/calendar/event:eventId', function ($eventId) {
				switch ($this->getSlim()->request->getMethod()) {
					default:
						$this->eventEditGet($eventId);
						break;
					case 'POST':
						$this->eventEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/calendar/event:eventId/remove', function ($eventId) {
				switch ($this->getSlim()->request->getMethod()) {
					default:
						$this->eventRemoveGet($eventId);
						break;
					case 'POST':
						$this->eventRemovePost();
						break;
				}
			})
			->via('GET', 'POST');

	}

	public function eventEditGet($eventId = null) {

		$event = EventFactory::getEvent($eventId);

		if ($event instanceof Event) {
			$this->getTwig()->addGlobal('event', $event);
		} else {

		}

		$this
			->getTwig()
			->display('/calendar/EventForm.twig');
	}

	public function eventEditPost() {

		$data = [
			'title'     => $this->getSlim()->request->post('title'),
			'eventDate' => vsprintf('%3$04u-%2$02u-%1$02u %4$02u:%5$02u:00', sscanf($this->getSlim()->request->post('eventDate'), '%02u.%02u.%04u %02u:%02u')),
			'place'     => $this->getSlim()->request->post('place'),
			'content'   => $this->getSlim()->request->post('content'),
			'eventId'   => $this->getSlim()->request->post('eventId', null)
		];

		if ($data['eventId'] === null) {
			$event = new Event();
		} else {
			$event = EventFactory::getEvent($data['eventId']);
		}

		$event->setCreatedAt(new \DateTime('now'));
		$event->setEventDate(new \DateTime($data['eventDate']));
		$event->setTitle($data['title']);
		$event->setPlace($data['place']);
		$event->setContent($data['content']);

		EventFactory::saveEvent($event);

		if ($event->getId()) {
			$this->getSlim()->redirect(sprintf('/office/calendar/event%u', $event->getId()));
		}


	}

	public function events($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$onPage = 50;

		$events = EventFactory::getEvents([], ($page - 1) * $onPage, $onPage, $totalFound);

		$this
			->getTwig()
			->display('/calendar/Events.twig', [
				'events'    => $events,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);

	}

	public function eventRemoveGet($eventId) {

		$event = EventFactory::getEvent($eventId);

		if (!$event) {
			$this->getSlim()->notFound();
		}

		$this
			->getTwig()
			->display('/calendar/EventRemove.twig', [
				'event' => $event
			]);
	}

	public function eventRemovePost() {
		$eventId = $this->getSlim()->request->post('eventId');

		$event = EventFactory::getEvent($eventId);

		EventFactory::removeEvent($event->getId());

		$this->getSlim()->redirect('/office/calendar/events');
	}
}