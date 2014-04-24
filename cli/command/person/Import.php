<?php
/**
 * User: anubis
 * Date: 22.11.13 16:11
 */

namespace popcorn\cli\command\person;


use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\Exception;
use popcorn\model\persons\Person;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;
	/**
	 * @var \PDOStatement
	 */
	private $selector;
	/**
	 * @var \PDOStatement
	 */
	private $insert;

	protected function configure() {
		$this
			->setName('import:persons')
			->addOption(
				'personId',
				null,
				InputOption::VALUE_REQUIRED,
				'Импорт одной конкретной персоны'
			)
			->setDescription("Импорт персон");

		$this->pdo = PDOHelper::getPDO();


		$this->insert = $this->pdo->prepare("
INSERT INTO pn_persons (
  id, name, englishName,
  genitiveName, prepositionalName,
  info, source, photo, birthDate,
  showInCloud, sex, isSinger,
  allowFacts,  vkPage, twitterLogin, pageName,
  nameForBio, published, urlName,
  look, style, talent
)
VALUES (
  :id, :name, :englishName,
  :genitiveName, :prepositionalName,
  :info, :source, :photo, :birthDate,
  :showInCloud, :sex, :isSinger,
  :allowFacts, :vkPage, :twitterLogin, :pageName,
  :nameForBio, :published, :urlName,
  :look, :style, :talent
)");

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Импорт персон...</info>');

		if ($input->getOption('personId')) {

			$this->pdo->exec(sprintf('DELETE FROM pn_persons WHERE id = %u', $input->getOption('personId')));
			$this->pdo->exec(sprintf('DELETE FROM pn_persons_images WHERE personId = %u', $input->getOption('personId')));

			$this->selector = $this->pdo->prepare("SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 3 AND id = :personId");
			$this->selector->bindValue(':personId', $input->getOption('personId'), \PDO::PARAM_INT);

		} else {
			$this->selector = $this->pdo->prepare("SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 3");
		}

		$this->selector->execute();

		$count = 0;
		$total = $this->selector->rowCount();

		for ($i = 0; $i < $total; $i++) {

			$item = $this->selector->fetch(\PDO::FETCH_ASSOC);

			$this->pdo->exec(sprintf('DELETE FROM pn_persons WHERE id = %u', $item['id']));
			$this->pdo->exec(sprintf('DELETE FROM pn_persons_images WHERE personId = %u', $item['id']));

			if ($item['pole33'] == 'Yes' || !empty($item['pole40'])) continue;

			$output->write("<info>Персона #" . $item['id'] . "...");

			$this->insert->bindValue(':id', $item['id']);
			$this->insert->bindValue(':name', $item['name']);
			$this->insert->bindValue(':englishName', $item['pole1']);
			$this->insert->bindValue(':genitiveName', $item['pole14']);
			$this->insert->bindValue(':prepositionalName', $item['pole15']);
			$this->insert->bindValue(':info', $item['pole2']);
			$this->insert->bindValue(':source', $item['pole4']);

			$personImage = ImageFactory::createFromUrl(sprintf('http://www.popcornnews.ru/upload1/%s', $item['pole5']));
			$this->insert->bindValue(':photo', $personImage->getId());

			//region Импортируем приложенные фотографии
			{
				$sql = <<<EOL
SELECT filename filepath FROM popcornnews.popkorn_user_pix where gid_ = :personId
union
SELECT diskname filepath FROM popcornnews.popconnews_pix where goods_id_ = :personId
EOL;
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindValue(':personId', $item['id'], \PDO::PARAM_INT);
				$stmt->execute();

				$images = [];

				while ($imagePath = $stmt->fetch(\PDO::FETCH_COLUMN)) {
					try {
						$url = sprintf('http://www.popcornnews.ru/upload1/%s', $imagePath);

						$output->write("\n\t<comment>Пытаемся скачать $url...</comment>");

						$images[] = ImageFactory::createFromUrl($url);
					} catch (Exception $e) {
						$output->write("<comment>неудачно</comment>\n");
						continue;
					}
				}

				if (count($images)) {

					$i = 0;

					foreach ($images as $image) {

						$stmt = $this->pdo->prepare('INSERT INTO pn_persons_images SET personId = :personId, imageId = :imageId, seq = :seq');
						$stmt->execute([
							':personId' => $item['id'],
							':imageId' => $image->getId(),
							':seq' => $i++
						]);
					}
				}
			}
			//endregion

			$bd = $item['pole10'];
			$y = substr($bd, 0, 4);
			$m = substr($bd, 4, 2);
			$d = substr($bd, 6, 2);
			$this->insert->bindValue(':birthDate', "{$y}-{$m}-{$d}");

			$this->insert->bindValue(':showInCloud', $item['pole11'] == 'Yes');
			$this->insert->bindValue(':sex', ($item['pole12'] == 'Yes' ? Person::FEMALE : Person::MALE));
			$this->insert->bindValue(':isSinger', $item['pole13'] == 'Yes');
			$this->insert->bindValue(':allowFacts', $item['pole25'] != 'Yes');
			$this->insert->bindValue(':vkPage', $item['pole26']);
			$this->insert->bindValue(':twitterLogin', $item['pole30']);
			$this->insert->bindValue(':pageName', $item['pole32']);
			$this->insert->bindValue(':nameForBio', $item['pole33']);
			$this->insert->bindValue(':published', 1);

			$urlName = str_replace('-', '_', $item['pole1']);
			$urlName = str_replace('&dash;', '_', $urlName);
			$urlName = str_replace(' ', '-', $urlName);
			$this->insert->bindValue(':urlName', $urlName);

			$this->insert->bindValue(':look', 0);
			$this->insert->bindValue(':style', 0);
			$this->insert->bindValue(':talent', 0);

			if (!$this->insert->execute()) {
				$output->writeln("</info>");
				$output->writeln("<error>" . print_r($this->insert->errorInfo(), true) . "</error>");
				exit;
			} else {
				$output->writeln("готово</info>");
			}
			$count++;
		}

		$output->writeln("<info>Импортированно {$count} персон из {$total}</info>");
		$this->selector->closeCursor();
	}

} 