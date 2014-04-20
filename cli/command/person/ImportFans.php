<?php

namespace popcorn\cli\command\person;

use popcorn\lib\PDOHelper;
use popcorn\model\tags\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportFans extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	protected function configure() {
		$this->setName('import:persons:fans')
			->setDescription("Импорт фанов персон");

		$this->pdo = PDOHelper::getPDO();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Импорт фанов...</info>');

		$stmt = $this->pdo->query('SELECT * FROM popcornnews.popkorn_fans');
		$stmt->execute();

		while ($fan = $stmt->fetch(\PDO::FETCH_ASSOC)){

			$stmt2 = $this->pdo->prepare('insert into pn_persons_fans set id = :id, personId = :personId, userId = :userId');
			$stmt2->bindValue(':id',$fan['id'],\PDO::PARAM_INT);
			$stmt2->bindValue(':personId',$fan['gid'],\PDO::PARAM_INT);
			$stmt2->bindValue(':userId',$fan['uid'],\PDO::PARAM_INT);

			$stmt2->execute();

		}

		$output->writeln('<info>Готово</info>');
	}
}