<?php
/**
 * User: anubis
 * Date: 09.10.13 18:33
 */

namespace popcorn\model\content;


class NullImage extends Image {

    public function getId() {
        return 0;
    }

}