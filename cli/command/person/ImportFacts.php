<?php

namespace popcorn\cli\command\person;

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
	private $stmtFindFacts, $stmtFindFactsVotes, $stmtInsertFacts, $stmtInsertFactsVotes;

	protected function configure() {
		$this->setName('import:persons:facts')
			->setDescription("Импорт фактов о персонах");

		$this->pdo = PDOHelper::getPDO();

		$this->stmtFindFacts =
			$this->pdo->prepare('SELECT * FROM popcornnews.popcornnews_facts');

		$this->stmtFindFactsVotes =
			$this->pdo->prepare('SELECT * FROM popcornnews.popcornnews_fact_votes');

		$this->stmtInsertFacts =
			$this->pdo->prepare('insert into pn_persons_facts set id = :id, fact = :fact, personId = :personId,
			 createdAt = :createdAt, userId = :userId, trust = :trust,
			  trustVotes = :trustVotes, likes = :likes, likesVotes = :likesVotes');

		$this->stmtInsertFactsVotes =
			$this->pdo->prepare('insert into pn_persons_facts_votes set factId = :factId, userId = :userId, category = :category, vote = :vote');

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->write('<info>Чистим таблицы...');

		PDOHelper::truncate(['pn_persons_facts', 'pn_persons_facts_votes']);

		$output->writeln(' готово</info>');

		$output->write('<info>Импорт фактов...');

		$this->importFacts();

		$output->writeln(' готово</info>');

		$output->write('<info>Импорт голосований фактов...');

		$this->importFactsVotes();

		$output->writeln(' готово</info>');
	}

	private function importFacts() {
		$this->stmtFindFacts->execute();

		while ($faсt = $this->stmtFindFacts->fetch(\PDO::FETCH_ASSOC)) {
			$this->stmtInsertFacts->execute([
				':id'         => $faсt['id'],
				':fact'       => $faсt['content'],
				':personId'   => $faсt['person1'],
				':createdAt'  => $faсt['cdate'],
				':userId'     => $faсt['uid'],
				':trust'      => $faсt['trust'],
				':trustVotes' => $faсt['trust_votes'],
				':likes'      => $faсt['liked'],
				':likesVotes' => $faсt['liked_votes']
			]);
		}
	}

	private function importFactsVotes() {
		$this->stmtFindFactsVotes->execute();

		while ($vote = $this->stmtFindFactsVotes->fetch(\PDO::FETCH_ASSOC)) {
			$this->stmtInsertFactsVotes->execute([
				':factId'   => $vote['fid'],
				':userId'   => $vote['uid'],
				':category' => $vote['rubric'],
				':vote'     => $vote['vote'] / 10
			]);
		}
	}
}