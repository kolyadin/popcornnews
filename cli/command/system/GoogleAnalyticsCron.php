<?php

namespace popcorn\cli\command\system;

use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use popcorn\model\dataMaps\GoogleStatDataMap;
use popcorn\model\system\GoogleStat;

class GoogleAnalyticsCron extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;


	protected function configure() {

		$this
			->setName('popcorn:gaCron')
			->setDescription("Google Analytics cron");

		$this->pdo = PDOHelper::getPDO();

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$client = new \Google_Client();

		$key = file_get_contents(__DIR__ . '/../../../lib/ga/ga-private-key');

		$client->setApplicationName('Popcorn google analytics');
		$client->setClientId('413200308444-he2o48s2tlm9jirkdscmtsn4v22cbvjn.apps.googleusercontent.com');
		$client->setAssertionCredentials(new \Google_AssertionCredentials(
			'413200308444-he2o48s2tlm9jirkdscmtsn4v22cbvjn@developer.gserviceaccount.com',
			array('https://www.googleapis.com/auth/analytics.readonly'),
			$key
		));

		$ga = new \Google_AnalyticsService($client);

		$datetime = new \Datetime(date('Y-m-d', strtotime('-1 year')));
		$nextdate = $datetime->getTimestamp();

		$dataMap = new GoogleStatDataMap();

		while (date('d.m.Y', $nextdate) !== date('d.m.Y')){

			$thisDate = date('Y-m-d', $nextdate);

			$result = $dataMap->findByDate($thisDate);
			if (!count($result)) {
				$data1 = $ga->data_ga->get('ga:43793910', $thisDate, $thisDate, 'ga:visits', array(
					'dimensions'  => 'ga:country',
					'sort'        => '-ga:visits',
					'max-results' => '20'
				));

				$data2 = $ga->data_ga->get('ga:43793910', $thisDate, $thisDate, 'ga:visits,ga:pageviews', array(
					'dimensions'  => 'ga:city,ga:country',
					'sort'        => '-ga:visits',
					'max-results' => '100'
				));

				$country = array();
				$city    = array();

				foreach ($data1['rows'] as $row){
					if ($row[0] == '(not set)') continue;
					$country[] = array($row[0] => $row[1]);
				}

				foreach ($data2['rows'] as $row){
					if ($row[0] == '(not set)') continue;
					$city[] = array($row[1] . ', ' . $row[0] => $row[2]);
				}

				$sex = array();
				$data3 = $ga->data_ga->get('ga:43793910',
					$thisDate,
					$thisDate,
					'ga:visits',
					array('dimensions' => 'ga:userGender')
				);
				foreach ($data3['rows'] as $row){
					if ($row[0] == '(not set)') continue;
					$sex[] = array($row[0] => $row[1]);
				}

				$age = array();
				$data4 = $ga->data_ga->get('ga:43793910',
					$thisDate,
					$thisDate,
					'ga:visits',
					array('dimensions' => 'ga:userAgeBracket')
				);
				foreach ($data4['rows'] as $row){
					if ($row[0] == '(not set)') continue;
					$age[] = array($row[0] => $row[1]);
				}

				$gaObject = new GoogleStat();
				$gaObject->setDate($thisDate);
				$gaObject->setPageViews($data2['totalsForAllResults']['ga:pageviews']);
				$gaObject->setVisits($data2['totalsForAllResults']['ga:visits']);
				$gaObject->setCountryJson(json_encode($country));
				$gaObject->setCityJson(json_encode($city));
				$gaObject->setSexJson(json_encode($sex));
				$gaObject->setAgeJson(json_encode($age));
				$dataMap->save($gaObject);
			}

			$nextdate = strtotime('+1 day', $nextdate);
		}

	}

}