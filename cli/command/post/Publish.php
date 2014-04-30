<?php

namespace popcorn\cli\command\post;

use popcorn\model\posts\NewsPost;
use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	popcorn\lib\PDOHelper;

class Publish extends Command {

	protected function configure() {

		$this
			->setName('post:publish')
			->setDescription('Публикация запланированных постов');

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$pdo = PDOHelper::getPDO();
		$findStmt = $pdo->prepare('select id from pn_news where status = :statusPlanned and :nowTime >= createDate');
		$updateStmt = $pdo->prepare('update pn_news set status = :statusPublished where id = :postId limit 1');

		$output->writeln('Поиск запланированных новостей...');


		$findStmt->bindValue(':statusPlanned', NewsPost::STATUS_PLANNED, \PDO::PARAM_INT);
		$findStmt->bindValue(':nowTime', time(), \PDO::PARAM_INT);
		$findStmt->execute();

		$totalFound = $findStmt->rowCount();

		if (!$totalFound) {
			$output->writeln('Запланированных новостей не найдено');
			return;
		}

		$output->writeln(sprintf('Запланированных новостей: %u', $totalFound));
		$output->writeln('Публикуем новости... ');

		while ($table = $findStmt->fetch(\PDO::FETCH_ASSOC)) {

			$updateStmt->bindValue(':statusPublished',NewsPost::STATUS_PUBLISHED,\PDO::PARAM_INT);
			$updateStmt->bindValue(':postId',$table['id'],\PDO::PARAM_INT);
			$updateStmt->execute();

		}

		$output->write('готово');


	}
}