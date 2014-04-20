<?php
/**
 * User: anubis
 * Date: 30.08.13 14:09
 */

namespace popcorn\model\dataMaps;


class CustomNewsPostDataMap extends NewsPostDataMap {

    protected $map = array(
        'class'   => 'popcorn\\model\\content\\CustomNewsPost',
        'tables'  =>
        array(
            'news' =>
            array(
                'table' => 'pn_news',
                'alias' => 'news',
            ),
        ),
        'columns' =>
        array(
            'id'         =>
            array(
                'field'    => 'id',
                'column'   => 'id',
                'table'    => 'news',
                'alias'    => '',
                'readonly' => true,
            ),
            'name'       =>
            array(
                'field'    => 'name',
                'column'   => 'name',
                'table'    => 'news',
                'alias'    => '',
                'readonly' => false,
            ),
            'createDate' =>
            array(
                'field'    => 'createDate',
                'column'   => 'createDate',
                'table'    => 'news',
                'alias'    => '',
                'readonly' => false,
            ),
            'comments'   =>
            array(
                'field'    => 'comments',
                'column'   => 'comments',
                'table'    => 'news',
                'alias'    => '',
                'readonly' => false,
            ),
        ),
    );

}