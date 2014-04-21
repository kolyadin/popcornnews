<?php
/**
 * User: anubis
 * Date: 23.01.13 13:12
 */
class PhotoArticle {

    private $id = null;
    private $title = '';
    /**
     * @var PhotoArticleItem[]
     */
    private $photos = array();
    private $date = 0;
    private $tags = array();
    private $persons = array();
    private $views = 0;
    private $commentsCount = 0;
    private $published = false;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->date = $data['date'];
        $this->tags = $data['tags'];
        $this->persons = $data['persons'];
        $this->views = $data['views'];
        $this->commentsCount = $data['commentsCount'];
        $this->published = $data['published'];
        if(array_key_exists('photos', $data)) {
            $this->photos = $data['photos'];
        }
    }

    public function addItem(PhotoArticleItem $item) {
        $this->photos[] = $item;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDate() {
        return $this->date;
    }

    public function getTags() {
        return $this->tags;
    }

    public function getPersons() {
        return $this->persons;
    }

    /**
     * @param $id
     *
     * @return PhotoArticleItem
     * @throws Exception
     */
    public function getItem($id) {
        if(!array_key_exists($id, $this->photos)) {
            throw new Exception("Wrong photo id in photo articles");
        }
        return $this->photos[$id];
    }

    public function getViews() {
        return $this->views;
    }

    public function getCommentsCount() {
        return $this->commentsCount;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPhotosCount() {
        return count($this->photos) + 1;
    }

    /**
     * @return PhotoArticleItem[]
     */
    public function getPhotos() {
        return $this->photos;
    }

    public function incViews() {
        $this->views++;

        PhotoArticleFactory::incViews($this->id);
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setTags($tags) {
        $this->tags = $tags;
    }

    public function setPersons($persons) {
        $this->persons = $persons;
    }

    /**
     * @return PhotoArticleItem
     */
    public function getRandomPhoto() {
        $rand = rand(0, count($this->photos) - 1);
        return $this->photos[$rand];
    }

    public function getPhotoByPerson($personId) {
        foreach($this->photos as $photo) {
            if($photo->hasPerson($personId)) {
                return $photo;
            }
        }
        return null;
    }

    public function isPublished() {
        return $this->published;
    }

}
