<?php
/**
 * User: kirill.mazurik
 * Date: 20.06.14
 * Time: 10:00
 */

namespace popcorn\model\system;

use popcorn\model\Model;

/**
 * Class GoogleStat
 * @package popcorn\model\system
 * @table pn_ga
 */
class GoogleStat extends Model {

    //region Fields

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $date = '';

    /**
     * @var int
     */
    private $pageviews;

    /**
     * @var int
     */
    private $visits;

    /**
     * @var string
     */
    private $country_json;

    /**
     * @var string
     */
    private $city_json;

    /**
     * @var string
     */
    private $sex_json;

    /**
     * @var string
     */
    private $age_json;

    //endregion

    //region Getters

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getPageViews() {
        return $this->pageviews;
    }

    /**
     * @return int
     */
    public function getVisits() {
        return $this->visits;
    }

    /**
     * @return string
     */
    public function getCountryJson() {
        return $this->country_json;
    }

    /**
     * @return string
     */
    public function getCityJson() {
        return $this->city_json;
    }

    /**
     * @return string
     */
    public function getSexJson() {
        return $this->sex_json;
    }

    /**
     * @return string
     */
    public function getAgeJson() {
        return $this->age_json;
    }

    //endregion

    //region Settings

    /**
     * @param int
     */
    public function setId($id) {
        $this->id = $id;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setDate($date) {
        $this->date = $date;
        $this->changed();
    }

    /**
     * @param int
     */
    public function setPageViews($pageviews) {
        $this->pageviews = $pageviews;
        $this->changed();
    }

    /**
     * @param int
     */
    public function setVisits($visits) {
        $this->visits = $visits;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setCountryJson($country_json) {
        $this->country_json = $country_json;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setCityJson($city_json) {
        $this->city_json = $city_json;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setSexJson($sex_json) {
        $this->sex_json = $sex_json;
        $this->changed();
    }

    /**
     * @param string
     */
    public function setAgeJson($age_json) {
        $this->age_json = $age_json;
        $this->changed();
    }

    //endregion

}