<?php

namespace popcorn\model\dataMaps;

use popcorn\lib\PDOHelper;
use popcorn\model\content\ImageFactory;
use popcorn\model\persons\Kid;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\VotingFactory;

class KidDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_PHOTO = 2;
	const WITH_PARENTS = 4;
	const WITH_ALL = 7;

	private $modifier;

	public function __construct($modifier = self::WITH_PHOTO) {

		parent::__construct();

		$this->modifier = $modifier;

		$this->class = "popcorn\\model\\persons\\Kid";
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_kids (firstParent, secondParent, name, sex, description, birthDate, photo, votesUp, votesDown)
            VALUES (:firstParent, :secondParent, :name, :sex, :description, :birthDate, :photo, :votesUp, :votesDown)");
		$this->updateStatement =
			$this->prepare("
            UPDATE pn_kids SET firstParent=:firstParent, secondParent=:secondParent, name=:name, sex=:sex, description=:description, birthDate=:birthDate, photo=:photo, votesUp=:votesUp, votesDown=:votesDown WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_kids WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_kids WHERE id=:id");
	}

	public function getRandomKid() {

		$stmt = PDOHelper::getPDO()->query('select count(*) from pn_kids');
		$stmt->execute();

		$totalFound = $stmt->fetchColumn();

		$stmt = PDOHelper::getPDO()->prepare('select * from pn_kids limit :limitStart,1');
		$stmt->bindValue(':limitStart',rand(0,$totalFound-1),\PDO::PARAM_INT);
		$stmt->execute();

		$item = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class)[0];
		$this->itemCallback($item);

		return $item;

	}

	/**
	 * @param int $from
	 * @param $count
	 *
	 * @return Kid[]
	 */
	public function getKids($from = 0, $count = -1) {
		$sql = "SELECT * FROM pn_kids" . $this->getLimitString($from, $count);

		return $this->fetchAll($sql);
	}

	/**
	 * @param Kid $item
	 */
	protected function insertBindings($item) {

		if ($item->getFirstParent() instanceof Person) {
			$this->insertStatement->bindValue(":firstParent", $item->getFirstParent()->convert());
		} else {
			$this->insertStatement->bindValue(":firstParent", $item->getFirstParent());
		}

		if ($item->getSecondParent() instanceof Person) {
			$this->insertStatement->bindValue(":secondParent", $item->getSecondParent()->convert());
		} else {
			$this->insertStatement->bindValue(":secondParent", $item->getSecondParent());
		}

		$this->insertStatement->bindValue(":name", $item->getName());
		$this->insertStatement->bindValue(":sex", $item->getSex());
		$this->insertStatement->bindValue(":description", $item->getDescription());
		$this->insertStatement->bindValue(":birthDate", $item->getBirthDate()->format('Y-m-d'));
		$this->insertStatement->bindValue(":photo", $item->getPhoto()->convert());
		$this->insertStatement->bindValue(':votesUp', $item->getVotesUp());
		$this->insertStatement->bindValue(':votesDown', $item->getVotesDown());
	}

	/**
	 * @param Kid $item
	 */
	protected function updateBindings($item) {
		if ($item->getFirstParent() instanceof Person) {
			$this->updateStatement->bindValue(":firstParent", $item->getFirstParent()->convert());
		} else {
			$this->updateStatement->bindValue(":firstParent", $item->getFirstParent());
		}

		if ($item->getSecondParent() instanceof Person) {
			$this->updateStatement->bindValue(":secondParent", $item->getSecondParent()->convert());
		} else {
			$this->updateStatement->bindValue(":secondParent", $item->getSecondParent());
		}

		$this->updateStatement->bindValue(":name", $item->getName());
		$this->updateStatement->bindValue(":sex", $item->getSex());
		$this->updateStatement->bindValue(":description", $item->getDescription());
		$this->updateStatement->bindValue(":birthDate", $item->getBirthDate()->format('Y-m-d'));
		$this->updateStatement->bindValue(":photo", $item->getPhoto()->convert());
		$this->updateStatement->bindValue(':votesUp', $item->getVotesUp());
		$this->updateStatement->bindValue(':votesDown', $item->getVotesDown());
	}

	/**
	 * @param Kid $item
	 * @param int $modifier
	 */
	protected function itemCallback($item) {

		if ($this->modifier & self::WITH_PHOTO) {
			$item->setPhoto(ImageFactory::getImage($item->getPhoto()));
		}

		if ($this->modifier & self::WITH_PARENTS) {

			if ((int)$item->getFirstParent() > 0) {
				$item->setFirstParent(PersonFactory::getPerson($item->getFirstParent()));
			}

			if ((int)$item->getSecondParent() > 0) {
				$item->setSecondParent(PersonFactory::getPerson($item->getSecondParent()));
			}
		}

		$item->setBirthDate(new \DateTime($item->getBirthDate()));

		parent::itemCallback($item);

	}

	/**
	 * @param Kid $item
	 * @return \popcorn\model\Model
	 */
	protected function prepareItem($item) {

		if (!$item->getVotesDown()) {
			$item->setVotesDown(0);
		}

		if (!$item->getVotesUp()) {
			$item->setVotesUp(0);
		}

		return parent::prepareItem($item);

//		$item->setCommentsCount($item->getCommentsCount());

	}

	public function findWithPaginator(array $orders = [], array &$paginator = []) {

		$orders = array_merge([
			'name' => 'asc'
		], $orders);

		$sql = 'SELECT %s FROM pn_kids';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));
		$stmt->execute();
		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString($orders);
		$sql .= $this->getLimitString($paginator[0], $paginator[1]);

		$paginator['overall'] = $totalFound;
		$paginator['pages'] = ceil($totalFound / $paginator[1]);

		return $this->fetchAll(sprintf($sql, '*'));

	}

	/**
	 * @param $id
	 *
	 * @return null|Kid
	 */
	public function findById($id) {
		return parent::findById($id);
	}


}