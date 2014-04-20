<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\poll\PollDataMap;

/**
 * Class SidebarController
 * @package popcorn\app\controllers\site
 */
class SidebarController extends GenericController implements ControllerInterface {

	private $twigData = [];

	public function getRoutes() {

	}

	public function build() {
		$this->buildTags();
		$this->buildPersons();
		$this->buildPoll();

		$this->getTwig()->addGlobal('sidebar', $this->twigData);
	}

	/**
	 * Персоны для правой колонки
	 */
	public function buildPersons() {
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

	/**
	 * Теги для правой колонки
	 */
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

	/**
	 * Выводим активный опрос
	 */
	private function buildPoll() {

		$dataMap = new PollDataMap();
		$poll = $dataMap->findActive();

		$maxVotes = 0;

		foreach ($poll->getOpinions() as $opinion) {
			if ($opinion->getVotes() > $maxVotes) {
				$maxVotes = $opinion->getVotes();
			}
		}

		$this->twigData['pollMaxVotes'] = $maxVotes;
		$this->twigData['poll'] = $poll;
	}
}