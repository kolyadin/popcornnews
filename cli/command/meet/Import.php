<?php

namespace popcorn\cli\command\meet;


use popcorn\lib\PDOHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	protected function configure() {

		$this
			->setName('import:meet')
			->setDescription("Импорт пар");

		$this->pdo = PDOHelper::getPDO();

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Чистим таблицы</info>');
		PDOHelper::truncate([
			'pn_meetings',
		]);
		$output->writeln('<comment> готово</comment>');

		$this->tablePnMeetings($input, $output);

	}

	protected function tablePnMeetings(InputInterface $input, OutputInterface $output, $limit = false) {

		$output->writeln(date('Y-m-d H:i', time()) . ' <info>Таблица pn_meetings</info>');
		if ($limit) {
			$limit = ' LIMIT ' . $limit;
		}
		$sql = 'SELECT * FROM `popcornnews`.`popconnews_goods_` WHERE `goods_id` = 15' . $limit;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$stmt2 = $this->pdo->prepare("
				INSERT INTO pn_meetings
				SET `id` = :id, `firstPerson` = :firstPerson, `secondPerson` = :secondPerson, `title` = :title,
					`description` = :description, `votesUp` = :votesUp, `votesDown` = :votesDown, `commentsCount` = :commentsCount,
					`date1` = :date1, `date2` = :date2"
			);
			$stmt2->execute([
				':id' => $item['id'],
				':firstPerson' => $item['pole4'],
				':secondPerson' => $item['pole8'],
				':title' => $item['name'],
				':description' => $item['pole1'],
				':votesUp' => $item['pole20'],
				':votesDown' => $item['pole21'],
				':commentsCount' => 0,
				':date1' => $item['pole7'],
				':date2' => $item['pole11'],
			]);
		}
		$output->writeln(date('Y-m-d H:i', time()) . ' <comment> готово</comment>');

	}


}