<?php
/**
 * (NULL)
 * @author
 *
 */
interface IMessage {

    public function update();

    /**
     * @param string $content
     */
    public function setContent($content);

    public function delete();

    public function restore();

    /**
     * @param int $uid
     * @param array $rating
     *
     * @return bool
     */
    public function rate($uid, $rating);

    /**
     * @param int $uid
     *
     * @return bool
     */
    public function abuse($uid);

    /**
     * @return int
     */
    public function getOwner();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return string
     */
    public function getID();

    /**
     * @return string|null
     */
    public function getParent();

    public function getAbuseCount();

}
