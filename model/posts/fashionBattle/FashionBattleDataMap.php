<?php

namespace popcorn\model\posts\fashionBattle;


use popcorn\model\dataMaps\DataMap;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\fashionBattle\FashionBattle;

class FashionBattleDataMap extends DataMap {

	public function __construct() {
		parent::__construct();
		$this->class = "popcorn\\model\\posts\\fashionBattle\\FashionBattle";
		$this->insertStatement =
			$this->prepare("
                INSERT INTO pn_news_fashion_battle (newsId, firstPerson, secondPerson)
                VALUES (:newsId, :firstPerson, :secondPerson)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_news_fashion_battle SET newsId=:newsId, firstPerson=:firstPerson, secondPerson=:secondPerson WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_news_fashion_battle WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_news_fashion_battle WHERE id=:id");

		$this->findByNewsIdStatement = $this->prepare("SELECT * FROM pn_news_fashion_battle WHERE newsId=:id");
	}

	/**
	 * @param FashionBattle $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":newsId", $item->getNewsId());
		$this->insertStatement->bindValue(":firstPerson", $item->getFirstPerson()->getId());
		$this->insertStatement->bindValue(":secondPerson", $item->getSecondPerson()->getId());
	}

	/**
	 * @param FashionBattle $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":newsId", $item->getTitle());
		$this->updateStatement->bindValue(":firstPerson", $item->getFirstPerson());
		$this->updateStatement->bindValue(":secondPerson", $item->getSecondPerson());
	}

	/**
	 * @param FashionBattle $item
	 */
	protected function itemCallback($item) {
        $item->setFirstPerson(PersonFactory::getPerson($item->getFirstPerson()));
		$item->setSecondPerson(PersonFactory::getPerson($item->getSecondPerson()));
		parent::itemCallback($item);
	}

	public function getByNewsId($newsId) {

		$items = $this->fetchAll("SELECT * FROM pn_news_fashion_battle WHERE newsId=:newsId limit 1",[
			':newsId' => $newsId
		]);

		if (count($items) == 1){
			return $items[0];
		}

		return false;

	}

	/**
	 * @param \popcorn\model\posts\NewsPost $item
	 */
	public function saveWithPost($item){
		$item->getFashionBattle()->setNewsId($item->getId());


		parent::save($item->getFashionBattle());
	}

	/**
	 * @param \popcorn\model\posts\NewsPost $item
	 * @return bool
	 */
	public function deleteWithPost($item){
		/** @var FashionBattle $fashionBattle */
		$fashionBattle = $this->getByNewsId($item->getId());

		if ($fashionBattle instanceof FashionBattle){
			return parent::delete($fashionBattle->getId());
		}

		return false;

	}

	/**
	 * @param FashionBattle $item
	 */
	protected function onSave($item) {
		parent::onSave($item);
		//$this->images->save($item->getImages(), $item->getId());
	}

}