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

		$this
			->getSlim()
			->get('/stat', [$this, 'getStat']);

	}


	public function getStat() {

		$month = array('', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');
		$monthSelect = array();

		$start = strtotime('-1 month', strtotime(date('Y-m-01 00:00:00')));
		for ($i = 5; $i >= 0; $i--) {
			$diffTime = strtotime("-$i month", $start);
			$monthSelect[] = date('m.Y', $diffTime);
		}

		//Не стоит выводить данные за совсем уж короткий текущий месяц
		if ((int)date('d') < 15) array_pop($monthSelect);

		$items = array();
		foreach ($monthSelect as $key => $m){
			list($monthNumeric, $year) = explode('.', $m);
			$item['val'] = $m;
			$item['m'] = $month[(int)$monthNumeric];
			$item['y'] = sprintf('%04u', $year);
			$items[] = $item;
		}

		$tmpl_vars = [
			'items' => $items,
		];

		$this->getTwig()->display('/static/StatPage.twig', $tmpl_vars);

	}

}