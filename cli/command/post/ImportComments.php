<?php
/**
 * User: anubis
 * Date: 22.11.13 16:11
 */

namespace popcorn\cli\command\post;


use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\FileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportComments
 * @package popcorn\cli\command\post
 */
class ImportComments extends Command {

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

	/**
	 * @var \PDOStatement
	 */
	private $insertImage;

	protected function configure() {
		$this->setName('import:post:comments')
			->setDescription("Импорт комментов для постов (новостей)");

		$this->pdo = PDOHelper::getPDO();

		$this->selector = $this->pdo->prepare("SELECT * FROM popcornnews.pn_comments_news ORDER BY id DESC");
		$this->insert = $this->pdo->prepare("
INSERT INTO pn_comments_kids (
  id, kidId, date, owner,
  parent, content, editDate,
  ip, abuse, ratingDown, ratingUp,
  deleted, level, imagesCount
)
VALUES (
  :id, :kidId, :date, :owner,
  :parent, :content, :editDate,
  :ip, :abuse, :ratingDown, :ratingUp,
  :deleted, :level, :imagesCount
)");
		$this->insertImage = $this->pdo->prepare("
INSERT INTO pn_comments_kids_images (commentId, imageId)
VALUES (:commentId, :imageId)
");

	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln('<info>Импорт комментарий детей...</info>');

		$this->selector->execute();

		$count = 0;
		$total = $this->selector->rowCount();

		for($i = 0; $i < $total; $i++) {

			$item = $this->selector->fetch(\PDO::FETCH_ASSOC);

			$output->write("<info>Коммент #".$item['id']."...");

			$content = $item['content'];

			preg_match_all('@\[img\](.+)\[\/img\]@iU',$content,$matches);

			$imagesCount = 0;

			if (isset($matches[1]) && count($matches[1])){
				foreach ($matches[1] as $imageUrl){
					try{
						$output->write("\n\t<comment>Пытаемся скачать $imageUrl...</comment>");

						$image = ImageFactory::createFromUrl($imageUrl);

						$this->insertImage->bindValue(':commentId',$item['id']);
						$this->insertImage->bindValue(':imageId',$image->getId());
						$this->insertImage->execute();

						$imagesCount++;

						$output->write("<comment>ok</comment>\n");

					} catch (FileNotFoundException $e){
						$output->write("<comment>неудачно</comment>\n");
						continue;
					}
				}
			}

			$content = preg_replace('@\[img\].+\[\/img\]@iU','',$content);
			$content = trim($content);

			//Не будем добавлять коммент, если нет фоток и текст пустой
			if ($imagesCount == 0 && empty($content)){
				continue;
			}

			$item['content'] = $content;

			$this->insert->bindValue(':id', $item['id']);
			$this->insert->bindValue(':kidId', $item['news_id']);
			$this->insert->bindValue(':date', $item['date']);
			$this->insert->bindValue(':owner', $item['owner']);
			$this->insert->bindValue(':parent', $item['parent']);
			$this->insert->bindValue(':content', $item['content']);
			$this->insert->bindValue(':editDate', $item['edit_date']);
			$this->insert->bindValue(':ip', $item['ip']);
			$this->insert->bindValue(':abuse', $item['abuse']);
			$this->insert->bindValue(':ratingDown', $item['rating_down']);
			$this->insert->bindValue(':ratingUp', $item['rating_up']);
			$this->insert->bindValue(':deleted', $item['deleted']);
			$this->insert->bindValue(':level', 0);
			$this->insert->bindValue(':imagesCount', $imagesCount);

			if(!$this->insert->execute()) {
				$output->writeln("</info>");
				$output->writeln("<error>".print_r($this->insert->errorInfo(), true)."</error>");
				exit;
			}
			else {
				$output->writeln("готово</info>");
			}
			$count++;

		}

		$output->writeln("<info>Импортированно {$count} комментов из {$total}</info>");
		$this->selector->closeCursor();
	}
}