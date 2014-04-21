<?php

namespace popcorn\cli\command\community;

use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo поставить на крон
 * Class UpdateCounters
 * @package popcorn\cli\command\person
 */
class UpdateCounters extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	protected function configure() {
		$this->setName('community:updateCounters')
			->setDescription("Пересчет счетчиков (комменты и т.д.)");

		$this->pdo = PDOHelper::getPDO();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Обновляем счетчики в сообществе...</info>');

		{
			$output->write('<info>Обновляю кол-во комментариев в обсуждениях...</info>');

			$stmt = $this->pdo->query('SELECT id FROM pn_groups_topics ORDER BY commentsCount DESC');

			while ($topicId = $stmt->fetch(\PDO::FETCH_COLUMN)) {

				$this->updateCommentsCount($topicId);
				$this->updateLastCommentTime($topicId);

			}

			$stmt->closeCursor();

			$output->writeln('<info> готово</info>');
		}


		$output->writeln('<info>Счетчики обновлены</info>');
	}

	/**
	 * @param int $topicId
	 */
	private function updateCommentsCount($topicId) {
		$subQuery = 'SELECT count(*) FROM pn_groups_topics_comments WHERE topicId = :topicId';

		$stmtUpdate = $this->pdo->prepare("UPDATE pn_groups_topics SET commentsCount = ($subQuery) WHERE id = :topicId");
		$stmtUpdate->execute([':topicId' => $topicId]);

		$stmtUpdate->closeCursor();
	}

	/**
	 * @param int $topicId
	 */
	private function updateLastCommentTime($topicId) {
		$subQuery = 'SELECT date FROM pn_groups_topics_comments WHERE topicId = :topicId ORDER BY date DESC LIMIT 1';

		$stmtUpdate = $this->pdo->prepare("UPDATE pn_groups_topics SET lastCommentTime = ($subQuery) WHERE id = :topicId");
		$stmtUpdate->execute([':topicId' => $topicId]);

		$stmtUpdate->closeCursor();
	}
}