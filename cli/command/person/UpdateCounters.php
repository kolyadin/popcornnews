<?php

namespace popcorn\cli\command\person;

use popcorn\lib\PDOHelper;
use popcorn\model\tags\Tag;
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
		$this->setName('person:updateCounters')
			->setDescription("Пересчет счетчиков (комменты и т.д.)");

		$this->pdo = PDOHelper::getPDO();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Обновляем счетчики у персон...</info>');

		$stmt = $this->pdo->query('SELECT id,name FROM pn_persons ORDER BY name ASC');

		$output->writeln(sprintf('<info>Найдено персон: %u</info>', $stmt->rowCount()));

		$persons = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

		foreach ($persons as $personId => $personName) {
			$this->updateNewsCount($personId);
			$this->updatePhotosCount($personId);
			$this->updateVotesCount($personId);
		}

		$output->writeln('<info>Счетчики персон обновлены</info>');
	}

	/**
	 * Обновление счетчиков фотографий у конкретной персоны
	 *
	 * @param $personId
	 */
	private function updatePhotosCount($personId) {

		$subquery1 = 'SELECT count(*) FROM pn_persons_images WHERE personId = :personId';

		$stmt = $this->pdo->prepare("UPDATE pn_persons SET photosCount = ($subquery1) WHERE id = :personId");
		$stmt->execute([
			':personId' => $personId
		]);

	}

	/**
	 * Обновление счетчиков голосов у конкретной персоны
	 *
	 * @param $personId
	 */
	private function updateVotesCount($personId) {

		$subquery1 = 'SELECT count(*) FROM pn_persons_voting WHERE personId = :personId';
		$subquery2 = 'SELECT avg(rating) FROM pn_persons_voting WHERE personId = :personId AND category = "look" GROUP BY category';
		$subquery3 = 'SELECT avg(rating) FROM pn_persons_voting WHERE personId = :personId AND category = "style" GROUP BY category';
		$subquery4 = 'SELECT avg(rating) FROM pn_persons_voting WHERE personId = :personId AND category = "talent" GROUP BY category';

		$stmt = $this->pdo->prepare("UPDATE pn_persons SET votesCount = ($subquery1), look = ($subquery2), style = ($subquery3), talent = ($subquery4) WHERE id = :personId");
		$stmt->execute([
			':personId' => $personId
		]);


	}

	/**
	 * Обновление счетчиков новостей у конкретной персоны
	 *
	 * @param $personId
	 */
	private function updateNewsCount($personId) {

		$sql = "select count(*) from pn_news where id in (
			select newsId from pn_news_tags where type = :type and entityId = :personId
		)";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':type'     => Tag::PERSON,
			':personId' => $personId
		]);

		$newsCount = $stmt->fetchColumn();

		$stmt = $this->pdo->prepare('UPDATE pn_persons SET newsCount = :newsCount WHERE id = :personId');
		$stmt->execute([
			':newsCount' => $newsCount,
			':personId'  => $personId
		]);
	}
}