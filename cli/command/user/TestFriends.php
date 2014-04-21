<?php

namespace popcorn\cli\command\user;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	popcorn\lib\PDOHelper;

class TestFriends extends Command{

	protected function configure(){

		$this
			->setName('user:testFriends')
			->setDescription('Тестовое заполнение пользователями')
		;

	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$output->write('<info>Выполняю... </info>');

		$stmt = PDOHelper::getPDO()->prepare('insert into pn_users_friends set userId = 1, friendId = 1, confirmed = "y"');

		for ($i=0;$i<=10;$i++){
			$stmt->execute();
		}

		$output->writeln('<bg=green;fg=white;options=bold>задача выполнена успешно</bg=green;fg=white;options=bold>');

	}
}