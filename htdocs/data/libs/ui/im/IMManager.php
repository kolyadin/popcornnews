<?php
/**
 * User: anubis
 * Date: 4/22/13
 * Time: 12:11 PM
 */

require_once __DIR__.'/../GenericManager.php';
require_once 'RoomFactory.php';

class IMManager extends GenericManager {

    public function __construct(user_base_api $ui) {
        parent::__construct($ui, array(
                                      'template' => 'settings/comments'
                                 ));
    }

    public static function createUrl($action = 'default', $params = array()) {
        return parent::createUrl('commentsettings', $action, $params);
    }

    /**
     * @return array
     */
    protected function initRoutes() {
        return array(
            'default'               => 'showMainPage',
            'toggle'   => 'toggle'
        );
    }

    public function showMainPage() {
        $this->tpl->assign('roomTypes', RoomFactory::getRoomTypes());
        $this->setTemplate('main');
    }

    public function toggle() {
        $roomType = $this->get_param('roomType');
        $valueName = $this->get_param('value');

        $value = RoomConfig::getValue($roomType, $valueName);
        RoomConfig::setValue($roomType, $valueName, !$value);

        $this->ui->url_jump(self::createUrl());
    }
}