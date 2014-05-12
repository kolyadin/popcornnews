<?php
/**
 * User: anubis
 * Date: 22.11.13 16:11
 */

namespace popcorn\cli\command\person;


use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\FileNotFoundException;
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
				'person-id',
				null,
				InputOption::VALUE_REQUIRED,
				'Импорт одной конкретной персоны'
			)
			->addOption(
				'set-images-limit',
				null,
				InputOption::VALUE_REQUIRED,
				'Ограничим кол-во импортируемых фото'
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

		PDOHelper::truncate(['pn_persons', 'pn_persons_images']);

		if ($input->getOption('person-id')) {

			$this->selector = $this->pdo->prepare("SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 3 AND id = :personId");
			$this->selector->bindValue(':personId', $input->getOption('personId'), \PDO::PARAM_INT);

		} else {
			$this->selector = $this->pdo->prepare("SELECT * FROM popcornnews.popconnews_goods_ WHERE goods_id = 3");
		}

		$this->selector->execute();

		$count = 0;

		while ($table = $this->selector->fetch(\PDO::FETCH_ASSOC)) {

			if ($table['pole33'] == 'Yes' || !empty($table['pole40'])) continue;

			$output->write("<info>Персона #" . $table['id'] . ' ' . $table['name'] . "...");

			$this->insert->bindValue(':id', $table['id']);
			$this->insert->bindValue(':name', $table['name']);
			$this->insert->bindValue(':englishName', $table['pole1']);
			$this->insert->bindValue(':genitiveName', $table['pole14']);
			$this->insert->bindValue(':prepositionalName', $table['pole15']);
			$this->insert->bindValue(':info', $table['pole2']);
			$this->insert->bindValue(':source', $table['pole4']);

			try {
				$personImage = ImageFactory::createFromUrl(sprintf('http://www.popcornnews.ru/upload1/%s', $table['pole5']));
				$this->insert->bindValue(':photo', $personImage->getId());
			} catch (FileNotFoundException $e) {
				$this->insert->bindValue(':photo', 0);
			}


			//region Импортируем приложенные фотографии
			{
				$sql = '
					SELECT filename filepath FROM popcornnews.popkorn_user_pix where gid_ = :personId
					union
					SELECT diskname filepath FROM popcornnews.popconnews_pix where goods_id_ = :personId
					Limit %u';

				$stmt = $this->pdo->prepare(sprintf($sql, $input->getOption('set-images-limit') ? : 999999));
				$stmt->bindValue(':personId', $table['id'], \PDO::PARAM_INT);
				$stmt->execute();

				$images = [];

				while ($imagePath = $stmt->fetch(\PDO::FETCH_COLUMN)) {
					try {
						$url = sprintf('http://www.popcornnews.ru/upload1/%s', $imagePath);

						$output->write("\n\t<comment>Пытаемся скачать $url");
						$images[] = ImageFactory::createFromUrl($url);
						$output->write(" готово</comment>\n");

					} catch (Exception $e) {
						$output->write(" неудачно</comment>\n");
						continue;
					}
				}

				if (count($images)) {

					$i = 0;

					foreach ($images as $image) {
						$stmt = $this->pdo->prepare('INSERT INTO pn_persons_images SET personId = :personId, imageId = :imageId, seq = :seq');
						$stmt->execute([
							':personId' => $table['id'],
							':imageId' => $image->getId(),
							':seq' => $i++
						]);
					}
				}
			}
			//endregion

			$bd = $table['pole10'];
			$y = substr($bd, 0, 4);
			$m = substr($bd, 4, 2);
			$d = substr($bd, 6, 2);
			$this->insert->bindValue(':birthDate', "{$y}-{$m}-{$d}");

			$this->insert->bindValue(':showInCloud', $table['pole11'] == 'Yes');
			$this->insert->bindValue(':sex', ($table['pole12'] == 'Yes' ? Person::FEMALE : Person::MALE));
			$this->insert->bindValue(':isSinger', $table['pole13'] == 'Yes');
			$this->insert->bindValue(':allowFacts', $table['pole25'] != 'Yes');
			$this->insert->bindValue(':vkPage', $table['pole26']);
			$this->insert->bindValue(':twitterLogin', $table['pole30']);
			$this->insert->bindValue(':pageName', $table['pole32']);
			$this->insert->bindValue(':nameForBio', $table['pole33']);
			$this->insert->bindValue(':published', 1);

			$urlName = str_replace('-', '_', $table['pole1']);
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

		$total = $this->selector->rowCount();

		$output->writeln("<info>Импортированно {$count} персон из {$total}</info>");
		$this->selector->closeCursor();
	}

} 