<?php

namespace popcorn\model\groups;

use popcorn\model\content\Image;
use popcorn\model\content\NullImage;
use popcorn\model\IBuilder;
use popcorn\model\system\users\User;
use popcorn\model\tags\Tag;

/**
 * Class GroupBuilder
 * @package \popcorn\model\groups
 */
class GroupBuilder implements IBuilder {

//region Fields

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $description = '';
    /**
     * @var bool
     */
    private $private = false;
    /**
     * @var \popcorn\model\system\users\User
     */
    private $owner;
    /**
     * @var \popcorn\model\content\Image
     */
    private $poster;
    /**
     * @var \popcorn\model\tags\Tag[]
     */
    private $tags = array();

//endregion

    /**
     * @param string $title
     *
     * @return GroupBuilder
     */
    public function title($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return GroupBuilder
     */
    public function description($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * @return GroupBuilder
     */
    public function notPublic() {
        $this->private = true;

        return $this;
    }

    /**
     * @param \popcorn\model\system\users\User $owner
     *
     * @return GroupBuilder
     */
    public function owner(User $owner) {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param \popcorn\model\content\Image $poster
     *
     * @return GroupBuilder
     */
    public function poster(Image $poster) {
        $this->poster = $poster;

        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return GroupBuilder
     * @throws \InvalidArgumentException
     */
    public function addTag(Tag $tag) {
        if(array_search($tag, $this->tags) !== false) {
            throw new \InvalidArgumentException('Tag exists');
        }
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @return GroupBuilder
     */
    public static function create() {
        return new self();
    }

    /**
     * @throws \InvalidArgumentException
     * @return \popcorn\model\groups\Group
     */
    public function build() {
        if(is_null($this->owner)) {
            throw new \InvalidArgumentException('Need to set group owner');
        }
        if(is_null($this->title)) {
            throw new \InvalidArgumentException('Need to set group title');
        }
        if(is_null($this->poster)) {
            $this->poster = new NullImage();
        }
        $item = new Group();
        $item->setTitle($this->title);
        $item->setDescription($this->description);
        $item->setCreateTime(new \DateTime());
        $item->setEditTime(new \DateTime());
        $item->setPrivate(intval($this->private));
        $item->setOwner($this->owner);
        $item->setPoster($this->poster);
        if(count($this->tags) > 0) {
            $item->setTags($this->tags);
        }

        return $item;
    }

}