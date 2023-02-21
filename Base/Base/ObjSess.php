<?php

namespace Base\Base;

use \Base\Base\BaseObj;

class ObjSess extends BaseObj
{
    protected $key;
    
    public $vars;

    public function __construct($key)
    {
        $this->key = $key;
        
        $vars = [];
        
        if (isset($_SESSION[$this->key]) && is_array($_SESSION[$this->key])) {
            $vars = $_SESSION[$this->key];
        }
            
        $_SESSION[$this->key] = [];
        
        if (is_array($vars)) {
            $this->vars = $vars;
        }
    }

    public function __set($key, $val)
    {
        $this->vars[$key] = $val;
        $_SESSION[$this->key][$key] = $val;
    }

    public function __unset($key)
    {
        if (isset($this->vars[$key])) {
            unset($this->vars[$key]);
            unset($_SESSION[$this->key][$key]);
        }
    }

    public function count()
    {
        return count($this->vars);
    }
}
