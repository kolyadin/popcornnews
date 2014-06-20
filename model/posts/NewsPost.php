<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 11:46
 */

namespace popcorn\model\posts;

use popcorn\model\Model;
use popcorn\model\content\Image;
use popcorn\model\posts\fashionBattle\FashionBattle;
use popcorn\model\tags\Tag;
use Stringy\Stringy as S;

/**
 * Class NewsPost
 * @package popcorn\model\posts
 * @table pn_news
 */
class NewsPost extends Model {

	//Новость не опубликована, невозможно найти на сайте
	const STATUS_NOT_PUBLISHED = 0;

	//Новость опубликована и доступна везде на сайте
	const STATUS_PUBLISHED = 1;

	//Новость запланирована (дата в будущем)
	const STATUS_PLANNED = 2;


	const TAGS_FILTER_ARTICLES = 1;
	const TAGS_FILTER_EVENTS = 2;
	const TAGS_FILTER_PERSONS = 4;
	const TAGS_FILTER_MOVIES = 8;
	const TAGS_FILTER_ALL = 15;


	private $encoding = 'utf-8';

	//region Fields

	/**
	 * @var string
	 * @export
	 */
	protected $announce = '';
	/**
	 * @var string
	 * @export
	 */
	protected $source = '';
	/**
	 * @var int
	 * @export
	 */
	protected $sent = 0;

	/**
	 * @var int
	 * @export
	 */
	protected $uploadRSS = 0;
	/**
	 * @var int
	 * @export
	 */
	protected $mainImageId = 0;
	/**
	 * @var Image[]
	 */
	protected $images = array();
	/**
	 * @var
	 */
	protected $tags = array();

	/**
	 * @var string
	 * @export
	 */
	protected $name = '';
	/**
	 * Дата создания новости, можно менять (можно отправлять в будущее)
	 * @var \DateTime
	 * @export
	 */
	protected $createDate;
	/**
	 * Дата изменения новости, обновляется по триггеру
	 * @var \DateTime
	 * @export
	 */
	protected $editDate;
	/**
	 * @var string
	 * @export
	 */
	protected $content = '';
	/**
	 * @var int
	 * @export
	 */
	protected $allowComment = 1;
	/**
	 * @var int
	 * @export
	 */
	protected $status = self::STATUS_PUBLISHED;
	/**
	 * @var int
	 * @export
	 */
	protected $views = 0;
	/**
	 * @var int
	 * @export
	 */
	protected $comments = 0;
	/**
	 * @var string
	 * @export
	 */
	protected $type = '';

	protected $fashionBattle = null;

	//endregion

	function __construct() {
		$this->type = get_class($this);

		$this->editDate = new \DateTime('now');
	}

	//region Getters

	/**
	 * @return bool
	 */
	public function getAllowComment() {
		return $this->allowComment;
	}

	/**
	 * @return string
	 */
	public function getAnnounce() {
		return $this->announce;
	}

	/**
	 * @return int
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreateDate() {
		return $this->createDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getEditDate() {
		return $this->editDate;
	}

	/**
	 * @return Image
	 */
	public function getMainImageId() {
		return $this->mainImageId;
	}

	/**
	 * @return string
	 */
	public function getName() {

		return html_entity_decode($this->name, ENT_QUOTES);

	}

	/**
	 * @return int
	 * @get published
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return bool
	 * @get sent
	 */
	public function isSent() {
		return $this->sent;
	}

	public function isPost() {
		return true;
	}

	/**
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getUploadRSS() {
		return $this->uploadRSS;
	}

	/**
	 * @return int
	 */
	public function getViews() {
		return $this->views;
	}

	/**
	 * @return \popcorn\model\content\Image[]
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * @param int $filter
	 * @return \popcorn\model\tags\Tag[]
	 */
	public function getTags($filter = self::TAGS_FILTER_ALL) {

		$tags = [];

		if ($filter & self::TAGS_FILTER_ALL) {
			$tags = $this->tags;
		} elseif ($filter & self::TAGS_FILTER_ARTICLES) {
			/** @var Tag $tag */
			foreach ($this->tags as $tag) {
				if ($tag->isArticle()){
					$tags[] = $tag;
				}
			}
		} elseif ($filter & self::TAGS_FILTER_EVENTS) {
			/** @var Tag $tag */
			foreach ($this->tags as $tag) {
				if ($tag->isEvent()){
					$tags[] = $tag;
				}
			}
		} elseif ($filter & self::TAGS_FILTER_PERSONS) {
			/** @var \popcorn\model\persons\Person $tag */
			foreach ($this->tags as $tag) {
				if ($tag->isPerson()){
					$tags[] = $tag;
				}
			}
		} elseif ($filter & self::TAGS_FILTER_MOVIES) {
			/** @var \popcorn\model\posts\Movie $tag */
			foreach ($this->tags as $tag) {
				if ($tag->isMovie()){
					$tags[] = $tag;
				}
			}
		}

		return $tags;
	}

	/**
	 * @return \popcorn\model\tags\Tag[]
	 */
	public function getTagsOnly() {

		$tags = [];

		foreach ($this->tags as $tag) {
			if ($tag->getType() != Tag::ARTICLE) {
				$tags[] = $tag;
			}
		}

		return $tags;

	}

	//endregion

	//region Setters

	/**
	 * @param int $allowComment
	 */
	public function setAllowComment($allowComment) {
		$this->allowComment = $allowComment;
		$this->changed();
	}

	/**
	 * @param string $announce
	 */
	public function setAnnounce($announce) {
		$this->announce = $announce;
		$this->changed();
	}

	/**
	 * @param int $comments
	 */
	public function setComments($comments) {
		$this->comments = $comments;
		$this->changed();
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
		$this->changed();
	}

	/**
	 * @param \DateTime $dateTime
	 */
	public function setCreateDate(\DateTime $dateTime) {
		$this->createDate = $dateTime;
		$this->changed();
	}

	/**
	 * @param \DateTime $dateTime
	 */
	public function setEditDate(\DateTime $dateTime) {
		$this->editDate = $dateTime;
		$this->changed();
	}

	/**
	 * @param \popcorn\model\content\Image[] $images
	 */
	public function setImages($images) {
		$this->images = $images;
	}

	/**
	 * @param Image $mainImageId
	 */
	public function setMainImageId($mainImageId) {
		$changed = false;
		if (is_object($this->mainImageId)) {
			$changed = true;
			if ($mainImageId == $this->mainImageId) {
				return;
			}
		}
		$this->mainImageId = $mainImageId;
		if ($changed) $this->changed();
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
		$this->changed();
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status) {
		$this->status = $status;
		$this->changed();
	}

	/**
	 * @param int $sent
	 */
	public function setSent($sent) {
		$this->sent = $sent;
		$this->changed();
	}

	/**
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
		$this->changed();
	}

	/**
	 * Изменения тегов через этот метод не схоранятся!
	 * @param \popcorn\model\tags\Tag[] $tags
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}

	/**
	 * @param int $uploadRSS
	 */
	public function setUploadRSS($uploadRSS) {
		$this->uploadRSS = $uploadRSS;
		$this->changed();
	}

	/**
	 * @param int $views
	 */
	public function setViews($views) {
		$this->views = $views;
		$this->changed();
	}

	//endregion


	/**
	 * @return FashionBattle
	 */
	public function getFashionBattle() {
		return $this->fashionBattle;
	}

	/**
	 * @param FashionBattle $fb
	 */
	public function addFashionBattle($fb) {
		$this->fashionBattle = $fb;
	}

	public function removeFashionBattle() {
		$this->fashionBattle = null;
	}

	/**
	 * @param popcorn\\model\\persons\\Person | popcorn\\model\\tags\\Tag | popcorn\\model\\posts\\Movie $entity
	 */
	public function addTag($tag) {
		$this->tags[] = $tag;
		$this->changed();
	}

	/**
	 * @param Image $img
	 */
	public function addImage($img) {
		if (array_search($img, $this->images) === false) {
			$this->images[] = $img;
			$this->changed();
		}
	}

	public function clearTags() {
		$this->tags = [];
		$this->changed();
	}

	public function clearImages() {
		$this->images = [];
		$this->changed();
	}

	/**
	 * @param int $announceLength
	 * @return null|string
	 */
	public function getAnnounceFriendly($announceLength = null) {

		$announce = trim($this->getAnnounce());
		$content = trim($this->getContent());

		$output = null;

		if (!$announce) {
			if ($content) {
				$content = strip_tags($content, '<a><strong><b><em><i>');
				$output = substr($content, 0, $announceLength) . '...';
			}
		} else {
			if (!is_null($announceLength)) {

				$output = wordwrap(strip_tags($announce), $announceLength);
				$lines = explode("\n", $output);
				$output = $lines[0];

				$output = rtrim($output, '.') . '.';

				if (count($lines) > 1) {
					$output = $output . rtrim($output, '.') . '...';
				}
			} else {
				$output = strip_tags($announce, '<a><strong><b><em><i>');
			}
		}

		return $output;

	}

	public function getAnnounceTouchFriendly() {

		$announce = trim($this->getAnnounce());
		$content = trim($this->getContent());

		$output = null;

		if (!$announce) {
			if ($content) {
				$output = S::create($content, $this->encoding);
			}
		} else {
			$output = S::create($announce, $this->encoding);
		}
		$output = preg_replace('/http:\/\/www.popcornnews.ru|http:\/\/popcornnews.ru/', 'http://' . $_SERVER['HTTP_HOST'], $output);

		return $output;
	}

}