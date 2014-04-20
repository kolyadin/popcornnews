<?php
/**
 * User: anubis
 * Date: 17.10.13 12:55
 */

namespace popcorn\model\persons;


use popcorn\model\content\Image;
use popcorn\model\content\NullImage;
use popcorn\model\IBuilder;
use popcorn\model\voting\VotingFactory;

class PersonBuilder implements IBuilder {

    private $name = '';
    private $englishName = '';
    private $genitiveName = '';
    private $prepositionalName = '';
    private $info = '';
    private $source = '';
    private $photo;
    private $birthDate = null;
    private $showInCoud = 0;
    private $sex = Person::MALE;
    private $isSinger = 0;
    private $allowFacts = 0;
    private $isWidgetAvailable = 1;
    private $widgetPhoto;
    private $widgetFullPhoto;
    private $vkPage = '';
    private $twitterLogin = '';
    private $pageName = '';
    private $nameForBio = '';
    private $publish = 1;

    private function __construct() {
        $this->photo = new NullImage();
        $this->widgetPhoto = new NullImage();
        $this->widgetFullPhoto = new NullImage();
    }

    public static function create() {
        return new self();
    }

    /**
     * @throws \InvalidArgumentException
     * @return Person
     */
    public function  build() {
        if(empty($this->name)) {
            throw new \InvalidArgumentException('Need to set name');
        }
        if(empty($this->englishName)) {
            throw new \InvalidArgumentException('Need to set english name');
        }
        if(!is_a($this->birthDate, '\DateTime') && !is_null($this->birthDate)) {
            throw new \InvalidArgumentException('Birth date must be instance of DateTime or null');
        }

        if(empty($this->genitiveName)) $this->genitiveName = $this->name;
        if(empty($this->prepositionalName)) $this->prepositionalName = $this->name;

        $person = new Person();
        $person->setName($this->name);
        $person->setEnglishName($this->englishName);
        $person->setGenitiveName($this->genitiveName);
        $person->setPrepositionalName($this->prepositionalName);
        $person->setInfo($this->info);
        $person->setSource($this->source);
        $person->setPhoto($this->photo);
        $person->setBirthDate($this->birthDate);
        $person->setShowInCloud($this->showInCoud);
        $person->setSex($this->sex);
        $person->setIsSinger($this->isSinger);
        $person->setAllowFacts($this->allowFacts);
        $person->setIsWidgetAvailable($this->isWidgetAvailable);
        $person->setWidgetPhoto($this->widgetPhoto);
        $person->setWidgetFullPhoto($this->widgetFullPhoto);
        $person->setVkPage($this->vkPage);
        $person->setTwitterLogin($this->twitterLogin);
        $person->setPageName($this->pageName);
        $person->setNameForBio($this->nameForBio);
        ($this->publish) ? $person->publish() : $person->unPublish();

        $name = str_replace('-', '_', $this->englishName);
        $name = str_replace('&dash;', '_', $name);
        $name = str_replace(' ', '-', $name);
        $person->setUrlName($name);

        $person->setLook(VotingFactory::createTenVoting());
        $person->setStyle(VotingFactory::createTenVoting());
        $person->setTalent(VotingFactory::createTenVoting());

        return $person;
    }

    /**
     * @return PersonBuilder
     */
    public function allowFacts() {
        $this->allowFacts = 1;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function disallowFacts() {
        $this->allowFacts = 0;
        return $this;
    }

    /**
     * @param \DateTime|null $birthDate
     * @return PersonBuilder
     */
    public function birthDate( $birthDate) {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @param string $englishName
     * @return PersonBuilder
     */
    public function englishName($englishName) {
        $this->englishName = $englishName;
        return $this;
    }

    /**
     * @param string $genitiveName
     * @return PersonBuilder
     */
    public function genitiveName($genitiveName) {
        $this->genitiveName = $genitiveName;
        return $this;
    }

    /**
     * @param string $info
     * @return PersonBuilder
     */
    public function info($info) {
        $this->info = $info;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function aSinger() {
        $this->isSinger = 1;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function notASinger() {
        $this->isSinger = 0;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function widgetAvailable() {
        $this->isWidgetAvailable = 1;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function widgetUnavailable() {
        $this->isWidgetAvailable = 0;
        return $this;
    }

    /**
     * @param string $name
     * @return PersonBuilder
     */
    public function name($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $nameForBio
     * @return PersonBuilder
     */
    public function nameForBio($nameForBio) {
        $this->nameForBio = $nameForBio;
        return $this;
    }

    /**
     * @param string $pageName
     * @return PersonBuilder
     */
    public function pageName($pageName) {
        $this->pageName = $pageName;
        return $this;
    }

    /**
     * @param Image $photo
     * @return PersonBuilder
     */
    public function photo(Image $photo) {
        $this->photo = $photo;
        return $this;
    }

    /**
     * @param string $prepositionalName
     * @return PersonBuilder
     */
    public function prepositionalName($prepositionalName) {
        $this->prepositionalName = $prepositionalName;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function publish() {
        $this->publish = 1;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function dontPublish() {
        $this->publish = 0;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function male() {
        $this->sex = Person::MALE;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function female() {
        $this->sex = Person::FEMALE;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function showInCoud() {
        $this->showInCoud = 1;
        return $this;
    }

    /**
     * @return PersonBuilder
     */
    public function dontShowInCloud() {
        $this->showInCoud = 0;
        return $this;
    }

    /**
     * @param string $source
     * @return PersonBuilder
     */
    public function source($source) {
        $this->source = $source;
        return $this;
    }

    /**
     * @param string $twitterLogin
     * @return PersonBuilder
     */
    public function twitterLogin($twitterLogin) {
        $this->twitterLogin = $twitterLogin;
        return $this;
    }

    /**
     * @param string $vkPage
     * @return PersonBuilder
     */
    public function vkPage($vkPage) {
        $this->vkPage = $vkPage;
        return $this;
    }

    /**
     * @param Image $widgetFullPhoto
     * @return PersonBuilder
     */
    public function widgetFullPhoto(Image $widgetFullPhoto) {
        $this->widgetFullPhoto = $widgetFullPhoto;
        return $this;
    }

    /**
     * @param Image $widgetPhoto
     * @return PersonBuilder
     */
    public function widgetPhoto(Image $widgetPhoto) {
        $this->widgetPhoto = $widgetPhoto;
        return $this;
    }


}