<?php
namespace popcorn\app\controllers\office;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\lib\ImageGenerator;
use popcorn\lib\PDOHelper;
use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\persons\KidFactory;
use popcorn\model\persons\PersonFactory;
use popcorn\model\poll\PollDataMap;
use popcorn\model\posts\PostFactory;

class AjaxController extends GenericController implements ControllerInterface {
	public function getRoutes() {

		$this
			->getSlim()
			->post('/ajax/:entity/remove', [$this, 'removeEntity']);

		$this
			->getSlim()
			->get('/ajax/find/movies', [$this, 'findMovies']);

		$this
			->getSlim()
			->get('/ajax/post/tags', [$this, 'postTags']);

		$this
			->getSlim()
			->get('/ajax/post/persons', [$this, 'postPersons']);

		$this
			->getSlim()
			->get('/ajax/get/person', [$this, 'getPerson']);

		$this
			->getSlim()
			->post('/ajax/upload-attach', [$this, 'uploadAttach']);

		$this
			->getSlim()
			->post('/ajax/crop', [$this, 'crop']);


	}

	public function removeEntity($entity) {

		$entityId = $this->getSlim()->request->post('entityId');

		switch ($entity){
			case 'post':
				PostFactory::removePost($entityId);
				break;
			case 'person':
				PersonFactory::removePerson($entityId);
				break;
			case 'kid':
				KidFactory::removeKid($entityId);
				break;
			case 'poll':
				$dataMap = new PollDataMap();
				$dataMap->delete($entityId);
				break;
		}

		$this->getApp()->exitWithJsonSuccessMessage();

	}

	public function findMovies() {
		$term = $this->getSlim()->request->get('term');

		$stmt = PDOHelper::getPDO()->prepare('SELECT id,name,year FROM ka_movies WHERE (name LIKE :query OR originalName LIKE :query) ORDER BY name ASC,year DESC LIMIT 50');
		$stmt->execute([
			':query' => '%' . $term . '%'
		]);

		$movies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$this->getApp()->exitWithJsonSuccessMessage([
			'movies' => $movies
		]);
	}

	public function getPerson() {
		$personId = $this->getSlim()->request->get('personId');

		$person = PersonFactory::getPerson($personId);

		$this->getApp()->exitWithJsonSuccessMessage([
			'id' => $person->getId(),
			'name' => $person->getName(),
			'photo' => $person->getPhoto()->getThumb('x100')->getUrl()
		]);
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

	public function uploadAttach() {

		// Make sure file is not cached (as it happens for example on iOS devices)
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Settings
		$targetDir = '/tmp/plupload';

		//$targetDir = 'uploads';
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part") {
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}


		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		if (!$chunks || $chunk == $chunks - 1) {
			rename("{$filePath}.part", $filePath);
		}

		$img = ImageFactory::createFromUpload($filePath);
		$img->setSource($filePath);

		$resizeValue = 'x30';

		if (isset($_POST['resize'])) {
			$resizeValue = $_POST['resize'];
		}

		$thumb = $img->getThumb($resizeValue);

		die(json_encode([
			'jsonrpc' => '2.0',
			'thumb' => [
				'url' => $thumb->getUrl(),
				'width' => $thumb->getWidth(),
				'height' => $thumb->getHeight()
			],
			'url' => $img->getUrl(),
			'width' => $img->getWidth(),
			'height' => $img->getHeight(),
			'id' => $img->getId()
		]));

	}

	public function crop() {

		$imageId = $this->getSlim()->request->post('imageId');
		$coords = $this->getSlim()->request->post('coords');

		$image = ImageFactory::getImage($imageId);

		list($x1, $y1, $x2, $y2, $w, $h) = sscanf($coords, '%u,%u,%u,%u,%u,%u');

		$gen = new ImageGenerator();
		$gen->setImage($image);

		$thumb = $gen->convert($image->getPath(), [
			'crop' => sprintf('%ux%u+%u+%u',
				$w, $h, $x1, $y1
			)
		]);

		if ($thumb->getId()) {

			$newImage = ImageFactory::createFromUpload(__DIR__ . '/../../../htdocs' . $thumb->getRelPath());

			$this->getApp()->exitWithJsonSuccessMessage([
				'id' => $newImage->getId(),
				'url' => $newImage->getUrl(),
				'width' => $newImage->getWidth(),
				'height' => $newImage->getHeight()
			]);

		}

	}
}