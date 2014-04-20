<?php
/**
 * 
 * Базовый класс для обработчиков из @see user_base_api
 * @author anubis
 *
 */
abstract class BaseHandler  {
    function __construct($ui) {
        $this->ui = $ui;
        $this->tpl = $this->ui->tpl;
        $this->tpl->assign('handler', $this);
    }
    
    protected final function getUI() {
        return $this->ui;
    }
    
    protected final function getTpl() {
        return $this->tpl;
    }
    
    protected final function showError($error) {
        $this->getUI()->handler_show_error($error);
    }
    
    protected final function redirect($url = '/', $redirectStatus = HTTP_STATUS_301) {
        $this->getUI()->redirect($url, $redirectStatus);
    }
    
    protected final function url_jump($url) {
        $this->getUI()->url_jump($url);
    }
    
    /**
     * @var user_base_api
     */
    protected $ui;
    
    /**
     * @var VPA_template
     */
    protected $tpl;
}

?>