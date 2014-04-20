<?php

namespace popcorn\cli\command\sql;

use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCounters
 * @package popcorn\cli\command\person
 */
class Import extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	protected function configure() {
		$this->setName('import:sql')
			->setDescription("Импорт sql с боевого попкорна");

		$this->pdo = PDOHelper::getPDO();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Импортирую sql</info>');

		exec('/usr/bin/ssh sky:dridliep@popcornnews.ru');

		$output->writeln('<info>Счетчики обновлены</info>');
	}

	/**
	 * @param int $topicId
	 */
	private function updateCommentsCount($topicId) {
		$subQuery = 'SELECT count(*) FROM pn_groups_topics_comments WHERE topicId = :topicId';

		$stmtUpdate = $this->pdo->prepare("UPDATE pn_groups_topics SET commentsCount = ($subQuery) WHERE id = :topicId LIMIT 1");
		$stmtUpdate->execute([':topicId' => $topicId]);

		$stmtUpdate->closeCursor();
	}

	/**
	 * @param int $topicId
	 */
	private function updateLastCommentTime($topicId) {
		$subQuery = 'SELECT date FROM pn_groups_topics_comments WHERE topicId = :topicId ORDER BY date DESC LIMIT 1';

		$stmtUpdate = $this->pdo->prepare("UPDATE pn_groups_topics SET lastCommentTime = ($subQuery) WHERE id = :topicId LIMIT 1");
		$stmtUpdate->execute([':topicId' => $topicId]);

		$stmtUpdate->closeCursor();
	}
}