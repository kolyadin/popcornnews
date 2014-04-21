<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;

/**
 * Class StaticController
 * @package popcorn\app\controllers\site
 */
class StaticController extends GenericController implements ControllerInterface {

	public function getRoutes() {

		$this
			->getSlim()
			->get('/faq', function () {
				$this->getTwig()->display('/static/FaqPage.twig');
			});

		$this
			->getSlim()
			->get('/rules', function () {
				$this->getTwig()->display('/static/RulesPage.twig');
			});

		$this
			->getSlim()
			->get('/contacts', function () {
				$this->getTwig()->display('/static/ContactsPage.twig');
			});

	}
}