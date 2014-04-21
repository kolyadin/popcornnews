<?php
/**
 * User: anubis
 * Date: 4/22/13
 * Time: 12:12 PM
 */

abstract class GenericManager {

    protected $ui = null;
    protected $tpl = null;
    private $routes = array();

    private $template;

    public function __construct(user_base_api $ui, $options = array()) {
        $this->template = $options['template'];
        $this->ui = $ui;
        $this->tpl = $ui->tpl;

        $this->routes = $this->initRoutes();
        $this->routeAction();
    }

    /**
     * @return array
     */
    protected abstract function initRoutes();

    protected function routeAction() {
        $action = $this->ui->get_param('action');
        if(is_null($action)) $action = 'default';

        if(array_key_exists($action, $this->routes) && method_exists($this, $this->routes[$action])) {
            call_user_func(array($this, $this->routes[$action]));
        }
        else {
            $this->ui->url_jump(self::createUrl());
        }
    }

    public static function createUrl($base='', $action = 'default', $params = array()) {
        $url = '?type='.$base;
        if($action != 'default') {
            $url .= '&action='.$action;
        }
        if(!empty($params)) {
            $url .= '&'.http_build_query($params);
        }

        return $url;
    }

    protected function setTemplate($tpl) {
        $this->tpl->assign('content', $tpl.'.php');
        $this->tpl->tpl('', '/manager/'.$this->template.'/', 'base.php');
    }

    protected function get_param($name) {
        return $this->ui->get_param($name);
    }
}