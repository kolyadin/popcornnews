<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\PDOHelper;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\poll\PollDataMap;

class AjaxController extends GenericController implements ControllerInterface {
	public function getRoutes() {
		$this
			->getSlim()
			->post('/ajax/poll/remove', [$this, 'pollRemove']);

		$this
			->getSlim()
			->get('/ajax/post/tags', [$this, 'postTags']);

		$this
			->getSlim()
			->get('/ajax/post/persons', [$this, 'postPersons']);


	}

	public function pollRemove() {
		$pollId = $this->getSlim()->request->post('pollId');

		$dataMap = new PollDataMap();
		$dataMap->delete($pollId);

		//Удаляем привязанные варианты ответов
		$stmt = PDOHelper::getPDO()->prepare('DELETE FROM pn_poll_opinions WHERE pollId = :pollId');
		$stmt->execute([
			'pollId' => $pollId
		]);


		$this->getApp()->exitWithJsonSuccessMessage();
	}

	public function postPersons() {
		$term = $this->getSlim()->request->get('term');

		$stmt = PDOHelper::getPDO()->prepare('SELECT id,name FROM pn_persons WHERE name LIKE :query OR englishName LIKE :query ORDER BY name ASC LIMIT 30');
		$stmt->execute([
			':query' => '%' . $term . '%'
		]);

		$persons = [];

		while ($table = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$persons[] = [
				'id' => $table['id'],
				'text' => $table['name']
			];
		}

		$this->getApp()->exitWithJsonSuccessMessage([
			'persons' => $persons
		]);
	}

	public function postTags() {
		$term = $this->getSlim()->request->get('term');

		$stmt = PDOHelper::getPDO()->prepare('SELECT id,name FROM pn_tags WHERE name LIKE :query AND type = 0 ORDER BY name ASC LIMIT 30');
		$stmt->execute([
			':query' => '%' . $term . '%'
		]);

		$tags = [];

		while ($table = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$tags[] = [
				'id' => $table['id'],
				'text' => $table['name']
			];
		}

		$this->getApp()->exitWithJsonSuccessMessage([
			'tags' => $tags
		]);
	}
}