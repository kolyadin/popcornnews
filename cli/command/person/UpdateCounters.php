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

		$sql = <<<SQL
SELECT
	count(*)
FROM
         pn_news      news
	JOIN pn_news_tags newsTags ON (newsTags.newsId = news.id)
	JOIN pn_tags      tags     ON (tags.id = newsTags.tagId)
WHERE
	tags.type = :tagType AND tags.name = :person
SQL;

		$stmt = $this->pdo->query('SELECT id,name FROM pn_persons ORDER BY name ASC');
		$stmt->execute();

		$output->writeln(sprintf('<info>Найдено персон: %u</info>', $stmt->rowCount()));

		$persons = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

		foreach ($persons as $personId => $personName) {

			$stmt2 = $this->pdo->prepare($sql);
			$stmt2->execute([
				'tagType' => Tag::PERSON,
				'person' => $personId
			]);

			$newsCount = $stmt2->fetchColumn();

			$stmt2 = $this->pdo->prepare('UPDATE pn_persons SET newsCount = ? WHERE id = ?');
			$stmt2->bindValue(1, $newsCount, \PDO::PARAM_INT);
			$stmt2->bindValue(2, $personId, \PDO::PARAM_INT);
			$stmt2->execute();

			$output->writeln(sprintf('<info>Обновляем счетчик новостей для "%s", всего новостей: %u</info>', $personName, $newsCount));
		}

		$output->writeln('<info>Счетчики персон обновлены</info>');
	}
}