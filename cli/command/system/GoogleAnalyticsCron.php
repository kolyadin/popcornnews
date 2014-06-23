<?php

namespace popcorn\cli\command\system;

use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

		$output->writeln('ok');


	}

}