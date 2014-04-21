<?php

namespace popcorn\model\talks;

use popcorn\model\IBuilder;
use popcorn\model\voting\VotingFactory;

/**
 * Class TalkBuilder
 * @package \popcorn\model\talks
 */
class TalkBuilder implements IBuilder {

//region Fields

    /**
     * @var \popcorn\model\system\users\User
     */
    private $owner;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $content;

//endregion

    /**
     * @param \popcorn\model\system\users\User $owner
     *
     * @return TalkBuilder
     */
    public function owner($owner) {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param string $title
     *
     * @return TalkBuilder
     */
    public function title($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return TalkBuilder
     */
    public function content($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * @return TalkBuilder
     */
    public static function create() {
        return new self();
    }

    /**
     * @return \popcorn\model\talks\Talk
     */
    public function build() {
        $rating = VotingFactory::createUpDownVoting();

        $item = new Talk();
        $item->setCreateTime(new \DateTime());
        $item->setOwner($this->owner);
        $item->setTitle($this->title);
        $item->setContent($this->content);
        $item->setRating($rating);

        return $item;
    }

}