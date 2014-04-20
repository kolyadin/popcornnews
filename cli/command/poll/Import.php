<?php

namespace popcorn\cli\command\poll;

use popcorn\lib\PDOHelper;
use popcorn\model\poll\Opinion;
use popcorn\model\poll\Poll;
use popcorn\model\poll\PollDataMap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo поставить на крон
 * Class Import
 * @package popcorn\cli\command\poll
 */
class Import extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	protected function configure() {
		$this->setName('poll:import')
			->setDescription("Импорт опросов со старого сайта");

		$this->pdo = PDOHelper::getPDO();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		{
			$output->write('<info>Очищаю все таблицы с опросами...</info>');

			PDOHelper::truncate([
				'pn_poll',
				'pn_poll_opinions',
				'pn_poll_voting'
			]);

			$output->writeln('<info> готово</info>');
		}

		{
			$output->write('<info>Импортирую опросы...</info>');

			$stmt = $this->pdo->query("SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 66");
			$stmt->execute();

			while ($table = $stmt->fetch(\PDO::FETCH_ASSOC)) {

				$this->importPoll($table);

			}

			$output->writeln('<info> готово</info>');
		}

		{
			$output->write('<info>Импортирую статистику опросов...</info>');

			$this->importPollVoting();

			$output->writeln('<info> готово</info>');
		}

		{
			$output->write('<info>Обновляю статистику вариантов ответов...');

			$this->updatePollVoting();

			$output->writeln('<info> готово</info>');
		}

		PDOHelper::getPDO()->query('UPDATE pn_poll SET status = 1 ORDER BY createdAt DESC LIMIT 1');

	}

	private function importPoll($table) {
		$dataMap = new PollDataMap();

		$poll = new Poll();
		$poll->setCreatedAt(new \DateTime($table['regtime']));
		$poll->setStatus(Poll::STATUS_NOT_ACTIVE);
		$poll->setQuestion($table['name']);

		for ($i = 1; $i <= 10; $i++) {
			if ($title = $table["pole$i"]) {
				$opinion = new Opinion();
				$opinion->setTitle($title);
				$opinion->setVotes(0);

				$poll->addOpinion($opinion);
			}
		}

		$dataMap->save($poll);

		if ($poll->getId()) {

			$stmt = $this->pdo->prepare('UPDATE pn_poll SET oldId = :oldId WHERE id = :id');
			$stmt->execute([
				':oldId' => $table['id'],
				':id' => $poll->getId()
			]);

		}
	}

	private function updatePollVoting() {

		$stmt1 = $this->pdo->query('SELECT id,pollId FROM pn_poll_opinions');
		$stmt1->execute();

		while ($table = $stmt1->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare('UPDATE pn_poll_opinions SET votes = (SELECT count(*) FROM pn_poll_voting WHERE pollId = :pollId AND opinionId = :opinionId) WHERE id = :opinionId LIMIT 1');
			$stmt2->execute([
				':pollId' => $table['pollId'],
				':opinionId' => $table['id']
			]);
		}
	}

	private function importPollVoting() {

		$stmt1 = $this->pdo->query('SELECT * FROM popcornnews.popcornnews_poll_statistics');
		$stmt2 = $this->pdo->prepare('SELECT opinion.id opinionId, poll.id pollId FROM pn_poll_opinions opinion JOIN pn_poll poll ON (poll.id = opinion.pollId) WHERE title = :title AND poll.oldId = :oldId LIMIT 1');
		$stmt3 = $this->pdo->prepare('INSERT INTO pn_poll_voting SET checksum = :checksum, votedAt = :votedAt, pollId = :pollId, opinionId = :opinionId');

		while ($tableStat = $stmt1->fetch(\PDO::FETCH_ASSOC)) {

			$stmt2->execute([
				':title' => $tableStat['anwser'],
				':oldId' => $tableStat['id']
			]);

			$id = $stmt2->fetch(\PDO::FETCH_ASSOC);

			if ($id) {
				$stmt3->execute([
					':checksum' => md5($tableStat['ip']),
					':votedAt' => strtotime($tableStat['regtime']),
					':pollId' => $id['pollId'],
					':opinionId' => $id['opinionId']
				]);
			}
		}
	}
}