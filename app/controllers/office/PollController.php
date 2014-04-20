<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\poll\Opinion;
use popcorn\model\poll\OpinionDataMap;
use popcorn\model\poll\Poll;
use popcorn\model\poll\PollDataMap;

class PollController extends GenericController implements ControllerInterface {

	private $pollDataMap;

	public function getRoutes() {
		$this
			->getSlim()
			->map('/poll_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->pollEditGet();
						break;
					case 'POST':
						$this->pollEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->get('/polls(/page:pageId)', function ($page = null) {
				$this->polls($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/poll:pollId', function ($pollId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->pollEditGet($pollId);
						break;
					case 'POST':
						$this->pollEditPost();
						break;
				}
			})
			->conditions([
				':pollId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');
	}

	public function __construct() {
		$this->pollDataMap = new PollDataMap();
	}

	public function polls($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$polls = $this->pollDataMap->find();

		$this
			->getTwig()
			->display('poll/List.twig', [
				'polls' => $polls
			]);

	}

	public function pollEditGet($pollId = null) {

		$twigData = [];

		if ($pollId > 0) {
			$poll = $this->pollDataMap->findById($pollId);

			if (!$poll) {
				$this->getSlim()->notFound();
			}

			$twigData['poll'] = $poll;
		}

		if ($pollId > 0 && $this->getSlim()->request->get('action') == 'remove'){
			$this
				->getTwig()
				->display('poll/PollRemove.twig', $twigData);
		}else{
			$this
				->getTwig()
				->display('poll/PollForm.twig', $twigData);
		}
	}

	public function pollEditPost() {

		$request = $this->getSlim()->request;

		$pollId = $request->post('pollId');

		if ($pollId > 0) {
			$poll = $this->pollDataMap->findById($pollId);
		} else {
			$poll = new Poll();
			$poll->setCreatedAt(new \DateTime());
		}

		$poll->setQuestion($request->post('question'));

		if ($request->post('status')) {
			$poll->setStatus(Poll::STATUS_ACTIVE);
		} else {
			$poll->setStatus(Poll::STATUS_NOT_ACTIVE);
		}

		if (!$pollId){
			foreach ($request->post('opinion') as $title) {

				$opinion = new Opinion();
				$opinion->setTitle($title);

				$poll->addOpinion($opinion);
			}
		}

		$this->pollDataMap->save($poll);

		if ($poll->getId()) {

			if ($pollId){
				$this->getSlim()->redirect(sprintf('/office/poll%u?status=updated', $poll->getId()));
			}else{
				$this->getSlim()->redirect(sprintf('/office/poll%u?status=created', $poll->getId()));
			}
		}
	}
}