<?php

namespace popcorn\cli\command\person;

use popcorn\cli\helpers\OutputHelper;
use popcorn\lib\PDOHelper;
use popcorn\model\tags\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportFacts extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var \PDOStatement
	 */
	private $stmtFindFacts, $stmtFindFactsVotes, $stmtFindFactRating,
		$stmtInsertFacts, $stmtInsertFactsVotes, $stmtCleanFacts, $stmtCleanVotes;

	protected function configure() {

		$this->setName('import:persons:facts')
			->setDescription("Импорт фактов о персонах");

		$this->pdo = PDOHelper::getPDO();

		$this->stmtFindFacts =
			$this->pdo->prepare('SELECT * FROM popcornnews.popcornnews_facts');

		$this->stmtCleanFacts =
			$this->pdo->prepare('DELETE FROM popcornnews.popcornnews_facts WHERE uid NOT IN(SELECT id FROM popcorn.pn_users)');

		$this->stmtCleanVotes =
			$this->pdo->prepare('DELETE FROM popcornnews.popcornnews_fact_votes WHERE uid NOT IN(SELECT id FROM popcorn.pn_users)');

		$this->stmtFindFactRating =
			$this->pdo->prepare('SELECT FLOOR(SUM(vote)/COUNT(vote)) FROM pn_persons_facts_votes WHERE category = :categoryId and factId = :factId');

		$subquery1 = 'SELECT IFNULL(FLOOR(SUM(vote)/COUNT(vote)),0) FROM pn_persons_facts_votes WHERE category = 1 AND factId = :id';
		$subquery2 = 'SELECT IFNULL(FLOOR(SUM(vote)/COUNT(vote)),0) FROM pn_persons_facts_votes WHERE category = 2 AND factId = :id';

		$this->stmtInsertFacts =
			$this->pdo->prepare("INSERT INTO pn_persons_facts SET id = :id, fact = :fact, personId = :personId,
			 createdAt = :createdAt, userId = :userId, trustRating = ($subquery1), voteRating = ($subquery2)");

		$this->stmtInsertFactsVotes =
			$this->pdo->query('INSERT INTO popcorn.pn_persons_facts_votes (factId,userId,category,vote) SELECT fid, uid, rubric, vote/10 FROM popcornnews.popcornnews_fact_votes');

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		{
			$output->write('<info>Подготовим данные для импорта...');

			$this->stmtCleanFacts->execute();
			$this->stmtCleanVotes->execute();

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Чистим таблицы...');

			PDOHelper::truncate(['pn_persons_facts', 'pn_persons_facts_votes']);

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Импорт голосований фактов...');

			$this->stmtInsertFactsVotes->execute();

			$output->writeln(' готово</info>');
		}

		{
			$output->write('<info>Импорт фактов...');

			$this->importFacts();

			$output->writeln(' готово</info>');
		}
	}

	private function importFacts() {
		$this->stmtFindFacts->execute();

		while ($faсt = $this->stmtFindFacts->fetch(\PDO::FETCH_ASSOC)) {
			$this->stmtInsertFacts->execute([
				':id'        => $faсt['id'],
				':fact'      => $faсt['content'],
				':personId'  => $faсt['person1'],
				':createdAt' => $faсt['cdate'],
				':userId'    => $faсt['uid']
			]);
		}
	}
}