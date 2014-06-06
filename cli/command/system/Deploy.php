<?php

namespace popcorn\cli\command\system;

use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Deploy extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;


	protected function configure() {

		$this
			->setName('popcorn:deploy')
			->setDescription("Развернуть проект");

		$this->pdo = PDOHelper::getPDO();

	}

	protected function execute(InputInterface $input, OutputInterface $output) {



	}

}