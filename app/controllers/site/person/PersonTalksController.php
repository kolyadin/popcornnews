<?php
namespace popcorn\app\controllers\site\person;

use popcorn\app\controllers\ControllerInterface;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TalkDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\system\users\UserFactory;
use popcorn\model\talks\Talk;
use popcorn\model\talks\TalkFactory;
use popcorn\model\voting\VotingFactory;

/**
 * Class PersonTalksController
 * @package popcorn\app\controllers
 */
class PersonTalksController extends PersonController implements ControllerInterface {

	public function getRoutes() {

	}

	public function talksPage() {

		$person = $this->getPersonLight(self::$personId);

		$dataMap = new TalkDataMap();
		$talks = $dataMap->findByPerson($person);

		self::getTwig()
			->display('/person/talks/PersonTalksPage.twig', [
				'person' => $person,
				'talks' => $talks
			]);
	}

	public static function topicPage($person, $topicId) {

		$dataMap = new TalkDataMap();
		$topic = $dataMap->findById($topicId);

		self::getTwig()
			->display('/person/talks/PersonTalkTopic.twig', array(
				'topic' => $topic
			));

	}

	public function talksCreate() {
		switch ($this->getSlim()->request()->getMethod()) {
			case 'GET':
				$this->talksCreateGet();
				break;
			case 'POST':
				$this->performTopicCreate();
				break;
		}
	}

	private function talksCreateGet() {

		$person = $this->getPersonLight(self::$personId);

		$this
			->getTwig()
			->display('/person/talks/PersonTalksCreatePost.twig', [
				'person' => $person
			]);
	}

	private static function performTopicCreate() {
		$user = UserFactory::getCurrentUser();
		$person = PersonFactory::getPerson(self::getSlim()->request()->post('personId'));

		//@todo вывод ошибки если юзверь кривой

		$talk = new Talk();
		$talk->setOwner($user);
		$talk->setTitle(self::getSlim()->request()->post('name'));
		$talk->setContent(self::getSlim()->request()->post('content'));
		$talk->setCreateTime(new \DateTime());
		$talk->setRating(VotingFactory::createUpDownVoting());
		$talk->setPerson($person);

		TalkFactory::save($talk);

		self::getSlim()->redirect(sprintf('/persons/%s/talks', $person->getUrlName()));


	}
}