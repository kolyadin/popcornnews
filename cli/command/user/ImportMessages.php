<?php

namespace popcorn\cli\command\user;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	popcorn\lib\PDOHelper;

class ImportMessages extends Command{

	protected function configure(){

		$this
			->setName('import:messages')
			->setDescription('Импорт личных сообщений')
		;

	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$output->write('<info>Выполняю... </info>');


		$stmtFrom = PDOHelper::getPDO()->prepare('select * from popkorn_user_msgs where private = 1 order by id desc limit 100');
		$stmtTo   = PDOHelper::getPDO()->prepare('insert into pn_messages set sentTime = ?, authorId = ?, recipientId = ?, content = ?, `read` = ?, parentId = ?');

		$stmtFrom->execute();

		while ($row = $stmtFrom->fetch(\PDO::FETCH_OBJ)){
			$stmtTo->bindValue(1,$row->cdate,\PDO::PARAM_INT);
			$stmtTo->bindValue(2,1,\PDO::PARAM_INT);
			$stmtTo->bindValue(3,$row->aid,\PDO::PARAM_INT);
			$stmtTo->bindValue(4,$row->content,\PDO::PARAM_STR);
			$stmtTo->bindValue(5,$row->readed,\PDO::PARAM_INT);
			$stmtTo->bindValue(6,$row->pid,\PDO::PARAM_INT);

			$stmtTo->execute();
		}


		$output->writeln('<bg=green;fg=white;options=bold>задача выполнена успешно</bg=green;fg=white;options=bold>');

	}
}