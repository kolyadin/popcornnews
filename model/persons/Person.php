<?php
/**
 * User: anubis
 * Date: 05.09.13 13:46
 */

namespace popcorn\model\persons;

use popcorn\model\content\Image;
use popcorn\model\exceptions\SaveFirstException;
use popcorn\model\Model;
use popcorn\model\voting\TenVoting;

/**
 * Class Person
 * @package popcorn\model\persons
 * @table pn_persons
 */
class Person extends Model {

	const MALE = 0;
	const FEMALE = 1;

	//region Fields

	/**
	 * @var string
	 * @export
	 */
	private $name = '';
	/**
	 * @var string
	 * @export
	 */
	private $englishName = '';
	/**
	 * @var string
	 * @export
	 */
	private $genitiveName = '';
	/**
	 * @var string
	 * @export
	 */
	private $prepositionalName = '';
	/**
	 * @var string
	 * @export
	 */
	private $info = '';
	/**
	 * @var string
	 * @export
	 */
	private $source = '';
	/**
	 * @var Image
	 * @export
	 */
	private $photo = 0;

	/**
	 * @var Image[]
	 */
	protected $images = [];

	/**
	 * @var \DateTime
	 * @export
	 */
	private $birthDate = '1987-01-02';
	/**
	 * @var bool
	 * @export
	 */
	private $showInCloud = true;
	/**
	 * @var int
	 * @export
	 */
	private $sex = self::MALE;
	/**
	 * @var bool
	 * @export
	 */
	private $isSinger = 0;
	/**
	 * @var bool
	 * @export
	 */
	private $allowFacts = true;
	/**
	 * @var string
	 * @export
	 */
	private $vkPage = '';
	/**
	 * @var string
	 * @export
	 */
	private $twitterLogin = '';
	/**
	 * @var string
	 * @export
	 */
	private $instagramLogin = '';
	/**
	 * заголовок страницы (видимо что-то для сеошников)
	 * @var string
	 * @export
	 */
	private $pageName = '';
	/**
	 * имя для биографии - тоже что-то для сеошников
	 * @var string
	 * @export
	 */
	private $nameForBio = '';

	/**
	 * @var bool
	 * @export
	 */
	private $published = 0;

	private $urlName;

	/**
	 * @var int
	 */
	private $newsCount = 0;

	/**
	 * @var int
	 */
	private $photosCount = 0;

	/**
	 * @var int
	 */
	private $votesCount = 0;

	/**
	 * @var float
	 */
	private $look = 0;

	/**
	 * @var float
	 */
	private $style = 0;

	/**
	 * @var float
	 */
	private $talent = 0;

	//endregion

	//region Getters

	/**
	 * @return \DateTime
	 */
	public function getBirthDate() {
		return $this->birthDate;
	}

	/**
	 * @return boolean
	 * @get allowFacts
	 */
	public function isAllowFacts() {
		return $this->allowFacts;
	}

	public function isPerson(){
		return true;
	}

	/**
	 * @return string
	 */
	public function getEnglishName() {
		return $this->englishName;
	}

	/**
	 * @return string
	 */
	public function getGenitiveName() {
		return $this->genitiveName;
	}

	/**
	 * @return string
	 */
	public function getInfo() {
		return $this->info;
	}

	/**
	 * @return boolean
	 * @get isSinger
	 */
	public function isSinger() {
		return $this->isSinger;
	}


	/**
	 * @return \popcorn\model\persons\Person[]
	 */
	public function getLinkedPersons() {
		return PersonFactory::getLinkedPersons($this->getId());
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getNameForBio() {
		return $this->nameForBio;
	}

	/**
	 * @return string
	 */
	public function getPageName() {
		return $this->pageName;
	}

	/**
	 * @return \popcorn\model\content\Image
	 */
	public function getPhoto() {
		return $this->photo;
	}

	/**
	 * @return \popcorn\model\content\Image[]
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * @return string
	 */
	public function getPrepositionalName() {
		return $this->prepositionalName;
	}

	/**
	 * @return int
	 */
	public function getSex() {
		return $this->sex;
	}

	/**
	 * @return boolean
	 * @get showInCloud
	 */
	public function isShowInCloud() {
		return $this->showInCloud;
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
	public function getTwitterLogin() {
		return $this->twitterLogin;
	}

	/**
	 * @return string
	 */
	public function getInstagramLogin() {
		return $this->instagramLogin;
	}

	/**
	 * @return string
	 */
	public function getVkPage() {
		return $this->vkPage;
	}

	/**
	 * @get published
	 * @return bool
	 */
	public function isPublished() {
		return $this->published;
	}

	public function getUrlName() {
		return $this->urlName;
	}

	/**
	 * @param string $format
	 * @return int|float
	 */
	public function getLook($format = '%.1f') {
		if (!(int)$this->look) return 0;
		return sprintf($format, $this->look);
	}

	/**
	 * @param string $format
	 * @return int|float
	 */
	public function getStyle($format = '%.1f') {
		if (!(int)$this->style) return 0;
		return sprintf($format, $this->style);
	}

	/**
	 * @param string $format
	 * @return int|float
	 */
	public function getTalent($format = '%.1f') {
		if (!(int)$this->talent) return 0;
		return sprintf($format, $this->talent);
	}

	public function getTotalRating($format = '%.1f') {
		return sprintf($format, ($this->look + $this->style + $this->talent)/3);
	}

	/**
	 * @return int
	 */
	public function getNewsCount() {
		return $this->newsCount;
	}

	/**
	 * @return int
	 */
	public function getPhotosCount() {
		return $this->photosCount;
	}

	/**
	 * @return int
	 */
	public function getVotesCount() {
		return $this->votesCount;
	}

	//endregion

	//region Setters

	/**
	 * @param boolean $allowFacts
	 */
	public function setAllowFacts($allowFacts) {
		$this->allowFacts = $allowFacts;
		$this->changed();
	}

	/**
	 * @param \DateTime $birthDate
	 */
	public function setBirthDate($birthDate) {
		$changed = true;
		if (!($this->birthDate instanceof \DateTime)) {
			$changed = false;
		}
		$this->birthDate = $birthDate;
		$this->birthDateFriendly = $this->getBirthDateFriendly();
		if ($changed) $this->changed();
	}

	/**
	 * @param string $englishName
	 */
	public function setEnglishName($englishName) {
		$this->englishName = $englishName;

		$urlName = str_replace('-', '_', $englishName);
		$urlName = str_replace('&dash;', '_', $urlName);
		$urlName = str_replace(' ', '-', $urlName);

		$this->urlName = $urlName;

		$this->changed();
	}

	/**
	 * @param string $genitiveName
	 */
	public function setGenitiveName($genitiveName) {
		$this->genitiveName = $genitiveName;
		$this->changed();
	}

	/**
	 * @param string $info
	 */
	public function setInfo($info) {
		$this->info = $info;
		$this->changed();
	}

	/**
	 * @param boolean $isSinger
	 */
	public function setIsSinger($isSinger) {
		$this->isSinger = $isSinger;
		$this->changed();
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
		$this->changed();
	}

	/**
	 * @param string $nameForBio
	 */
	public function setNameForBio($nameForBio) {
		$this->nameForBio = $nameForBio;
		$this->changed();
	}

	/**
	 * @param string $pageName
	 */
	public function setPageName($pageName) {
		$this->pageName = $pageName;
		$this->changed();
	}

	/**
	 * @param \popcorn\model\content\Image $photo
	 */
	public function setPhoto($photo) {
		$changed = false;
		if (is_object($this->photo)) {
			$changed = true;
			if ($photo == $this->photo) {
				return;
			}
		}
		$this->photo = $photo;
		if ($changed) $this->changed();
	}

	/**
	 * @param \popcorn\model\content\Image[] $images
	 */
	public function setImages($images) {
		$this->images = $images;
		$this->changed();
	}

	/**
	 * @param string $prepositionalName
	 */
	public function setPrepositionalName($prepositionalName) {
		$this->prepositionalName = $prepositionalName;
		$this->changed();
	}

	/**
	 * @param int $sex
	 */
	public function setSex($sex) {
		$this->sex = $sex;
		$this->changed();
	}

	/**
	 * @param boolean $showInCloud
	 */
	public function setShowInCloud($showInCloud) {
		$this->showInCloud = $showInCloud;
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
	 * @param string $twitterLogin
	 */
	public function setTwitterLogin($twitterLogin) {
		$this->twitterLogin = $twitterLogin;
		$this->changed();
	}

	/**
	 * @param $instagramLogin
	 */
	public function setInstagramLogin($instagramLogin) {
		$this->instagramLogin = $instagramLogin;
		$this->changed();
	}

	/**
	 * @param string $vkPage
	 */
	public function setVkPage($vkPage) {
		$this->vkPage = $vkPage;
		$this->changed();
	}


	public function publish() {
		$this->published = 1;
		$this->changed();
	}

	public function unPublish() {
		$this->published = 0;
		$this->changed();
	}

	public function setUrlName($urlName) {
		$this->urlName = $urlName;
		$this->changed();
	}

	/**
	 * @param float $look
	 */
	public function setLook($look) {
		$this->look = $look;
	}

	/**
	 * @param float $style
	 */
	public function setStyle($style) {
		$this->style = $style;
	}

	/**
	 * @param float $talent
	 */
	public function setTalent($talent) {
		$this->talent = $talent;
	}

	/**
	 * @param int $count
	 */
	public function setNewsCount($count) {
		$this->newsCount = $count;
	}

	/**
	 * @param int $count
	 */
	public function setPhotosCount($count) {
		$this->photosCount = $count;
	}

	/**
	 * @param int $count
	 */
	public function setVotesCount($count) {
		$this->votesCount = $count;
	}

	//endregion

	/**
	 * @param Person $person
	 *
	 * @return bool
	 * @throws \popcorn\model\exceptions\SaveFirstException
	 */
	public function link(Person $person) {
		$this->checkIsSaved($this);
		$this->checkIsSaved($person);

		return PersonFactory::link($this->getId(), $person->getId());
	}

	public function cleanLinks() {
		PersonFactory::clearLinks($this->getId());
	}

	private function getBirthDateFriendly() {
		if (is_null($this->getBirthDate())) return null;

		return vsprintf('%3$02u.%2$02u.%1$04u', sscanf($this->getBirthDate()->format('Y-m-d'), '%04u-%02u-%02u'));
	}

	/**
	 * @param Person $person
	 */
	public function unlink($person) {
		$this->checkIsSaved($this);
		$this->checkIsSaved($person);
		PersonFactory::unlink($this->getId(), $person->getId());
	}

	/**
	 * @param Person $person
	 *
	 * @throws \popcorn\model\exceptions\SaveFirstException
	 */
	private function checkIsSaved($person) {
		if ($person->getId() <= 0 || is_null($person->getId())) {
			throw new SaveFirstException();
		}
	}

}