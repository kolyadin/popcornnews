<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerAjaxInterface;
use popcorn\app\controllers\ControllerInterface;
use popcorn\lib\RuHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\UpDownDataMap;
use popcorn\model\exceptions\AjaxException;
use popcorn\model\exceptions\CommonErrorException;
use popcorn\model\persons\fanfics\FanFic;
use popcorn\model\persons\fanfics\FanFicFactory;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\UserFactory;
use popcorn\model\voting\UpDownVoting;

/**
 * Class PersonFanFicsController
 * @package popcorn\app\controllers
 */
class PersonFanFicsController extends PersonController implements ControllerInterface, ControllerAjaxInterface {

	const FANFICS_PER_PAGE = 20;

	public function getAjaxRoutes() {
		$this
			->getSlim()
			->post('/ajax/fanfic/vote', [$this, 'fanficAjaxVote']);
	}

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
								->redirect(sprintf('/persons/%s/fanfics', $urlName), 301);
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

				$this
					->getSlim()
					->get('/:fanficId', function ($urlName, $fanficId) {
						$this->fanfic($fanficId);
					})
					->conditions([
						'fanficId' => '[1-9][0-9]*'
					]);

			});
	}

	public function fanficAjaxVote() {
		$fanficId = $this->getSlim()->request->post('entityId');
		$vote = $this->getSlim()->request->post('vote');

		$fanfic = FanFicFactory::getFanFic($fanficId);

		$upDownDataMap = new UpDownDataMap();

		try {
			if ($upDownDataMap->isAllow($fanfic)) {

				$voting = new UpDownVoting();
				$voting->setVotedAt(new \DateTime());
				$voting->setEntity(get_class(new FanFic()));
				$voting->setEntityId($fanfic->getId());

				if ($vote == 'vote-up') {
					$voting->setVote(UpDownVoting::Up);
					$fanfic->setVotesUp($fanfic->getVotesUp() + 1);
				} elseif ($vote == 'vote-down') {
					$voting->setVote(UpDownVoting::Down);
					$fanfic->setVotesDown($fanfic->getVotesDown() + 1);
				}

				$upDownDataMap->save($voting);

				FanFicFactory::saveFanFic($fanfic);

				$pointsOverall = sprintf('Всего %s', RuHelper::ruNumber($fanfic->getVotesOverall(), ['нет голосов', '%u голос', '%u голоса', '%u голосов']));

				$this->getApp()->exitWithJsonSuccessMessage([
					'entityId'      => $fanfic->getId(),
					'points'        => $fanfic->getVotes(),
					'pointsOverall' => $pointsOverall
				]);

			}
		} catch (AjaxException $e) {
			$e->exitWithJsonException();
		}
	}

	public function fanfic($fanficId) {

		$person = PersonFactory::getPerson(self::$personId);
		$fanfic = FanFicFactory::getFanFic($fanficId);

		if (!($fanfic instanceof FanFic)) {
			$this->getSlim()->notFound();
		}

		$comments = FanFicFactory::getComments($fanfic);

		$this
			->getTwig()
			->display('/person/fanfics/PersonFanFic.twig', [
				'person'   => $person,
				'fanfic'   => $fanfic,
				'comments' => $comments
			]);

	}

	public function fanfics($page) {

		$person = PersonFactory::getPerson(self::$personId);

		if (is_null($page)) {
			$page = 1;
		}

		$fanfics = FanFicFactory::getFanFicsByPerson($person, [], ($page - 1) * self::FANFICS_PER_PAGE, self::FANFICS_PER_PAGE, $totalFound);

//		if (!count($fanfics)) {
//			$this->getSlim()->notFound();
//		}

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

	public function fanficPostPost() {

		$personId = (int)$this->getSlim()->request->post('personId');

		$title = $this->getSlim()->request->post('title');
		$announce = $this->getSlim()->request->post('announce');
		$content = $this->getSlim()->request->post('content');

		$person = PersonFactory::getPerson($personId);

		if (!($person instanceof Person)) {
			$this->getSlim()->error(new CommonErrorException());
		}

		$photo = 0;

		$fanfic = new FanFic();
		$fanfic->setCreatedAt(new \DateTime('now'));
		$fanfic->setUserId(UserFactory::getCurrentUser()->getId());
		$fanfic->setPersonId($person->getId());
		$fanfic->setStatus(FanFic::STATUS_ACTIVE);

		if (isset($_FILES['photo']) && isset($_FILES['photo']['tmp_name'])) {
			$photo = ImageFactory::createFromUpload($_FILES['photo']['tmp_name']);
		}

		$fanfic->setPhoto($photo);
		$fanfic->setTitle($title);
		$fanfic->setAnnounce($announce);
		$fanfic->setContent($content);

		FanFicFactory::saveFanFic($fanfic);

		if ($fanfic->getId() > 0) {
			$this
				->getSlim()
				->flash('fanFicAdded', true);

			$this
				->getSlim()
				->redirect(sprintf('/persons/%s/fanfics/%u', $person->getUrlName(), $fanfic->getId()));
		} else {
			$this
				->getSlim()
				->error(new CommonErrorException());
		}


	}


}