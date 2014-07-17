<?php

namespace popcorn\app;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\app\controllers\office\AjaxController;
use popcorn\app\controllers\office\CalendarController;
use popcorn\app\controllers\office\KidController;
use popcorn\app\controllers\office\person\PersonFactController;
use popcorn\app\controllers\office\person\PersonFanficController;
use popcorn\app\controllers\office\PhotoArticleController;
use popcorn\app\controllers\office\PollController;
use popcorn\app\controllers\office\PostController;
use popcorn\app\controllers\office\MeetController;
use popcorn\app\controllers\office\person\PersonController;
use popcorn\lib\Config;

class OfficeApp extends Application {

	public function __construct() {

		parent::__construct([
			'mode'           => Config::getMode(),
			'templates.path' => __DIR__ . '/../templates/office'
		]);

		GenericController::setApp($this);

		$this->registerController(new AjaxController());


		$this->registerController(new CalendarController());

		$this->registerController(new PostController());
		$this->registerController(new PhotoArticleController());

		$this->registerController(new PersonController());
		$this->registerController(new PersonFactController());
		$this->registerController(new PersonFanficController());

		$this->registerController(new KidController());
		$this->registerController(new PollController());
		$this->registerController(new MeetController());

	}

	public function registerController(ControllerInterface $controller) {

		$controller->getRoutes();

	}

	public function exitWithJsonSuccessMessage(array $messages = []) {
		$output = ['status' => 'success'];

		if (count($messages)) {

			foreach ($messages as $key => $message) {
				$output[$key] = $message;
			}

		}

		die(json_encode($output));
	}


}