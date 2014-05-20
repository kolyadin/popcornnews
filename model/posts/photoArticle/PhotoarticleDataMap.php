<?php

namespace popcorn\model\posts\photoArticle;

use popcorn\lib\mmc\MMC;
use popcorn\model\content\Image;
use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\PersonDataMap;
use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\tags\Tag;

class PhotoArticleDataMap extends DataMap {

	const WITH_NONE = 1;
	const WITH_TAGS = 2;
	const WITH_IMAGES = 4;
	const WITH_ALL = 7;

	/**
	 * @var PhotoArticleImageDataMap
	 */
	private $imagesDataMap;
	/**
	 * @var PhotoArticleTagDataMap
	 */
	private $tagsDataMap;

	public function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct();

		$this->class = "popcorn\\model\\posts\\photoArticle\\PhotoArticlePost";
		$this->initStatements();
		$this->imagesDataMap = new PhotoArticleImageDataMap();
		$this->tagsDataMap = new PhotoArticleTagDataMap();
	}

	private function initStatements() {
		$this->insertStatement =
			$this->prepare("INSERT INTO pn_photoarticles
			(name, createDate, editDate, views, comments)
				VALUES
			(:name, :createDate, :editDate, :views, :comments)");
		$this->updateStatement =
			$this->prepare("
			UPDATE pn_photoarticles SET name=:name, createDate=:createDate, editDate=:editDate, views=:views, comments=:comments WHERE id=:id");
		$this->deleteStatement = $this->prepare("DELETE FROM pn_photoarticles WHERE id=:id");
		$this->findOneStatement = $this->prepare("SELECT * FROM pn_photoarticles WHERE id=:id");
	}

	/**
	 * @param PhotoArticlePost $item
	 */
	protected function insertBindings($item) {
		$this->insertStatement->bindValue(":name", $item->getName());
		$this->insertStatement->bindValue(":editDate", $item->getEditDate()->getTimestamp());
		$this->insertStatement->bindValue(":createDate", $item->getCreateDate()->getTimestamp());
		$this->insertStatement->bindValue(":views", $item->getViews());
		$this->insertStatement->bindValue(":comments", $item->getComments());
	}

	/**
	 * @param PhotoArticlePost $item
	 */
	protected function updateBindings($item) {
		$this->updateStatement->bindValue(":name", $item->getName());
		$this->updateStatement->bindValue(":editDate", $item->getEditDate()->getTimestamp());
		$this->updateStatement->bindValue(":createDate", $item->getCreateDate()->getTimestamp());
		$this->updateStatement->bindValue(":views", $item->getViews());
		$this->updateStatement->bindValue(":comments", $item->getComments());
		$this->updateStatement->bindValue(":id", $item->getId());
	}

	/**
	 * @param PhotoArticlePost $item
	 * @param int $modifier
	 */
	public function itemCallback($item, $modifier = self::WITH_TAGS) {

		parent::itemCallback($item);

		$modifier = $this->getModifier($this, $modifier);

		if ($modifier & self::WITH_TAGS) {
			$item->setTags($this->getAttachedTags($item->getId()));
		}

		if ($modifier & self::WITH_IMAGES) {
			$item->setImages($this->getAttachedImages($item->getId()));
		}

	}

	/**
	 * @param PhotoArticlePost $item
	 */
	protected function onInsert($item) {
		$this->attachImages($item);
		$this->attachTags($item);

		MMC::delByTag('photoarticle');
	}

	/**
	 * @param PhotoArticlePost $item
	 */
	protected function onUpdate($item) {
		$this->attachImages($item);
		$this->attachTags($item);

		MMC::delByTag('photoarticle');
	}

	/**
	 * При удалении новости удаляем:
	 * - все вложенные фотки
	 * - все связи тегов
	 *
	 * @param $postId
	 */
	protected function onRemove($postId) {

		$this->getPDO()->prepare('
			DELETE FROM pn_photoarticles_images WHERE photoarticleId = :postId;
			DELETE FROM pn_photoarticles_tags WHERE photoarticleId = :postId;
		')->execute([
			':postId' => $postId
		]);

		MMC::delByTag('photoarticle');
	}

	/**
	 * @param PhotoArticlePost $item
	 */
	private function attachTags($item) {
		$this->tagsDataMap->save($item->getTags(), $item->getId());
	}

	/**
	 * @param int $id
	 *
	 * @return Tag[]
	 */
	private function getAttachedTags($id) {
		return $this->tagsDataMap->findById($id);
	}

	/**
	 * @param PhotoArticlePost $item
	 */
	private function attachImages($item) {
		$this->imagesDataMap->save($item->getImages(), $item->getId());

	}

	private function getAttachedImages($id) {
		$items = $this->imagesDataMap->findById($id);

		foreach ($items as &$image) {
			$image->setExtra($this->getAttachPersonsOfImage($image));
		}

		return $items;
	}

	public function getAttachPersonsOfImage(Image $image){
		$stmt = $this->prepare('select personId from pn_photoarticles_images_persons where imageId = :imageId');
		$stmt->execute([
			':imageId'  => $image->getId(),
		]);

		$persons = [];

		while ($personId = $stmt->fetch(\PDO::FETCH_COLUMN)){
			$persons[] = PersonFactory::getPerson($personId,[
				'itemCallback' => [
					'popcorn\\model\\dataMaps\\PersonDataMap' => PersonDataMap::WITH_NONE
				]
			]);
		}

		return $persons;
	}

	public function attachPersonToImage(Image $image, Person $person) {

		$stmt = $this->prepare('insert into pn_photoarticles_images_persons set imageId = :imageId, personId = :personId');
		$stmt->execute([
			':imageId'  => $image->getId(),
			':personId' => $person->getId()
		]);

	}

	public function clearImageFromPersons(Image $image) {

		$stmt = $this->prepare('delete from pn_photoarticles_images_persons where imageId = :imageId');
		$stmt->execute([
			':imageId' => $image->getId()
		]);

	}

	/**
	 * @param $postId
	 * @param array $options
	 * @return PhotoArticlePost
	 */
	public function findById($postId, array $options = []) {
		return $this->fetchOne('SELECT * FROM pn_photoarticles WHERE id = :postId LIMIT 1', [
			':postId' => $postId
		]);
	}

	/**
	 * @param array $options
	 * @param int $from
	 * @param $count
	 * @param int $totalFound
	 * @return PhotoArticlePost[]
	 */
	public function findByLimit(array $options = [], $from = 0, $count = -1, &$totalFound = 0) {

		$options = array_merge([
			'orderBy' => [
				'createDate' => 'desc'
			]
		], $options);

		$sql = 'SELECT %s FROM pn_photoarticles WHERE 1=1';

		$stmt = $this->prepare(sprintf($sql, 'count(*)'));

		$totalFound = $stmt->fetchColumn();

		$sql .= $this->getOrderString($options['orderBy']);
		$sql .= $this->getLimitString($from, $count);

		return $this->fetchAll(sprintf($sql, '*'));
	}

	/**
	 * @param $from
	 * @param $to
	 * @param int $limit
	 * @return PhotoArticlePost[]
	 */
	public function findByDate($from, $to, $limit = 0) {
		$sql = "SELECT *
				FROM pn_photoarticles
				WHERE createDate BETWEEN $from AND $to
				ORDER BY createDate DESC";

		if ($limit) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $this->fetchAll($sql);
	}

	/**
	 * @param PhotoArticlePost $post
	 */
	public function updateViews(PhotoArticlePost $post) {

		$stmt = $this->prepare('UPDATE pn_photoarticles SET views = views+1 WHERE id = :postId LIMIT 1');
		$stmt->execute([
			':postId' => $post->getId()
		]);

	}


}