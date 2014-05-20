<?php

namespace popcorn\model\posts\photoArticle;

use popcorn\model\dataMaps\DataMap;
use popcorn\model\dataMaps\DataMapHelper;
use popcorn\model\dataMaps\NewsTagDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\tags\Tag;

class PhotoArticleTagDataMap extends NewsTagDataMap {

	function __construct(DataMapHelper $helper = null) {

		if ($helper instanceof DataMapHelper) {
			DataMap::setHelper($helper);
		}

		parent::__construct($helper);

		$this->findLinkedStatement =
			$this->prepare("
                SELECT t.id,t.name,t.type FROM pn_tags AS t
				JOIN pn_photoarticles_tags AS l ON (l.entityId = t.id and (l.type = " . Tag::ARTICLE . " or l.type = " . Tag::EVENT . "))
				WHERE l.photoarticleId = :id

				union all

				SELECT p.id,p.name," . Tag::PERSON . " type FROM pn_persons AS p
				JOIN pn_photoarticles_tags AS l ON (l.entityId = p.id and l.type = " . Tag::PERSON . ")
				WHERE l.photoarticleId = :id

				union all

				SELECT m.id,m.name," . Tag::MOVIE . " type FROM ka_movies AS m
				JOIN pn_photoarticles_tags AS l ON (l.entityId = m.id and l.type = " . Tag::MOVIE . ")
				WHERE l.photoarticleId = :id");

		$this->cleanStatement = $this->prepare("DELETE FROM pn_photoarticles_tags WHERE photoarticleId = :id");
		$this->insertStatement = $this->prepare("INSERT INTO pn_photoarticles_tags (photoarticleId, type, entityId) VALUES (:id, :type, :entityId)");
		$this->fidnByLinkStatement = $this->prepare("
            SELECT n.* FROM pn_photoarticles AS n
            INNER JOIN pn_photoarticles_tags AS t ON (n.id = t.photoarticleId)
            WHERE t.tagId = :id
        ");
	}

	protected function mainDataMap() {
		return new TagDataMap();
	}

}