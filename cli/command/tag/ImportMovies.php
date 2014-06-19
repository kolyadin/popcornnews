<?php
/**
 * User: anubis
 * Date: 22.11.13 16:11
 */

namespace popcorn\cli\command\tag;


use DateTime;
use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\exceptions\Exception;
use popcorn\model\exceptions\FileNotFoundException;
use popcorn\model\tags\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMovies extends Command {

	/**
	 * @var \PDO
	 */
	private $pdo;

	private $timer;

	/**
	 * @var \PDOStatement
	 */
	private $stmtMovies, $stmtPersons, $stmtDeleteFilmography, $stmtAddFilmography;

	private function init() {
		$this->pdo = PDOHelper::getPDO();

		$this->stmtPersons = $this->pdo->query('select * from pn_persons order by name asc');
		$this->stmtMovies = $this->pdo->prepare('select id from kinoafisha.kinoafisha_v2_goods_ where page_id = 2 and goods_id = 110 and pole5 like :personName');
		$this->stmtDeleteFilmography =
			$this->pdo->prepare('delete from pn_persons_movies where personId = :personId');
		$this->stmtAddFilmography =
			$this->pdo->prepare('insert into pn_persons_movies set personId = :personId, movieId = :movieId');
	}

	protected function configure() {
		$this->setName('import:movies')
			->setDescription("Импорт фильмов в систему тегов попкорна + простановка фильмографии у персон");

		$this->timer = microtime(1);



	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->init();

		$output->write('<info>Импорт всех тегов для всех новостей...</info>');

		$this->pdo->exec('lock tables ka_movies WRITE, kinoafisha.kinoafisha_v2_goods_ READ;');

		PDOHelper::truncate(['ka_movies']);

		$sql = 'insert into ka_movies (id,name,originalName,year)
		SELECT id,name,pole1 originalName,pole2 year FROM kinoafisha.kinoafisha_v2_goods_ WHERE page_id = 2 AND goods_id = 110';

		$this->pdo->exec($sql);

		$this->pdo->exec('unlock tables;');

		$output->writeln('<info> готово</info>');

		$output->writeln('<info>Обновление фильмографии всех персон...</info>');

		$this->stmtPersons->execute();

		while ($table = $this->stmtPersons->fetch(\PDO::FETCH_ASSOC)) {

			$output->write("<info>Генерация фильмографии \"{$table['name']}\"...</info>");

			$personId = $table['id'];

			$this->stmtMovies->execute([
				':personName' => '%' . $table['name'] . '%'
			]);

			$movies = $this->stmtMovies->fetchAll(\PDO::FETCH_COLUMN);

			if (count($movies)) {

				$this->stmtDeleteFilmography->execute([
					':personId' => $personId
				]);

				foreach ($movies as $movieId) {
					$this->stmtAddFilmography->execute([
						':personId' => $personId,
						':movieId' => $movieId
					]);
				}

				$output->writeln("<info> [фильмов: ".count($movies)."]</info>");
			}else{
				$output->writeln("<info> [нет фильмов]</info>");
			}
		}

		$output->writeln(sprintf('<info>Задача выполнена за %.2f</info>',microtime(1)-$this->timer));
	}
}