<?php
/**
 * User: anubis
 * Date: 23.01.13 13:15
 */
class PhotoArticleItem {

    private $id = null;
    private $photo = '';
    private $description = '';
    private $date = 0;
    private $zoomable = false;
    private $source = '';
    private $title = '';
    private $persons = array();
    private $articleId = null;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->photo = $data['photo'];
        $this->description = $data['description'];
        $this->date = $data['date'];
        $this->zoomable = (bool)$data['zoomable'];
        $this->source = $data['source'];
        $this->title = $data['title'];
        $this->persons = $data['persons'];
        $this->articleId = $data['articleId'];
    }

    public function getId() {
        return $this->id;
    }

    public function getPhoto() {
        return $this->photo;
    }

    public function getPhotoPathBySize($size) {
        return '/k/photo_articles/'.$size.$this->getOriginalPath();
    }

    public function getOriginalPath() {
        $y = date('Y', $this->date);
        $m = date('m', $this->date);
        $d = date('d', $this->date);
        $path = '/upload/photo_articles/'.$y.'/'.$m.'/'.$d.'/'.$this->photo;
        return $path;
    }

    public function getDescription() {
        return $this->filterTags($this->description);
    }

    public function getDate() {
        return $this->date;
    }

    public function isZoomable() {
        return $this->zoomable;
    }

    public function hasSource() {
        return !empty($this->source);
    }

    public function getSource() {
        return $this->source;
    }

    public function setSource($source) {
        $this->source = $source;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getPersons() {
        return $this->persons;
    }

    public function setArticleId($articleId) {
        $this->articleId = $articleId;
    }

    public function getArticleId() {
        return $this->articleId;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setPersons($persons) {
        $this->persons = $persons;
    }

    private function filterTags($text) {
        $text = preg_replace('@(<[^>]+)\s*style=".*?"@is','$1', $text);
        $text = strip_tags($text, '<br><br/><br /><a><span><p><strong><em><strike><blockquote><ol><ul><li>');
        if(strpos($text, '<p') === false && strpos($text, '<P') === false) {
            $text = '<p>'.$text.'</p>';
        }
        return $text;
    }

    public function setZoomable($zoomable) {
        $this->zoomable = $zoomable;
    }

    public function hasPerson($personId) {
        foreach($this->persons as $person) {
            if($personId == $person['personId'])
                return true;
        }
        return false;
    }
}
