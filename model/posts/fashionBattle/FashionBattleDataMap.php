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
                INSERT INTO pn_news_fashion_battle (newsId, firstOption, secondOption)
                VALUES (:newsId, :firstOption, :secondOption)");
		$this->updateStatement =
			$this->prepare("UPDATE pn_news_fashion_battle SET newsId=:newsId, firstOption=:firstOption, secondOption=:secondOption WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_news_fashion_battle WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_news_fashion_battle WHERE id=:id");

		$this->findByNewsIdStatement = $this->prepare("SELECT * FROM pn_news_fashion_battle WHERE newsId=:id");
	}

	/**
	 * @param FashionBattle $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":newsId", $item->getNewsId());
		$this->insertStatement->bindValue(":firstOption", $item->getFirstOption());
		$this->insertStatement->bindValue(":secondOption", $item->getSecondOption());
	}

	/**
	 * @param FashionBattle $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":newsId", $item->getNewsId());
		$this->updateStatement->bindValue(":firstOption", $item->getFirstOption());
		$this->updateStatement->bindValue(":secondOption", $item->getSecondOption());
		$this->updateStatement->bindValue(":id", $item->getId());
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