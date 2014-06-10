<?php

namespace popcorn\model\calendar;

use Decoda\Decoda;
use popcorn\model\Model;

/**
 * Class Event
 * @package popcorn\model\calendar\Event
 * @table pn_calendar_events
 */
class Event extends Model {

	//region Fields

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var \DateTime
	 * @export
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 * @export
	 */
	private $eventDate;

	/**
	 * @var string
	 */
	private $place;

	/**
	 * @var string
	 */
	private $content;

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
		$this->changed();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt
	 */
	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
		$this->changed();
	}

	/**
	 * @return \DateTime
	 */
	public function getEventDate() {
		return $this->eventDate;
	}

	/**
	 * @param \DateTime $eventDate
	 */
	public function setEventDate($eventDate) {
		$this->eventDate = $eventDate;
		$this->changed();
	}

	/**
	 * @return string
	 */
	public function getPlace() {
		return $this->place;
	}

	/**
	 * @param string $place
	 */
	public function setPlace($place) {
		$this->place = $place;
		$this->changed();
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->changed();
	}

	//endregion


}