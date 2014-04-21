<?php

require_once __DIR__ . '/sphinxapi.php';

/**
 * Class akSphinx
 * Date begin: 19.06.2011
 *
 * Searcher
 *
 * See also:
 * 	akLayout
 * 	akDiff
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
class akSphinx {
	/**
	 * @var sphinxapi
	 */
	protected $_client;
	/**
	 * Search query
	 *
	 * @var string
	 */
	protected $_q;
	/**
	 * Indexes to object
	 *
	 * @var array
	 */
	static protected $_indexesObjects = array(
		100 => 'VPA_table_yourstyle_tiles_brands',
	);
	/**
	 * Avaliable indexes
	 *
	 * @var array
	 */
	static protected $_indexes = array(
		100 => 'popcornnews_yourstyle_tiles_brands',
	);


	static public function getInstance() {
		static $o;
		if (!$o) {
			$o = new self;
		}
		return $o;
	}

	/**
	 * Search items
	 *
	 * @param string $query
	 * @param string $index
	 * @param int $offset
	 * @param int $limit
	 * @param bool $idsOnly
	 * @return mixed
	 */
	public function search($query, $index = '*', $offset = 0, $limit = 100, $idsOnly = false) {
		if (!self::_isIndexValid($index)) {
			throw new Exception('Such index is not avaliable');
		}
		$this->_q = $query;

		$this->_client->SetLimits($offset, $limit);
		$result = $this->_client->Query($query, $index);

		$out = array();
		if (!empty($result['matches'])) {
			foreach ($result['matches'] as &$record) {
				$out[$record['attrs']['index_name']][$record['id']] = $record;
			}
		}
		$founded = array();
		if (!empty($out)) {
			foreach ($out as $index => $rows) {
				$founded = $idsOnly ? array_merge($founded, array_keys($rows)) : array_merge($founded, $this->_fetchRowsInfo($index, $rows));
			}
		}
		return $founded;
	}

	/**
	 * Restore default settings
	 *
	 * @return akSphinx - current object
	 */
	public function restoreSettings() {
		$this->_client->SetConnectTimeout(1);
		$this->_client->setMaxQueryTime(5);
		$this->_client->SetArrayResult(true);
		$this->setMatchMode();
		$this->_client->SetSortMode(SPH_SORT_RELEVANCE);
		$this->_client->SetRankingMode(SPH_RANK_WORDCOUNT);
		$this->_client->SetFieldWeights(
			array(
				'title' => 50,
				'description' => 10,
			)
		);

		return $this;
	}

	/**
	 * Set match mode
	 *
	 * @param string $mode - mode/ if null set it to default
	 * @return akSphinx - current object
	 */
	public function setMatchMode($mode = null) {
		$this->_client->SetMatchMode(is_null($mode) ? SPH_MATCH_ANY : $mode);

		return $this;
	}

	protected function _fetchRowsInfo($index, array $rows) {
		$className = self::_getIndexObject($index);
		$o = new $className;

		$ids = array_keys($rows);
		$rows = $o->get_fetch(array('id_in' => $ids), array(sprintf('FIELD(id, %s)', join(',', $ids))), 0, count($ids));
		foreach ($rows as &$row) {
			$row = $this->_convertInfo($index, $row);
		}

		return $rows;
	}

	static protected function _isIndexValid($index) {
		if ($index == '*') {
			return true;
		}
		return in_array($index, self::$_indexes);
	}

	static protected function _getIndexObject($index) {
		if (is_numeric($index)) {
			return self::$_indexesObjects[$index];
		}
		return self::$_indexesObjects[array_search($index, self::$_indexes)];
	}

	static protected function _getIndexName($index) {
		if (is_numeric($index)) {
			return self::$_indexes[$index];
		}
		return self::$_indexes[array_search($index, self::$_indexesObjects)];
	}

	/**
	* Get trigrams
	*
	* @param string $word - word
	* @return array
	*
	* @example "deal" => array("__d", "_de", "dea", "eal", "al_", "l__")
	*/
	static public function getTrigrams($word) {
		if (!$word) return false;
		$length = strlen($word);

		$trigrams = array();
		for ($i = 0; $i < $length+2; $i++) {
			$trigrams[] = (isset($word[$i-2]) ? $word[$i-2] : '_') . (isset($word[$i-1]) ? $word[$i-1] : '_') . (isset($word[$i]) ? $word[$i] : '_');
		}
		return $trigrams;
	}

	/**
	* Get suggest optimal params
	* min number of trigrams & min/max length
	*
	* @param int $len - length
	*/
	static public function getSuggestOptimalParams($len) {
		if ($len <= 5) {
			$trigrams = $len - 1;
			$minLength = $len - 1;
			$maxLength = $len + 1;
		} elseif ($len > 5 && $len <= 8) {
			$trigrams = $len - 2;
			$minLength = $len - 2;
			$maxLength = $len + 2;
		} else {
			$trigrams = $len - 3;
			$minLength = $len - 3;
			$maxLength = $len + 3;
		}
		if ($trigrams <= 0) {
			$trigrams = 1;
		}

		return array($trigrams, $minLength, $maxLength);
	}

	protected function _convertInfo($index, array $row) {
		$row['title'] = $this->_highLight($index, $row['title']);
		if (isset($row['description'])) $row['description'] = $this->_highLight($index, $row['description']);
		return $row;
	}

	/**
	 * HighLight
	 *
	 * @param string $index - index name
	 * @param mixed $text - text
	 * @return string or false
	 */
	protected function _highLight($index, $text) {
		if (!$text) return null;
		if (is_scalar($text)) $text = array($text);

		foreach ($text as $i => $row) {
			$text[$i] = strip_tags($row);
		}

		static $options = array(
			'before_match' => '<strong style="background-color: #DAE2E8;">',
			'after_match' => '</strong>',
			'limit' => 256,
		);

		return join('',
			$this->_client->buildExcerpts(
				$text,
				self::_getIndexName($index),
				$this->_q,
				$options
			)
		);
	}

	private function __construct() {
		$this->_client = new SphinxClient;

		$this->_client->SetServer('unix:///var/run/sphinxsearch/searchd.sock');
		$this->restoreSettings();
	}
}
