<?php

namespace popcorn\cli\command\user;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	popcorn\lib\PDOHelper;

class RemoveOne extends Command {

	protected function configure() {

		$this
			->setName('user:removeOne')
			->addOption(
				'userId',
				null,
				InputOption::VALUE_REQUIRED
			)
			->setDescription('Удаление пользователя по ID');

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$userId = $input->getOption('userId');

		$removeIt = function () use ($output,$userId) {
			$output->write('<info>Выполняю... </info>');

			$stmt = PDOHelper::getPDO()->prepare('delete from pn_users where id = ?');
			$stmt->bindValue(1,$userId,\PDO::PARAM_INT);
			$stmt->execute();

			$output->writeln('<bg=green;fg=white;options=bold>задача выполнена успешно</bg=green;fg=white;options=bold>');
		};


		$defaultType = 1;
		$question = array(
			"<comment>1</comment>: Нет\n",
			"<comment>2</comment>: Да\n",
			"<question>Вы уверены, что хотите удалить пользователя с id $userId</question> [<comment>$defaultType</comment>] "
		);

		$this
			->getHelper('dialog')
			->askAndValidate($output, $question, function ($typeInput) use ($removeIt, $output) {
				switch ($typeInput) {
					case 1:
						$output->writeln('<bg=red;fg=white>Задача отменена</bg=red;fg=white>');
						break;
					case 2:
						$removeIt();
						break;
				}
			}, 10, $defaultType);
	}
}