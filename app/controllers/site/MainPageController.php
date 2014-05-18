<?php

namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\persons\KidFactory;
use popcorn\model\posts\NewsPost;
use popcorn\model\posts\PostFactory;
use popcorn\model\posts\PostFactory2;

/**
 * Class PopcornController
 * @package popcorn\app\controllers\site
 */
class MainPageController extends GenericController implements ControllerInterface {

	private $twigData = [];

	public function getRoutes() {

		$this
			->getSlim()
			->get('/', [$this, 'mainPage']);

	}

	public function mainPage() {

		$this->buildPersons();
		$this->buildTags();
		$this->buildTopPosts();
		$this->buildLastPosts();
		$this->buildRandomKid();
		$this->buildStopShot();

		$this->twigData['showSidebar'] = false;

		$this->getTwig()->display('/MainPage.twig', $this->twigData);

	}

	private function buildStopShot() {
		$post = PostFactory::getStopShot();
		$this->twigData['stopShots'] = $post;
	}

	private function buildRandomKid() {
		$kid = KidFactory::getRandomKid();
		$this->twigData['randomKid'] = $kid;
	}

	private function buildLastPosts() {

		$posts = PostFactory::getPosts([
			'status' => NewsPost::STATUS_PUBLISHED,
			'itemCallback' => [
				'popcorn\\model\\dataMaps\\NewsPostDataMap' => NewsPostDataMap::WITH_NONE ^ NewsPostDataMap::WITH_MAIN_IMAGE
			]
		],0,17);

		$this->twigData['lastPosts'] = $posts;
	}

	private function buildTopPosts() {
		$topPosts = PostFactory::getTopPosts(10);
		$this->twigData['topPosts'] = $topPosts;
	}

	private function buildPersons() {
		$personDataMap = new PersonDataMap();
		$persons = $personDataMap->getTop();

		$personMax = $persons[0];

		usort($persons, function ($a, $b) {
			return strcmp($a['name'], $b['name']);
		});

		foreach ($persons as &$person) {
			$color = ceil(($person['newsCount'] * 7) / $personMax['newsCount']);
			$color = 7 - $color ? : 1;

			$person['color'] = $color;
		}

		$this->twigData['persons'] = $persons;
		$this->twigData['personsCount'] = $personDataMap->getAllPersonsCount();
	}

	private function buildTags() {
		$tagDataMap = new TagDataMap();
		$tags = $tagDataMap->getTop();

		usort($tags, function ($a, $b) {
			return strcmp($a['name'], $b['name']);
		});

		$tagMax = max($tags);

		$tagMin = min($tags);

		foreach ($tags as &$tag) {
			$color = ceil(($tag['overall'] * 7) / $tagMax['overall']);
			$color = 7 - $color ? : 1;

			$tag['color'] = $color;
		}

		$this->twigData['tags'] = $tags;
	}
}