<?php

require_once (dirname(__FILE__) . '/BaseHandler.php');

/**
 * 
 * Класс для подключения и управления обработчиками
 * @author anubis
 *
 */
class Handlers  {
    
    function __construct(&$ui) {
        $this->ui = $ui;
    }
    
    public function LoadHandler($handler, $method) {
        try {
            $handle = $this->GetHandler($handler);
            if(!method_exists($handle, $method)) {
                throw new Exception("Action '{$method}' not exists");
            }
            $params = array_slice(func_get_args(), 2);
            if(count($params) == 0) {
                call_user_func(array($handle, $method));
            }
            if (count($params) == 1) {
                call_user_func(array($handle, $method), $params[0]);
            } else
            if (count($params) == 2) {
                call_user_func(array($handle, $method), $params[0], $params[1]);
            } else
            if (count($params) == 3) {
                call_user_func(array($handle, $method), $params[0], $params[1], $params[2]);
            } 
            else
            {
                call_user_func(array($handle, $method), $params);
            }
        }
        catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    public function GetHandler($handler) {
        if(array_key_exists($handler, $this->handlers)) {
            if($this->handlers[$handler] instanceof BaseHandler) {
                return $this->handlers[$handler];
            }
            throw new Exception("Wrong handler '{$handler}'");
        }
        
        $file = dirname(__FILE__).'/'.$handler.'Handler.php';
        if(!file_exists($file)) {
            throw new Exception("Handler '{$handler}' not exists");
        }
        require_once $file;
        $class = $handler.'Handler';
        $this->handlers[$handler] = new $class($this->ui);
        return $this->handlers[$handler];
    }
    
    /**
     * @var user_base_api
     */
    private $ui = null;
    
    private $handlers = array();
}

?>