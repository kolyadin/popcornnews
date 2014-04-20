<?php

namespace popcorn\cli\command\kid;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	popcorn\lib\PDOHelper;

class RemoveAllComments extends Command{

	protected function configure(){

		$this
			->setName('kids:comments:removeAll')
			->addOption(
				'force',//Удалить не спрашивая подтверждения
				null,
				InputOption::VALUE_NONE
			)
			->setDescription('Удаление всех комментов у детей')
		;

	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$removeIt = function() use($output){
			$output->write('<info>Выполняю... </info>');

			PDOHelper::truncate(array(
				'pn_comments_kids',
				'pn_comments_kids_abuse',
				'pn_comments_kids_images',
				'pn_comments_kids_subscribe',
				'pn_comments_kids_vote'
			));

			$output->writeln('<bg=green;fg=white;options=bold>задача выполнена успешно</bg=green;fg=white;options=bold>');
		};

		if ($input->getOption('force')){

			$removeIt();

		} else{

			$defaultType = 1;
			$question = array(
				"<comment>1</comment>: Нет\n",
				"<comment>2</comment>: Да\n",
				"<question>Вы уверены, что хотите удалить все комменты у детей??</question> [<comment>$defaultType</comment>] "
			);

			$this
				->getHelper('dialog')
				->askAndValidate($output,$question,function($typeInput) use($removeIt,$output){
					switch($typeInput){
						case 1:
							$output->writeln('<bg=red;fg=white>Задача отменена</bg=red;fg=white>');
							break;
						case 2:
							$removeIt();
							break;
					}
				},10,$defaultType);

		}
	}
}