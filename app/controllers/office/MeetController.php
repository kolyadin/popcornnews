<?php

namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\persons\MeetingFactory;
use popcorn\model\persons\Meeting;
use popcorn\model\persons\PersonFactory;

class MeetController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/meets(/page:pageId)', function ($page = null) {
				$this->meets($page);
			})
			->conditions([
				':pageId' => '[1-9][0-9]*'
			]);

		$this
			->getSlim()
			->map('/meet_create', function () {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->meetEditGet();
						break;
					case 'POST':
						$this->meetEditPost();
						break;
				}
			})
			->via('GET', 'POST');

		$this
			->getSlim()
			->map('/meet:meetId', function ($meetId) {
				switch ($this->getSlim()->request->getMethod()) {
					case 'GET':
						$this->meetEditGet($meetId);
						break;
					case 'POST':
						$this->meetEditPost();
						break;
				}
			})
			->conditions([
				':meetId' => '[1-9][0-9]*'
			])
			->via('GET', 'POST');

	}

	public function meets($page = null) {

		if ($page === null) {
			$page = 1;
		}

		$onPage = 50;
		$totalFound = 0;

		$meets = MeetingFactory::getMeets(['orderBy' => ['id' => 'asc']], ($page - 1) * $onPage, $onPage, $totalFound);
		//echo_arr($meets);

		$this
			->getTwig()
			->display('meets/MeetList.twig', [
				'meets'     => $meets,
				'paginator' => [
					'pages'  => ceil($totalFound / $onPage),
					'active' => $page
				]
			]);

	}

	public function meetEditGet($meetId = null) {

		$request = $this->getSlim()->request;

		$twigData = [];

		if ($meetId > 0) {
			$meet = MeetingFactory::get($meetId);

			if (!$meet) {
				$this->getSlim()->notFound();
			}

			$names = explode(' и ', $meet->getTitle());

			$twigData['meet'] = $meet;
			$twigData['name1'] = trim($names[0]);
			$twigData['name2'] = trim($names[1]);
		}

		if ($meetId > 0 && $request->get('action') == 'remove') {
			$this
				->getTwig()
				->display('news/PostRemove.twig', $twigData);
		} else {
			$this
				->getTwig()
				->display('meets/MeetForm.twig', $twigData);
		}
	}

	public function meetEditPost() {

		$request = $this->getSlim()->request;

		$meetId = $request->post('meetId');

		if ($meetId > 0) {
			$meet = MeetingFactory::get($meetId);
		} else {
			$meet = new Meeting();
		}

		$firstPerson = $request->post('firstPerson');
		if ($firstPerson) {
			$person1 = PersonFactory::getPerson($firstPerson);
			$meet->setFirstPerson($person1);
			$name1 = $person1->getName();
		} else {
			$meet->setFirstPerson('');
			$name1 = $request->post('firstPersonCustom');
		}

		$secondPerson = $request->post('firstPerson');
		if ($secondPerson) {
			$person2 = PersonFactory::getPerson($secondPerson);
			$meet->setSecondPerson($person2);
			$name2 = $person1->getName();
		} else {
			$meet->setSecondPerson('');
			$name2 = $request->post('secondPersonCustom');
		}

		$meet->setTitle($name1 . ' и ' . $name2);

		$meet->setDescription($request->post('description'));
		$meet->setVotesUp(0);
		$meet->setVotesDown(0);
		$meet->setCommentsCount(0);
		echo_arr($meet);
		MeetingFactory::save($meet);

		if ($meetId) {
			$this->getSlim()->redirect(sprintf('/office/meet%u?status=updated', $meet->getId()));
		} else {
			$this->getSlim()->redirect(sprintf('/office/meet%u?status=created', $meet->getId()));
		}

	}

}


